<?php

namespace Tests\Feature;

use App\Models\AttendanceDevice;
use App\Models\AttendanceEvent;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTapApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tap_requires_device_authentication(): void
    {
        $response = $this->postJson('/api/attendance/tap', [
            'cardIdentifier' => 'A1B2C3D4',
        ]);

        $response->assertStatus(401);
    }

    public function test_unknown_employee_returns_404_and_creates_single_unknown_event_with_duplicate_lock(): void
    {
        config(['attendance.duplicate_event_lock_seconds' => 8]);

        [$attendanceDevice, $plainToken] = $this->createAttendanceDevice();

        $response = $this
            ->withHeader('X-Attendance-Device-Token', $plainToken)
            ->postJson('/api/attendance/tap', [
                'cardIdentifier' => 'aa:bb-cc dd',
            ]);

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
            'error' => [
                'code' => 'employee_not_found',
            ],
        ]);

        $this->assertDatabaseHas('attendance_events', [
            'employee_id' => null,
            'attendance_device_id' => $attendanceDevice->id,
            'event_type' => 'unknown_card_attempt',
            'metadata->rfidUid' => 'AABBCCDD',
            'metadata->errorCode' => 'employee_not_found',
        ]);

        $countAfterFirst = AttendanceEvent::query()
            ->whereNull('employee_id')
            ->where('event_type', 'unknown_card_attempt')
            ->where('metadata->rfidUid', 'AABBCCDD')
            ->count();

        $this->assertSame(1, $countAfterFirst);

        $response2 = $this
            ->withHeader('X-Attendance-Device-Token', $plainToken)
            ->postJson('/api/attendance/tap', [
                'cardIdentifier' => 'aa:bb-cc dd',
            ]);

        $response2->assertStatus(404);

        $countAfterSecond = AttendanceEvent::query()
            ->whereNull('employee_id')
            ->where('event_type', 'unknown_card_attempt')
            ->where('metadata->rfidUid', 'AABBCCDD')
            ->count();

        $this->assertSame(1, $countAfterSecond);
    }

    public function test_known_employee_creates_entry_then_duplicate_returns_existing_then_after_lock_creates_exit(): void
    {
        config(['attendance.duplicate_event_lock_seconds' => 8]);

        [$attendanceDevice, $plainToken] = $this->createAttendanceDevice();

        $employee = Employee::query()->create([
            'rfid_uid' => 'DEADBEEF',
            'full_name' => 'Jan Kowalski',
            'department' => 'Produkcja',
        ]);

        $response1 = $this
            ->withHeader('X-Attendance-Device-Token', $plainToken)
            ->postJson('/api/attendance/tap', [
                'cardIdentifier' => 'de:ad-be ef',
            ]);

        $response1->assertOk();
        $response1->assertJson([
            'status' => 'ok',
            'createdNewEvent' => true,
            'employee' => [
                'id' => $employee->id,
            ],
            'event' => [
                'type' => 'entry',
            ],
            'presenceStatus' => 'present',
        ]);

        $firstEventId = (int) $response1->json('event.id');

        $response2 = $this
            ->withHeader('X-Attendance-Device-Token', $plainToken)
            ->postJson('/api/attendance/tap', [
                'cardIdentifier' => 'DE AD BE EF',
            ]);

        $response2->assertOk();
        $response2->assertJson([
            'status' => 'ok',
            'createdNewEvent' => false,
            'employee' => [
                'id' => $employee->id,
            ],
            'event' => [
                'id' => $firstEventId,
                'type' => 'entry',
            ],
            'presenceStatus' => 'present',
        ]);

        $this->travel(9)->seconds();

        $response3 = $this
            ->withHeader('X-Attendance-Device-Token', $plainToken)
            ->postJson('/api/attendance/tap', [
                'cardIdentifier' => 'DEADBEEF',
            ]);

        $response3->assertOk();
        $response3->assertJson([
            'status' => 'ok',
            'createdNewEvent' => true,
            'employee' => [
                'id' => $employee->id,
            ],
            'event' => [
                'type' => 'exit',
            ],
            'presenceStatus' => 'absent',
        ]);

        $this->assertDatabaseHas('attendance_events', [
            'employee_id' => $employee->id,
            'attendance_device_id' => $attendanceDevice->id,
            'event_type' => 'exit',
            'metadata->rfidUid' => 'DEADBEEF',
        ]);
    }

    public function test_soft_deleted_employee_is_not_found_and_is_logged_as_unknown_attempt(): void
    {
        config(['attendance.duplicate_event_lock_seconds' => 8]);

        [$attendanceDevice, $plainToken] = $this->createAttendanceDevice();

        $employee = Employee::query()->create([
            'rfid_uid' => 'CAFEBABE',
            'full_name' => 'Anna Nowak',
            'department' => null,
        ]);

        $employee->delete();

        $response = $this
            ->withHeader('X-Attendance-Device-Token', $plainToken)
            ->postJson('/api/attendance/tap', [
                'cardIdentifier' => 'CAFE-BABE',
            ]);

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
            'error' => [
                'code' => 'employee_not_found',
            ],
        ]);

        $this->assertDatabaseHas('attendance_events', [
            'employee_id' => null,
            'attendance_device_id' => $attendanceDevice->id,
            'event_type' => 'unknown_card_attempt',
            'metadata->rfidUid' => 'CAFEBABE',
            'metadata->errorCode' => 'employee_not_found',
        ]);
    }

    private function createAttendanceDevice(): array
    {
        $plainToken = 'test-device-token';

        $attendanceDevice = AttendanceDevice::query()->create([
            'name' => 'Test Device',
            'api_token_hash' => hash('sha256', $plainToken),
            'is_active' => true,
            'last_seen_at' => null,
        ]);

        return [$attendanceDevice, $plainToken];
    }
}
