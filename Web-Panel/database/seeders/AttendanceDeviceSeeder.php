<?php

namespace Database\Seeders;

use App\Models\AttendanceDevice;
use Illuminate\Database\Seeder;
use RuntimeException;

class AttendanceDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $deviceName = (string) env('ATTENDANCE_PRIMARY_DEVICE_NAME', 'Arduino RFID Reader');
        $plainToken = env('ATTENDANCE_PRIMARY_DEVICE_TOKEN');

        if (!is_string($plainToken) || $plainToken === '') {
            throw new RuntimeException('Missing ATTENDANCE_PRIMARY_DEVICE_TOKEN in environment.');
        }

        AttendanceDevice::query()->updateOrCreate(
            ['name' => $deviceName],
            [
                'api_token_hash' => hash('sha256', $plainToken),
                'is_active' => true,
            ],
        );
    }
}
