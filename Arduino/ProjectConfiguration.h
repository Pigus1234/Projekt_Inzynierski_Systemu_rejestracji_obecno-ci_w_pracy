#pragma once
#include <Arduino.h>

namespace ProjectConfiguration {

struct HardwarePins final {
  static constexpr uint8_t speakerOutputPin = 7;
  static constexpr uint8_t ethernetChipSelectPin = 10;
  static constexpr uint8_t storageChipSelectPin = 4;
};

struct Backend final {
  static constexpr uint16_t port = 80;
  static constexpr const char* attendanceTapPath = "/api/attendance/tap";
};

struct Timing final {
  static constexpr unsigned long duplicateCardCooldownMs = 700;

  static constexpr unsigned long radioFrequencyIdentificationQuietToResetLastCardMs = 250;

  static constexpr unsigned long httpFirstByteTimeoutMs = 20000;

  static constexpr unsigned long backendErrorBeepIntervalMs = 5000;

  static constexpr unsigned long connectivityProbeIntervalMs = 2000;
  static constexpr unsigned long ethernetRecoverCooldownMs = 8000;

  static constexpr unsigned long postLinkOnlineStabilizationMs = 1500;

  static constexpr unsigned long networkSendOnlyAfterRadioFrequencyIdentificationIdleMs = 400;

  static constexpr unsigned long backendSendBackoffMinimumMs = 250;
  static constexpr unsigned long backendSendBackoffMaximumMs = 8000;
};

struct Thresholds final {
  static constexpr uint8_t httpFailuresToTriggerEthernetRecovery = 2;
  static constexpr uint8_t probeFailuresToMarkBackendOffline = 2;
};

struct Capacities final {
  static constexpr uint8_t pendingAttendanceTapCapacity = 24;
  static constexpr uint8_t audioSequenceCapacity = 8;
};

struct EthernetController final {
  static constexpr uint16_t retransmissionTimeoutMs = 200;
  static constexpr uint8_t retransmissionRetryCount = 3;
};

struct RadioFrequencyIdentificationFrame final {
  static constexpr uint8_t expectedHexLength = 12;
  static constexpr unsigned long interByteTimeoutMs = 80;
  static constexpr unsigned long totalFrameTimeoutMs = 250;
};

}