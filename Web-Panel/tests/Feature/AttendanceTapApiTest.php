<?php

namespace Tests\Feature;

use App\Models\AttendanceDevice;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceTapApiTest extends TestCase
{
    use RefreshDatabase;

    public function testRequestRequiresDeviceToken(): void
    {
        $response = $this->postJson('/api/attendance/tap', [
            'cardIdentifier' => 'A1B2C3D4',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseCount('attendance_events', 0);
    }

    public function testRequestReturnsNotFoundWhenEmployeeDoesNotExist(): void
    {
        $token = 'test-token';
        $this->createAttendanceDevice($token);

        $response = $this->withHeaders([
            'X-Attendance-Device-Token' => $token,
        ])->postJson('/api/attendance/tap', [
            'cardIdentifier' => 'A1B2C3D4',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseCount('attendance_events', 0);
    }

    public function testRequestCreatesEntryThenExit(): void
    {
        $token = 'test-token';
        $this->createAttendanceDevice($token);

        $employee = Employee::query()->create([
            'rfid_uid' => 'A1B2C3D4',
            'full_name' => 'Jan Kowalski',
            'department' => 'Magazyn',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-01-07 10:00:00'));

        $firstResponse = $this->withHeaders([
            'X-Attendance-Device-Token' => $token,
        ])->postJson('/api/attendance/tap', [
            'cardIdentifier' => 'A1:B2:C3:D4',
        ]);

        $firstResponse->assertOk();
        $firstResponse->assertJsonPath('event.type', 'entry');

        Carbon::setTestNow(Carbon::parse('2026-01-07 10:00:20'));

        $secondResponse = $this->withHeaders([
            'X-Attendance-Device-Token' => $token,
        ])->postJson('/api/attendance/tap', [
            'cardIdentifier' => 'A1B2C3D4',
        ]);

        $secondResponse->assertOk();
        $secondResponse->assertJsonPath('event.type', 'exit');

        $this->assertDatabaseCount('attendance_events', 2);

        $this->assertDatabaseHas('attendance_events', [
            'employee_id' => $employee->id,
            'event_type' => 'entry',
        ]);

        $this->assertDatabaseHas('attendance_events', [
            'employee_id' => $employee->id,
            'event_type' => 'exit',
        ]);
    }

    public function testRequestDoesNotCreateDuplicateEventWithinLockWindow(): void
    {
        config(['attendance.duplicate_event_lock_seconds' => 60]);

        $token = 'test-token';
        $this->createAttendanceDevice($token);

        Employee::query()->create([
            'rfid_uid' => 'A1B2C3D4',
            'full_name' => 'Jan Kowalski',
            'department' => 'Magazyn',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-01-07 10:00:00'));

        $firstResponse = $this->withHeaders([
            'X-Attendance-Device-Token' => $token,
        ])->postJson('/api/attendance/tap', [
            'cardIdentifier' => 'A1B2C3D4',
        ]);

        $firstResponse->assertOk();
        $this->assertDatabaseCount('attendance_events', 1);

        $secondResponse = $this->withHeaders([
            'X-Attendance-Device-Token' => $token,
        ])->postJson('/api/attendance/tap', [
            'cardIdentifier' => 'A1B2C3D4',
        ]);

        $secondResponse->assertOk();
        $this->assertDatabaseCount('attendance_events', 1);
        $secondResponse->assertJsonPath('createdNewEvent', false);
    }

    private function createAttendanceDevice(string $plainToken): AttendanceDevice
    {
        return AttendanceDevice::query()->create([
            'name' => 'Test Device',
            'api_token_hash' => hash('sha256', $plainToken),
            'is_active' => true,
        ]);
    }
}
