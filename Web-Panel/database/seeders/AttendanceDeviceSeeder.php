<?php

namespace Database\Seeders;

use App\Models\AttendanceDevice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AttendanceDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $plainToken = 'local-development-attendance-device-token';
        $attributes = [
            'api_token_hash' => hash('sha256', $plainToken),
            'is_active' => true,
        ];

        if (Schema::hasColumn('attendance_devices', 'last_seen_at')) {
            $attributes['last_seen_at'] = now();
        }

        AttendanceDevice::query()->updateOrCreate(
            ['name' => 'Urządzenie testowe'],
            $attributes
        );
    }
}
