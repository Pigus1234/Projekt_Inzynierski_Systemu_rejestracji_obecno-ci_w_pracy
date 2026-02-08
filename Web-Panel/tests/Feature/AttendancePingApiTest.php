<?php

namespace Tests\Feature;

use App\Models\AttendanceDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendancePingApiTest extends TestCase
{
    use RefreshDatabase;

    public function testPingUpdatesLastSeenAt(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-02 21:00:00'));

        $token = 'test-token';
        $device = AttendanceDevice::query()->create([
            'name' => 'Test Device',
            'api_token_hash' => hash('sha256', $token),
            'is_active' => true,
            'last_seen_at' => null,
        ]);

        $this->withHeaders([
            'X-Attendance-Device-Token' => $token,
        ])->getJson('/api/attendance/ping')
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $device->refresh();

        $this->assertSame('2026-02-02 21:00:00', $device->last_seen_at->format('Y-m-d H:i:s'));
    }
}
