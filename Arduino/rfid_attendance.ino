#include <Arduino.h>
#include <SPI.h>
#include <Ethernet.h>
#include <string.h>

#include "ProjectConfiguration.h"
#include "arduino_secrets.h"

#define SERIAL_LOGGING_ENABLED 0

#if SERIAL_LOGGING_ENABLED
  #define SERIAL_BEGIN(baud) do { \
    Serial.begin((baud)); \
    unsigned long _serialWaitStartedAtMs = millis(); \
    while (!Serial && (millis() - _serialWaitStartedAtMs < 2000)) {} \
  } while (0)
  #define SERIAL_PRINT(x) Serial.print((x))
  #define SERIAL_PRINT2(x, y) Serial.print((x), (y))
  #define SERIAL_PRINTLN(x) Serial.println((x))
  #define SERIAL_PRINTLN0() Serial.println()
#else
  #define SERIAL_BEGIN(baud) do {} while (0)
  #define SERIAL_PRINT(x) do {} while (0)
  #define SERIAL_PRINT2(x, y) do {} while (0)
  #define SERIAL_PRINTLN(x) do {} while (0)
  #define SERIAL_PRINTLN0() do {} while (0)
#endif

static HardwareSerial& radioFrequencyIdentificationSerialPort = Serial1;
static EthernetClient backendHttpClient;

static bool usingDhcp = true;

static bool ethernetLinkUp = true;
static bool backendReachable = true;
static bool backendWasHealthy = true;

static unsigned long ethernetLinkOnlineAtMs = 0;
static unsigned long lastConnectivityProbeAtMs = 0;
static unsigned long lastEthernetRecoverAtMs = 0;
static unsigned long lastBackendBeepAtMs = 0;

static uint8_t consecutiveProbeFailures = 0;
static uint8_t consecutiveHttpFailures = 0;

static bool radioFrequencyIdentificationCollectingNow = false;
static bool radioFrequencyIdentificationTimedOut = false;

static unsigned long lastRadioFrequencyIdentificationByteAtMs = 0;

static char lastAcceptedCardIdentifier[ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength + 1] = {0};
static unsigned long lastAcceptedCardAtMs = 0;

static constexpr uint8_t pendingAttendanceTapCapacity = 64;

struct PendingAttendanceTap final {
  char cardIdentifier[ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength + 1];
};

static PendingAttendanceTap pendingAttendanceTapQueue[pendingAttendanceTapCapacity];
static uint8_t pendingAttendanceTapHead = 0;
static uint8_t pendingAttendanceTapTail = 0;
static uint8_t pendingAttendanceTapCount = 0;

static unsigned long nextBackendSendAttemptAtMs = 0;
static unsigned long backendSendBackoffMs = 0;

static constexpr unsigned long backendSendBackoffMinimumMs = 250;
static constexpr unsigned long backendSendBackoffMaximumMs = 8000;
static constexpr unsigned long networkSendOnlyAfterRadioFrequencyIdentificationIdleMs = 400;

static void initializeNetwork();
static void handleEthernetLink();
static void updateConnectivityState();
static void recoverEthernetIfNeeded();
static void recoverEthernet();

static bool probeBackendTcp();

static bool readRadioFrequencyIdentificationFrame(char* outFrame, size_t outFrameSize);
static bool normalizeCardIdentifier(const char* raw, char* outIdentifier, size_t outIdentifierSize);

static void collectPendingAttendanceTapsFromRadioFrequencyIdentification();
static bool tryEnqueuePendingAttendanceTap(const char* cardIdentifier);
static bool tryDequeuePendingAttendanceTap(char* outCardIdentifier, size_t outCardIdentifierSize);
static void processPendingAttendanceTapQueue();

static int sendAttendanceTap(const char* cardIdentifier);
static int readHttpStatusCode(unsigned long timeoutMs);
static bool isBackendHealthy(int statusCode);

static void playCardDetectedBeep();
static void playCardOkBeep();
static void playRecoveredConnectivityBeep();
static void playBackendErrorBeep();
static void playCardRejectedBeep();
static void playQueueFullBeep();

static void printBootState();
static void printHeartbeat();

void setup() {
  pinMode(ProjectConfiguration::HardwarePins::speakerOutputPin, OUTPUT);

  SERIAL_BEGIN(9600);
  printBootState();

  radioFrequencyIdentificationSerialPort.begin(9600);

  initializeNetwork();
  SERIAL_PRINT(F("NET IP: "));
  SERIAL_PRINTLN(Ethernet.localIP());

  playRecoveredConnectivityBeep();
}

void loop() {
  printHeartbeat();

  if (usingDhcp) {
    Ethernet.maintain();
  }

  handleEthernetLink();
  updateConnectivityState();
  recoverEthernetIfNeeded();

  if ((!ethernetLinkUp ||
       (!backendReachable && (millis() - ethernetLinkOnlineAtMs >= ProjectConfiguration::Timing::postLinkOnlineStabilizationMs))) &&
      (millis() - lastBackendBeepAtMs >= ProjectConfiguration::Timing::backendErrorBeepIntervalMs)) {
    lastBackendBeepAtMs = millis();
    playBackendErrorBeep();
  }

  if (lastAcceptedCardIdentifier[0] != '\0' &&
      (millis() - lastRadioFrequencyIdentificationByteAtMs >= ProjectConfiguration::Timing::radioFrequencyIdentificationQuietToResetLastCardMs)) {
    lastAcceptedCardIdentifier[0] = '\0';
    lastAcceptedCardAtMs = 0;
    SERIAL_PRINTLN(F("RFID: last card reset (quiet)"));
  }

  collectPendingAttendanceTapsFromRadioFrequencyIdentification();
  processPendingAttendanceTapQueue();
}

static void initializeNetwork() {
  pinMode(ProjectConfiguration::HardwarePins::storageChipSelectPin, OUTPUT);
  digitalWrite(ProjectConfiguration::HardwarePins::storageChipSelectPin, HIGH);

  pinMode(ProjectConfiguration::HardwarePins::ethernetChipSelectPin, OUTPUT);
  digitalWrite(ProjectConfiguration::HardwarePins::ethernetChipSelectPin, HIGH);

  Ethernet.init(ProjectConfiguration::HardwarePins::ethernetChipSelectPin);

  byte macAddress[6];
  for (uint8_t index = 0; index < 6; index++) {
    macAddress[index] = ProjectSecrets::ethernetMacAddress[index];
  }

  uint8_t dhcpResult = 0;
  for (int attempt = 0; attempt < 5 && dhcpResult == 0; attempt++) {
    dhcpResult = Ethernet.begin(macAddress);
    if (dhcpResult == 0) delay(700);
  }

  if (dhcpResult == 0) {
    usingDhcp = false;
    Ethernet.begin(
      macAddress,
      ProjectSecrets::fallbackStaticIpAddress,
      ProjectSecrets::fallbackDnsAddress,
      ProjectSecrets::fallbackGatewayAddress,
      ProjectSecrets::fallbackSubnetMask
    );
    SERIAL_PRINTLN(F("NET: static fallback"));
  } else {
    usingDhcp = true;
    SERIAL_PRINTLN(F("NET: DHCP OK"));
  }

  delay(600);
}

static void handleEthernetLink() {
  static EthernetLinkStatus lastStatus = Unknown;

  EthernetLinkStatus status = Ethernet.linkStatus();
  if (status == Unknown) {
    return;
  }

  if (status == lastStatus) {
    return;
  }

  lastStatus = status;

  if (status == LinkOFF) {
    ethernetLinkUp = false;
    backendReachable = false;
    backendWasHealthy = false;

    backendHttpClient.stop();

    SERIAL_PRINTLN(F("ETH: LINK OFFLINE"));

    lastBackendBeepAtMs = millis();
    playBackendErrorBeep();
    return;
  }

  ethernetLinkUp = true;
  ethernetLinkOnlineAtMs = millis();

  backendReachable = false;
  consecutiveProbeFailures = ProjectConfiguration::Thresholds::probeFailuresToMarkBackendOffline;

  SERIAL_PRINTLN(F("ETH: LINK ONLINE"));

  recoverEthernet();

  lastAcceptedCardIdentifier[0] = '\0';
  lastAcceptedCardAtMs = 0;
}

static bool probeBackendTcp() {
  EthernetClient probeClient;
  probeClient.setTimeout(800);
  bool ok = probeClient.connect(ProjectSecrets::backendServerAddress, ProjectConfiguration::Backend::port);
  probeClient.stop();
  return ok;
}

static void updateConnectivityState() {
  if (!ethernetLinkUp) {
    backendReachable = false;
    consecutiveProbeFailures = ProjectConfiguration::Thresholds::probeFailuresToMarkBackendOffline;
    return;
  }

  if (Ethernet.localIP() == IPAddress(0, 0, 0, 0)) {
    backendReachable = false;
    return;
  }

  if (millis() - ethernetLinkOnlineAtMs < ProjectConfiguration::Timing::postLinkOnlineStabilizationMs) {
    backendReachable = false;
    return;
  }

  if (millis() - lastConnectivityProbeAtMs < ProjectConfiguration::Timing::connectivityProbeIntervalMs) {
    return;
  }

  lastConnectivityProbeAtMs = millis();

  bool ok = probeBackendTcp();
  if (ok) {
    bool wasOffline = !backendReachable;

    backendReachable = true;
    consecutiveProbeFailures = 0;
    lastBackendBeepAtMs = millis();

    if (wasOffline) {
      SERIAL_PRINTLN(F("BACKEND: reachable"));
      playRecoveredConnectivityBeep();
    }

    return;
  }

  consecutiveProbeFailures++;
  if (consecutiveProbeFailures >= ProjectConfiguration::Thresholds::probeFailuresToMarkBackendOffline) {
    if (backendReachable) {
      SERIAL_PRINTLN(F("BACKEND: unreachable"));
    }
    backendReachable = false;
  }
}

static void recoverEthernetIfNeeded() {
  if (backendReachable) {
    return;
  }

  if (!ethernetLinkUp) {
    return;
  }

  if (millis() - lastEthernetRecoverAtMs < ProjectConfiguration::Timing::ethernetRecoverCooldownMs) {
    return;
  }

  lastEthernetRecoverAtMs = millis();

  SERIAL_PRINTLN(F("NET: recover (soft)"));

  backendHttpClient.stop();

  byte macAddress[6];
  for (uint8_t index = 0; index < 6; index++) {
    macAddress[index] = ProjectSecrets::ethernetMacAddress[index];
  }

  uint8_t dhcpResult = 0;
  for (int attempt = 0; attempt < 3 && dhcpResult == 0; attempt++) {
    dhcpResult = Ethernet.begin(macAddress);
    if (dhcpResult == 0) delay(400);
  }

  if (dhcpResult == 0) {
    usingDhcp = false;
    Ethernet.begin(
      macAddress,
      ProjectSecrets::fallbackStaticIpAddress,
      ProjectSecrets::fallbackDnsAddress,
      ProjectSecrets::fallbackGatewayAddress,
      ProjectSecrets::fallbackSubnetMask
    );
    SERIAL_PRINTLN(F("NET: static fallback (recover)"));
  } else {
    usingDhcp = true;
    SERIAL_PRINTLN(F("NET: DHCP OK (recover)"));
  }

  SERIAL_PRINT(F("NET IP: "));
  SERIAL_PRINTLN(Ethernet.localIP());

  delay(200);
}

static void recoverEthernet() {
  if (millis() - lastEthernetRecoverAtMs < ProjectConfiguration::Timing::ethernetRecoverCooldownMs) {
    return;
  }

  lastEthernetRecoverAtMs = millis();

  SERIAL_PRINTLN(F("NET: recover (hard)"));

  backendHttpClient.stop();
  delay(150);

  Ethernet.init(ProjectConfiguration::HardwarePins::ethernetChipSelectPin);

  byte macAddress[6];
  for (uint8_t index = 0; index < 6; index++) {
    macAddress[index] = ProjectSecrets::ethernetMacAddress[index];
  }

  uint8_t dhcpResult = 0;
  for (int attempt = 0; attempt < 3 && dhcpResult == 0; attempt++) {
    dhcpResult = Ethernet.begin(macAddress);
    if (dhcpResult == 0) delay(700);
  }

  if (dhcpResult == 0) {
    usingDhcp = false;
    Ethernet.begin(
      macAddress,
      ProjectSecrets::fallbackStaticIpAddress,
      ProjectSecrets::fallbackDnsAddress,
      ProjectSecrets::fallbackGatewayAddress,
      ProjectSecrets::fallbackSubnetMask
    );
    SERIAL_PRINTLN(F("NET: static fallback (hard)"));
  } else {
    usingDhcp = true;
    SERIAL_PRINTLN(F("NET: DHCP OK (hard)"));
  }

  delay(600);

  SERIAL_PRINT(F("NET IP: "));
  SERIAL_PRINTLN(Ethernet.localIP());

  if (Ethernet.localIP() == IPAddress(0, 0, 0, 0)) {
    backendReachable = false;
  }
}

static void collectPendingAttendanceTapsFromRadioFrequencyIdentification() {
  char rawFrame[ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength + 1] = {0};
  char cardIdentifier[ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength + 1] = {0};

  while (readRadioFrequencyIdentificationFrame(rawFrame, sizeof(rawFrame))) {
    if (!normalizeCardIdentifier(rawFrame, cardIdentifier, sizeof(cardIdentifier))) {
      continue;
    }

    if (cardIdentifier[0] == '\0') {
      continue;
    }

    unsigned long nowMs = millis();

    if (lastAcceptedCardIdentifier[0] != '\0' &&
        strcmp(cardIdentifier, lastAcceptedCardIdentifier) == 0 &&
        (nowMs - lastAcceptedCardAtMs < ProjectConfiguration::Timing::duplicateCardCooldownMs)) {
      SERIAL_PRINTLN(F("RFID: duplicate (cooldown)"));
      continue;
    }

    if (!tryEnqueuePendingAttendanceTap(cardIdentifier)) {
      SERIAL_PRINTLN(F("QUEUE: full"));
      playQueueFullBeep();
      continue;
    }

    strncpy(lastAcceptedCardIdentifier, cardIdentifier, sizeof(lastAcceptedCardIdentifier) - 1);
    lastAcceptedCardIdentifier[sizeof(lastAcceptedCardIdentifier) - 1] = '\0';
    lastAcceptedCardAtMs = nowMs;

    SERIAL_PRINT(F("RFID: enqueued "));
    SERIAL_PRINT(cardIdentifier);
    SERIAL_PRINT(F(" (count="));
    SERIAL_PRINT(pendingAttendanceTapCount);
    SERIAL_PRINTLN(F(")"));

    playCardDetectedBeep();
  }
}

static bool tryEnqueuePendingAttendanceTap(const char* cardIdentifier) {
  if (pendingAttendanceTapCount >= pendingAttendanceTapCapacity) {
    return false;
  }

  strncpy(
    pendingAttendanceTapQueue[pendingAttendanceTapTail].cardIdentifier,
    cardIdentifier,
    ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength
  );
  pendingAttendanceTapQueue[pendingAttendanceTapTail].cardIdentifier[ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength] = '\0';

  pendingAttendanceTapTail = (uint8_t)((pendingAttendanceTapTail + 1) % pendingAttendanceTapCapacity);
  pendingAttendanceTapCount++;

  return true;
}

static bool tryDequeuePendingAttendanceTap(char* outCardIdentifier, size_t outCardIdentifierSize) {
  if (pendingAttendanceTapCount == 0) {
    return false;
  }

  strncpy(outCardIdentifier, pendingAttendanceTapQueue[pendingAttendanceTapHead].cardIdentifier, outCardIdentifierSize - 1);
  outCardIdentifier[outCardIdentifierSize - 1] = '\0';

  pendingAttendanceTapHead = (uint8_t)((pendingAttendanceTapHead + 1) % pendingAttendanceTapCapacity);
  pendingAttendanceTapCount--;

  return true;
}

static void processPendingAttendanceTapQueue() {
  if (pendingAttendanceTapCount == 0) {
    return;
  }

  if (millis() - lastRadioFrequencyIdentificationByteAtMs < networkSendOnlyAfterRadioFrequencyIdentificationIdleMs) {
    return;
  }

  if (!ethernetLinkUp) {
    return;
  }

  if (Ethernet.localIP() == IPAddress(0, 0, 0, 0)) {
    return;
  }

  if (millis() - ethernetLinkOnlineAtMs < ProjectConfiguration::Timing::postLinkOnlineStabilizationMs) {
    return;
  }

  if (!backendReachable) {
    return;
  }

  unsigned long nowMs = millis();
  if (nowMs < nextBackendSendAttemptAtMs) {
    return;
  }

  char cardIdentifier[ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength + 1] = {0};
  if (!tryDequeuePendingAttendanceTap(cardIdentifier, sizeof(cardIdentifier))) {
    return;
  }

  SERIAL_PRINT(F("HTTP: send "));
  SERIAL_PRINT(cardIdentifier);
  SERIAL_PRINT(F(" (pending="));
  SERIAL_PRINT(pendingAttendanceTapCount);
  SERIAL_PRINTLN(F(")"));

  int statusCode = sendAttendanceTap(cardIdentifier);

  SERIAL_PRINT(F("HTTP: status "));
  SERIAL_PRINTLN(statusCode);

  if (statusCode <= 0) {
    backendWasHealthy = false;
    consecutiveHttpFailures++;

    if (consecutiveHttpFailures >= ProjectConfiguration::Thresholds::httpFailuresToTriggerEthernetRecovery) {
      recoverEthernet();
      consecutiveHttpFailures = 0;
    }

    tryEnqueuePendingAttendanceTap(cardIdentifier);

    if (backendSendBackoffMs == 0) {
      backendSendBackoffMs = backendSendBackoffMinimumMs;
    } else {
      backendSendBackoffMs = min(backendSendBackoffMs * 2UL, backendSendBackoffMaximumMs);
    }

    nextBackendSendAttemptAtMs = millis() + backendSendBackoffMs;

    if (millis() - lastBackendBeepAtMs >= ProjectConfiguration::Timing::backendErrorBeepIntervalMs) {
      lastBackendBeepAtMs = millis();
      playBackendErrorBeep();
    }

    return;
  }

  consecutiveHttpFailures = 0;
  backendSendBackoffMs = 0;
  nextBackendSendAttemptAtMs = 0;

  if (!isBackendHealthy(statusCode)) {
    backendWasHealthy = false;

    tryEnqueuePendingAttendanceTap(cardIdentifier);

    backendSendBackoffMs = backendSendBackoffMinimumMs;
    nextBackendSendAttemptAtMs = millis() + backendSendBackoffMs;

    if (millis() - lastBackendBeepAtMs >= ProjectConfiguration::Timing::backendErrorBeepIntervalMs) {
      lastBackendBeepAtMs = millis();
      playBackendErrorBeep();
    }

    return;
  }

  if (!backendWasHealthy) {
    playRecoveredConnectivityBeep();
  }

  backendWasHealthy = true;

  if (statusCode == 200) {
    playCardOkBeep();
    return;
  }

  if (statusCode == 404) {
    playCardRejectedBeep();
    return;
  }
}

static bool readRadioFrequencyIdentificationFrame(char* outFrame, size_t outFrameSize) {
  static char buffer[ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength + 1];
  static uint8_t length = 0;
  static unsigned long startedAtMs = 0;
  static unsigned long lastByteAtMs = 0;

  radioFrequencyIdentificationTimedOut = false;

  auto isHexCharacter = [](uint8_t c) -> bool {
    return (c >= '0' && c <= '9') || (c >= 'A' && c <= 'F') || (c >= 'a' && c <= 'f');
  };

  auto toUppercaseHexCharacter = [](uint8_t c) -> char {
    if (c >= 'a' && c <= 'f') return (char)(c - 32);
    return (char)c;
  };

  while (radioFrequencyIdentificationSerialPort.available() > 0) {
    uint8_t readByte = (uint8_t)radioFrequencyIdentificationSerialPort.read();
    unsigned long nowMs = millis();
    lastRadioFrequencyIdentificationByteAtMs = nowMs;

    if (!radioFrequencyIdentificationCollectingNow) {
      if (readByte != 0x02) {
        continue;
      }

      radioFrequencyIdentificationCollectingNow = true;
      length = 0;
      startedAtMs = nowMs;
      lastByteAtMs = nowMs;
      continue;
    }

    if (readByte == 0x03 || readByte == '\r' || readByte == '\n') {
      radioFrequencyIdentificationCollectingNow = false;
      length = 0;
      continue;
    }

    if (!isHexCharacter(readByte)) {
      continue;
    }

    if (length < ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength) {
      buffer[length++] = toUppercaseHexCharacter(readByte);
      lastByteAtMs = nowMs;
    }

    if (length == ProjectConfiguration::RadioFrequencyIdentificationFrame::expectedHexLength) {
      buffer[length] = '\0';

      if (outFrameSize == 0) {
        radioFrequencyIdentificationCollectingNow = false;
        length = 0;
        return false;
      }

      strncpy(outFrame, buffer, outFrameSize - 1);
      outFrame[outFrameSize - 1] = '\0';

      radioFrequencyIdentificationCollectingNow = false;
      length = 0;

      return true;
    }

    if (nowMs - startedAtMs > ProjectConfiguration::RadioFrequencyIdentificationFrame::totalFrameTimeoutMs) {
      radioFrequencyIdentificationCollectingNow = false;
      length = 0;
      radioFrequencyIdentificationTimedOut = true;
      SERIAL_PRINTLN(F("RFID: frame timeout"));
      return false;
    }
  }

  if (radioFrequencyIdentificationCollectingNow) {
    unsigned long nowMs = millis();
    if ((nowMs - lastByteAtMs >= ProjectConfiguration::RadioFrequencyIdentificationFrame::interByteTimeoutMs) ||
        (nowMs - startedAtMs > ProjectConfiguration::RadioFrequencyIdentificationFrame::totalFrameTimeoutMs)) {
      radioFrequencyIdentificationCollectingNow = false;
      length = 0;
      radioFrequencyIdentificationTimedOut = true;
      SERIAL_PRINTLN(F("RFID: inter-byte timeout"));
    }
  }

  return false;
}

static bool normalizeCardIdentifier(const char* raw, char* outIdentifier, size_t outIdentifierSize) {
  if (outIdentifierSize == 0) {
    return false;
  }

  uint8_t writeIndex = 0;
  for (uint8_t readIndex = 0; raw[readIndex] != '\0'; readIndex++) {
    char c = raw[readIndex];

    bool isDigit = (c >= '0' && c <= '9');
    bool isUpperHex = (c >= 'A' && c <= 'F');
    bool isLowerHex = (c >= 'a' && c <= 'f');

    if (!(isDigit || isUpperHex || isLowerHex)) {
      continue;
    }

    if (isLowerHex) {
      c = (char)(c - 32);
    }

    if (writeIndex + 1 >= outIdentifierSize) {
      break;
    }

    outIdentifier[writeIndex++] = c;
  }

  outIdentifier[writeIndex] = '\0';
  return true;
}

static int sendAttendanceTap(const char* cardIdentifier) {
  backendHttpClient.stop();
  backendHttpClient.setTimeout(ProjectConfiguration::Timing::httpFirstByteTimeoutMs);

  char payload[96];
  int payloadLength = snprintf(payload, sizeof(payload), "{\"cardIdentifier\":\"%s\"}", cardIdentifier);
  if (payloadLength <= 0 || payloadLength >= (int)sizeof(payload)) {
    SERIAL_PRINTLN(F("HTTP: payload error"));
    return -1;
  }

  if (!backendHttpClient.connect(ProjectSecrets::backendServerAddress, ProjectConfiguration::Backend::port)) {
    backendHttpClient.stop();
    SERIAL_PRINTLN(F("HTTP: connect failed"));
    return -1;
  }

  backendHttpClient.print(F("POST "));
  backendHttpClient.print(ProjectConfiguration::Backend::attendanceTapPath);
  backendHttpClient.println(F(" HTTP/1.0"));

  backendHttpClient.print(F("Host: "));
  backendHttpClient.print(ProjectSecrets::backendServerAddress);
  backendHttpClient.println();

  backendHttpClient.println(F("Connection: close"));
  backendHttpClient.println(F("Accept: application/json"));
  backendHttpClient.println(F("Content-Type: application/json"));

  backendHttpClient.print(F("X-Attendance-Device-Token: "));
  backendHttpClient.println(ProjectSecrets::attendanceDeviceToken);

  backendHttpClient.print(F("Content-Length: "));
  backendHttpClient.println(payloadLength);
  backendHttpClient.println();

  backendHttpClient.write((const uint8_t*)payload, payloadLength);

  int statusCode = readHttpStatusCode(ProjectConfiguration::Timing::httpFirstByteTimeoutMs);

  unsigned long drainStartedAtMs = millis();
  while ((backendHttpClient.connected() || backendHttpClient.available()) && (millis() - drainStartedAtMs < 3000)) {
    collectPendingAttendanceTapsFromRadioFrequencyIdentification();

    while (backendHttpClient.available()) {
      backendHttpClient.read();
      drainStartedAtMs = millis();
    }
  }

  backendHttpClient.stop();
  return statusCode;
}

static int readHttpStatusCode(unsigned long timeoutMs) {
  backendHttpClient.setTimeout(timeoutMs);

  unsigned long startedAtMs = millis();
  while (!backendHttpClient.available()) {
    collectPendingAttendanceTapsFromRadioFrequencyIdentification();

    if (!backendHttpClient.connected()) {
      SERIAL_PRINTLN(F("HTTP: closed before response"));
      return -1;
    }

    if (millis() - startedAtMs >= timeoutMs) {
      SERIAL_PRINTLN(F("HTTP: timeout first byte"));
      return -1;
    }

    delay(1);
  }

  char statusLine[64];
  size_t readCount = backendHttpClient.readBytesUntil('\n', statusLine, sizeof(statusLine) - 1);

  if (readCount == 0) {
    SERIAL_PRINTLN(F("HTTP: no status line"));
    return -1;
  }

  statusLine[readCount] = '\0';

  if (readCount > 0 && statusLine[readCount - 1] == '\r') {
    statusLine[readCount - 1] = '\0';
  }

  char* firstSpace = strchr(statusLine, ' ');
  if (!firstSpace) {
    SERIAL_PRINTLN(F("HTTP: bad status line"));
    return -1;
  }

  int statusCode = atoi(firstSpace + 1);
  return statusCode > 0 ? statusCode : -1;
}

static bool isBackendHealthy(int statusCode) {
  if (statusCode < 0) return false;
  if (statusCode >= 500) return false;
  return true;
}

static void playCardDetectedBeep() {
  tone(ProjectConfiguration::HardwarePins::speakerOutputPin, 988, 40);
}

static void playCardOkBeep() {
  tone(ProjectConfiguration::HardwarePins::speakerOutputPin, 1047, 90);
}

static void playRecoveredConnectivityBeep() {
  tone(ProjectConfiguration::HardwarePins::speakerOutputPin, 784, 90);
}

static void playBackendErrorBeep() {
  tone(ProjectConfiguration::HardwarePins::speakerOutputPin, 440, 120);
}

static void playCardRejectedBeep() {
  tone(ProjectConfiguration::HardwarePins::speakerOutputPin, 262, 140);
}

static void playQueueFullBeep() {
  tone(ProjectConfiguration::HardwarePins::speakerOutputPin, 196, 220);
}

static void printBootState() {
  SERIAL_PRINTLN(F("BOOT: serial ok"));
  SERIAL_PRINT(F("BOOT: logging "));
  SERIAL_PRINTLN(SERIAL_LOGGING_ENABLED ? F("ON") : F("OFF"));
}

static void printHeartbeat() {
  static unsigned long lastHeartbeatAtMs = 0;
  if (millis() - lastHeartbeatAtMs < 1000) {
    return;
  }

  lastHeartbeatAtMs = millis();

  SERIAL_PRINT(F("HEARTBEAT ms="));
  SERIAL_PRINT(millis());
  SERIAL_PRINT(F(" link="));
  SERIAL_PRINT(ethernetLinkUp ? F("ON") : F("OFF"));
  SERIAL_PRINT(F(" backend="));
  SERIAL_PRINT(backendReachable ? F("ON") : F("OFF"));
  SERIAL_PRINT(F(" pending="));
  SERIAL_PRINT(pendingAttendanceTapCount);
  SERIAL_PRINT(F(" ip="));
  SERIAL_PRINTLN(Ethernet.localIP());
}
