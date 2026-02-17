<?php

namespace Tests\Feature;

use App\Attendance\AttendanceEventType;
use App\Models\AttendanceEvent;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceChangelogAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_changelog(): void
    {
        $response = $this->get(route('attendance.changelog'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_without_permission_is_forbidden(): void
    {
        $user = $this->createStandardUser();

        $response = $this->actingAs($user)->get(route('attendance.changelog'));

        $response->assertForbidden();
    }

    public function test_administrator_can_open_changelog_without_explicit_permission(): void
    {
        $administrator = $this->createAdministratorUser();

        $response = $this->actingAs($administrator)->get(route('attendance.changelog'));

        $response->assertOk();
        $response->assertViewIs('attendance.changelog');
        $response->assertSeeText('Historia odbić');
        $response->assertSeeText('Zdarzenia:');
        $response->assertSeeText('Filtruj');
    }

    public function test_user_with_permission_can_open_changelog_and_sees_table_headers_and_unknown_card_label(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'attendance.changelog.view', 'Widok na historię odbić');

        AttendanceEvent::query()->create([
            'employee_id' => null,
            'attendance_device_id' => null,
            'recorded_by_user_id' => null,
            'event_type' => AttendanceEventType::UnknownCardAttempt,
            'occurred_at' => Carbon::parse('2026-02-10 10:00:00'),
            'metadata' => [
                'rfidUid' => 'AABBCCDD',
                'errorCode' => 'employee_not_found',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('attendance.changelog'));

        $response->assertOk();
        $response->assertViewIs('attendance.changelog');

        $response->assertSeeText('Historia odbić');
        $response->assertSeeText('Data');
        $response->assertSeeText('Typ');
        $response->assertSeeText('Pracownik');
        $response->assertSeeText('Dział');

        $response->assertSeeText('Nieznana karta');
    }

    public function test_filters_work_for_employee_department_event_type_and_date_range(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'attendance.changelog.view', 'Widok na historię odbić');

        $employeeJan = Employee::query()->create([
            'rfid_uid' => 'JAN00001',
            'full_name' => 'Jan Kowalski',
            'department' => 'Magazyn',
        ]);

        $employeeAnna = Employee::query()->create([
            'rfid_uid' => 'ANN00001',
            'full_name' => 'Anna Nowak',
            'department' => 'Produkcja',
        ]);

        AttendanceEvent::query()->create([
            'employee_id' => $employeeJan->id,
            'attendance_device_id' => null,
            'recorded_by_user_id' => null,
            'event_type' => AttendanceEventType::Entry,
            'occurred_at' => Carbon::parse('2026-02-10 08:00:00'),
            'metadata' => ['rfidUid' => 'JAN00001'],
        ]);

        AttendanceEvent::query()->create([
            'employee_id' => $employeeAnna->id,
            'attendance_device_id' => null,
            'recorded_by_user_id' => null,
            'event_type' => AttendanceEventType::Exit,
            'occurred_at' => Carbon::parse('2026-02-10 09:00:00'),
            'metadata' => ['rfidUid' => 'ANN00001'],
        ]);

        AttendanceEvent::query()->create([
            'employee_id' => null,
            'attendance_device_id' => null,
            'recorded_by_user_id' => null,
            'event_type' => AttendanceEventType::UnknownCardAttempt,
            'occurred_at' => Carbon::parse('2026-02-11 12:00:00'),
            'metadata' => [
                'rfidUid' => 'DEADBEEF',
                'errorCode' => 'employee_not_found',
            ],
        ]);

        $responseEmployee = $this->actingAs($user)->get(route('attendance.changelog', [
            'employee' => 'Jan',
        ]));

        $responseEmployee->assertOk();
        $responseEmployee->assertSeeText('Zdarzenia: 1');
        $responseEmployee->assertSeeText('Jan Kowalski');
        $responseEmployee->assertDontSeeText('Anna Nowak');

        $responseDepartment = $this->actingAs($user)->get(route('attendance.changelog', [
            'department' => 'Produkcja',
        ]));

        $responseDepartment->assertOk();
        $responseDepartment->assertSeeText('Zdarzenia: 1');
        $responseDepartment->assertSeeText('Anna Nowak');
        $responseDepartment->assertDontSeeText('Jan Kowalski');

        $responseEventType = $this->actingAs($user)->get(route('attendance.changelog', [
            'eventType' => AttendanceEventType::Entry->value,
        ]));

        $responseEventType->assertOk();
        $responseEventType->assertSeeText('Zdarzenia: 1');
        $responseEventType->assertSeeText('Jan Kowalski');
        $responseEventType->assertDontSeeText('Anna Nowak');

        $responseDateRange = $this->actingAs($user)->get(route('attendance.changelog', [
            'dateFrom' => '2026-02-10',
            'dateTo' => '2026-02-10',
        ]));

        $responseDateRange->assertOk();
        $responseDateRange->assertSeeText('Zdarzenia: 2');
        $responseDateRange->assertSeeText('Jan Kowalski');
        $responseDateRange->assertSeeText('Anna Nowak');
    }

    public function test_pagination_keeps_query_string_with_with_query_string(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'attendance.changelog.view', 'Widok na historię odbić');

        $employee = Employee::query()->create([
            'rfid_uid' => 'PAG00001',
            'full_name' => 'Pagination Person',
            'department' => 'Test',
        ]);

        for ($i = 0; $i < 60; $i++) {
            AttendanceEvent::query()->create([
                'employee_id' => $employee->id,
                'attendance_device_id' => null,
                'recorded_by_user_id' => null,
                'event_type' => AttendanceEventType::Entry,
                'occurred_at' => Carbon::parse('2026-02-10 08:00:00')->addSeconds($i),
                'metadata' => ['rfidUid' => 'PAG00001'],
            ]);
        }

        $response = $this->actingAs($user)->get(route('attendance.changelog', [
            'employee' => 'Pagination',
        ]));

        $response->assertOk();

        $response->assertSee('employee=Pagination', false);
        $response->assertSee('page=2', false);
    }

    private function createStandardUser(): User
    {
        $standardRole = Role::query()->firstOrCreate(['name' => 'Standard']);

        return User::factory()->create([
            'role_id' => $standardRole->id,
        ]);
    }

    private function createAdministratorUser(): User
    {
        $administratorRole = Role::query()->firstOrCreate(['name' => 'Administrator']);

        return User::factory()->create([
            'role_id' => $administratorRole->id,
        ]);
    }

    private function grantPermission(User $user, string $key, string $label): void
    {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $key],
            ['label' => $label],
        );

        $user->permissions()->syncWithoutDetaching([$permission->id]);
    }
}
