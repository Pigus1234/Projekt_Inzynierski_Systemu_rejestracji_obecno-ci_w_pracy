#pragma once
#include <Arduino.h>
#include <Ethernet.h>

namespace ProjectSecrets {

static constexpr byte ethernetMacAddress[6] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

static const IPAddress backendServerAddress(xxx, xxx, xxx, xxx);
static constexpr const char* attendanceDeviceToken = "xxx";

static const IPAddress fallbackStaticIpAddress(xxx, xxx, xxx, xxx);
static const IPAddress fallbackDnsAddress(xxx, xxx, xxx, xxx);
static const IPAddress fallbackGatewayAddress(xxx, xxx, xxx, xxx);
static const IPAddress fallbackSubnetMask(255, 255, 255, 0);

}
