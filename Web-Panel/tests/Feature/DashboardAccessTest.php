<?php

namespace Tests\Feature;

use App\Attendance\AttendanceEventType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_open_dashboard_and_sees_basic_layout_elements(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard');

        $contentTypeHeader = $response->headers->get('Content-Type');
        $this->assertNotNull($contentTypeHeader);
        $this->assertStringContainsString('text/html', $contentTypeHeader);
        $this->assertStringContainsString('charset=utf-8', strtolower($contentTypeHeader));

        $response->assertSeeText('Pulpit');
        $response->assertSeeText($user->name);
        $response->assertSeeText('Wyloguj');
    }

    public function test_dashboard_controller_provides_correct_metrics_and_statuses(): void
    {
        $user = User::factory()->create();

        $now = now();

        DB::table('attendance_devices')->insert([
            [
                'name' => 'Device Online',
                'api_token_hash' => hash('sha256', 'token-1'),
                'is_active' => true,
                'last_seen_at' => $now->copy()->subMinutes(1),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Device Offline',
                'api_token_hash' => hash('sha256', 'token-2'),
                'is_active' => true,
                'last_seen_at' => $now->copy()->subMinutes(10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Device Inactive',
                'api_token_hash' => hash('sha256', 'token-3'),
                'is_active' => false,
                'last_seen_at' => $now->copy()->subMinutes(1),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('employees')->insert([
            [
                'rfid_uid' => 'A1B2C3D4',
                'rfid_card_identifier' => 'CARD-A1',
                'full_name' => 'Jan Kowalski',
                'department' => 'Produkcja',
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rfid_uid' => 'B1B2C3D4',
                'rfid_card_identifier' => 'CARD-B1',
                'full_name' => 'Anna Nowak',
                'department' => 'Magazyn',
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rfid_uid' => 'C1B2C3D4',
                'rfid_card_identifier' => 'CARD-C1',
                'full_name' => 'Piotr Zieliński',
                'department' => 'Magazyn',
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $employeeIds = DB::table('employees')->orderBy('id')->pluck('id')->all();
        $employeeId1 = $employeeIds[0];
        $employeeId2 = $employeeIds[1];
        $employeeId3 = $employeeIds[2];

        $deviceId = DB::table('attendance_devices')->where('name', 'Device Online')->value('id');

        DB::table('attendance_events')->insert([
            [
                'employee_id' => $employeeId1,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::Exit->value,
                'occurred_at' => $now->copy()->subHours(3),
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeId1,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::Entry->value,
                'occurred_at' => $now->copy()->subHours(1),
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeId2,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::Entry->value,
                'occurred_at' => $now->copy()->subHours(2),
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeId3,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::Entry->value,
                'occurred_at' => $now->copy()->subHours(4),
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeId3,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::Exit->value,
                'occurred_at' => $now->copy()->subHours(1),
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => null,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::UnknownCardAttempt->value,
                'occurred_at' => $now->copy()->subHours(5),
                'metadata' => json_encode(['rfid_uid' => 'FFFF0001']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => null,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::UnknownCardAttempt->value,
                'occurred_at' => $now->copy()->subHours(6),
                'metadata' => json_encode(['rfid_uid' => 'FFFF0002']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => null,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::UnknownCardAttempt->value,
                'occurred_at' => $now->copy()->subHours(7),
                'metadata' => json_encode(['rfid_uid' => 'FFFF0003']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => null,
                'attendance_device_id' => $deviceId,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::UnknownCardAttempt->value,
                'occurred_at' => $now->copy()->subHours(30),
                'metadata' => json_encode(['rfid_uid' => 'OLD00001']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard');

        $response->assertViewHas('databaseStatusValue', 'OK');
        $response->assertViewHas('databaseStatusState', 'ok');

        $response->assertViewHas('rfidStatusValue', '1 / 2');
        $response->assertViewHas('rfidStatusState', 'ok');

        $response->assertViewHas('presentEmployeesCount', 2);
        $response->assertViewHas('attendanceEventsCountLast24Hours', 8);
        $response->assertViewHas('unknownCardAttemptsCountLast24Hours', 3);

        $response->assertSeeText('Pracownicy na terenie');
        $response->assertSeeText('Odbicia (ostatnie 24h)');
        $response->assertSeeText('Status systemu');
    }
}
