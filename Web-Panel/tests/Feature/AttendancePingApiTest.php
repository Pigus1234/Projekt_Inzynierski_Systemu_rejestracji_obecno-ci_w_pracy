<?php

namespace Tests\Feature;

use App\Models\AttendanceDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ping_requires_device_authentication(): void
    {
        $response = $this->getJson('/api/attendance/ping');

        $response->assertStatus(401);
    }

    public function test_ping_returns_ok_for_authenticated_device(): void
    {
        $plainToken = 'test-device-token';
        $attendanceDevice = AttendanceDevice::query()->create([
            'name' => 'Test Device',
            'api_token_hash' => hash('sha256', $plainToken),
            'is_active' => true,
            'last_seen_at' => null,
        ]);

        $response = $this
            ->withHeader('X-Attendance-Device-Token', $plainToken)
            ->getJson('/api/attendance/ping');

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
        ]);
    }
}
