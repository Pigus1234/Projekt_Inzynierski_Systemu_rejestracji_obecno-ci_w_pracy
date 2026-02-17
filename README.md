# Projekt_Inzynierski_Systemu_rejestracji_obecno-ci_w_pracy
This repository contains an engineering thesis project: an RFID-based attendance registration system split into two parts:
- **Arduino device** (RFID reader + buzzer) — reads card UID and communicates with the web API.
- **Web Panel** (Laravel) — administration panel + attendance views + API for attendance devices.

## Repository structure

- [`Arduino/`](./Arduino) — firmware for the attendance device (RFID).
- [`Web-Panel/`](./Web-Panel) — Laravel web application (admin panel + API).

## How it works (high level)

1. An employee taps an RFID card/tag on the Arduino device.
2. The device reads the RFID UID and sends an event to the Web Panel API.
3. The Web Panel stores and displays attendance information in the admin UI.

## Key features

### Web Panel
- Authentication (Breeze).
- Dashboard.
- Employees management (including archive/restore).
- Departments management.
- Attendance:
  - Present list
  - Evacuation list print
  - Changelog (filterable)
- Attendance devices (admin-only):
  - Create device and generate token
  - Rotate token
  - Activate / deactivate

### Device API
- `GET  /api/attendance/ping` — connectivity/health check for the device.
- `POST /api/attendance/tap` — register a “tap” (RFID UID event).

## Quick start (recommended) — Docker (Laravel Sail)

### Requirements
- Docker + Docker Compose

### Run Web Panel (Sail)
```bash
cd Web-Panel

cp .env.example .env

composer install
npm install

# If you have a "sail" alias, use it. Otherwise: ./vendor/bin/sail
./vendor/bin/sail up -d

./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan db:seed --class=DemoDatabaseSeeder

npm run dev
```

Open the application in a browser (commonly `http://localhost`).

## Local install (no Docker)

### Requirements
- PHP + Composer
- Node.js + npm
- MySQL (or compatible database)

### Run Web Panel (direct install)
```bash
cd Web-Panel

cp .env.example .env
# Configure DB_* and APP_URL in .env

composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoDatabaseSeeder

npm install
npm run dev

php artisan serve
```

> If you see “Vite manifest not found”, run `npm run dev` or `npm run build`.

## Arduino device (high level)

1. Open the firmware project from [`Arduino/`](./Arduino) in Arduino IDE / PlatformIO.
2. Configure:
   - Network settings (Ethernet/Wi‑Fi module, if applicable)
   - Web API base URL (pointing to the Web Panel)
   - Device token (generated in Web Panel → Attendance Devices)
3. Upload the firmware to the board.
4. Test connectivity via the device diagnostics / `GET /api/attendance/ping`.

## Testing (Web-Panel)

### Sail
```bash
sail artisan test; sail artisan migrate:fresh --seed; sail artisan db:seed --class=DemoDatabaseSeeder
```

### Local (no Docker)
```bash
php artisan test; php artisan migrate:fresh --seed; php artisan db:seed --class=DemoDatabaseSeeder
```

## Security notes

- Treat device tokens as secrets (rotate if exposed).
- Change any default/demo credentials after first setup.
- Do not commit `.env` files.