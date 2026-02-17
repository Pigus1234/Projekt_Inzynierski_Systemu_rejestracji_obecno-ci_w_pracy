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

class AttendancePresentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_present_page(): void
    {
        $response = $this->get(route('attendance.present'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_without_attendance_present_view_permission_is_forbidden(): void
    {
        $user = $this->createStandardUser();

        $response = $this->actingAs($user)->get(route('attendance.present'));

        $response->assertForbidden();
    }

    public function test_administrator_can_open_present_page_without_explicit_permission(): void
    {
        $administrator = $this->createAdministratorUser();

        $response = $this->actingAs($administrator)->get(route('attendance.present'));

        $response->assertOk();
        $response->assertSeeText('Obecni na terenie');
        $response->assertSeeText('Pracownik');
        $response->assertSeeText('Dział');
        $response->assertSeeText('RFID UID');
        $response->assertSeeText('Wejście');
    }

    public function test_user_with_attendance_present_view_permission_can_open_present_page_and_sees_only_present_employees_sorted(): void
    {
        $user = $this->createStandardUserWithPermissions([
            'attendance.present.view',
        ]);

        $employeeWarehouse = Employee::query()->create([
            'rfid_uid' => 'UID-AAAA',
            'full_name' => 'Adam Kowalski',
            'department' => 'Magazyn',
        ]);

        $employeeProduction = Employee::query()->create([
            'rfid_uid' => 'UID-BBBB',
            'full_name' => 'Beata Nowak',
            'department' => 'Produkcja',
        ]);

        $employeeLeft = Employee::query()->create([
            'rfid_uid' => 'UID-CCCC',
            'full_name' => 'Cezary Wyszedł',
            'department' => 'Produkcja',
        ]);

        $now = Carbon::now();

        $this->createAttendanceEvent($employeeWarehouse, AttendanceEventType::Entry, $now->copy()->subMinutes(10));
        $this->createAttendanceEvent($employeeProduction, AttendanceEventType::Entry, $now->copy()->subMinutes(20));

        $this->createAttendanceEvent($employeeLeft, AttendanceEventType::Entry, $now->copy()->subMinutes(30));
        $this->createAttendanceEvent($employeeLeft, AttendanceEventType::Exit, $now->copy()->subMinutes(5));

        $response = $this->actingAs($user)->get(route('attendance.present'));

        $response->assertOk();
        $response->assertSeeText('Łącznie: 2');

        $response->assertSeeText('Adam Kowalski');
        $response->assertSeeText('Beata Nowak');
        $response->assertDontSeeText('Cezary Wyszedł');

        $response->assertSeeText('UID-AAAA');
        $response->assertSeeText('UID-BBBB');
        $response->assertDontSeeText('UID-CCCC');

        $response->assertSeeInOrder([
            'Adam Kowalski',
            'Magazyn',
            'Beata Nowak',
            'Produkcja',
        ]);
    }

    public function test_deleted_employee_is_not_listed_even_if_last_event_is_entry(): void
    {
        $user = $this->createStandardUserWithPermissions([
            'attendance.present.view',
        ]);

        $employee = Employee::query()->create([
            'rfid_uid' => 'UID-DEAD',
            'full_name' => 'Usunięty Pracownik',
            'department' => 'Magazyn',
        ]);

        $this->createAttendanceEvent($employee, AttendanceEventType::Entry, Carbon::now()->subMinutes(5));

        $employee->delete();

        $response = $this->actingAs($user)->get(route('attendance.present'));

        $response->assertOk();
        $response->assertSeeText('Łącznie: 0');
        $response->assertDontSeeText('Usunięty Pracownik');
        $response->assertDontSeeText('UID-DEAD');
        $response->assertSeeText('Brak osób obecnych.');
    }

    public function test_page_shows_empty_state_when_no_present_employees(): void
    {
        $user = $this->createStandardUserWithPermissions([
            'attendance.present.view',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.present'));

        $response->assertOk();
        $response->assertSeeText('Łącznie: 0');
        $response->assertSeeText('Brak osób obecnych.');
    }

    private function createAdministratorUser(): User
    {
        $administratorRole = Role::query()->updateOrCreate(['name' => 'Administrator']);

        return User::factory()->create([
            'role_id' => $administratorRole->id,
        ]);
    }

    private function createStandardUser(): User
    {
        $standardRole = Role::query()->updateOrCreate(['name' => 'Standard']);

        return User::factory()->create([
            'role_id' => $standardRole->id,
        ]);
    }

    private function createStandardUserWithPermissions(array $permissionKeys): User
    {
        $user = $this->createStandardUser();

        $permissionIds = [];
        foreach ($permissionKeys as $permissionKey) {
            $permissionIds[] = Permission::query()->updateOrCreate(
                ['key' => $permissionKey],
                ['label' => $permissionKey]
            )->id;
        }

        $user->permissions()->sync($permissionIds);

        return $user;
    }

    private function createAttendanceEvent(Employee $employee, AttendanceEventType $type, Carbon $occurredAt): AttendanceEvent
    {
        return AttendanceEvent::query()->create([
            'employee_id' => $employee->id,
            'attendance_device_id' => null,
            'recorded_by_user_id' => null,
            'event_type' => $type,
            'occurred_at' => $occurredAt,
            'metadata' => [
                'rfidUid' => $employee->rfid_uid,
            ],
        ]);
    }
}
