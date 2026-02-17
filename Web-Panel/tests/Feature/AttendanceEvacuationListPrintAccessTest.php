<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceEvacuationListPrintAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_print_page(): void
    {
        $response = $this->get(route('attendance.present.print'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_without_attendance_present_print_permission_is_forbidden(): void
    {
        $user = $this->createStandardUser();

        $response = $this->actingAs($user)->get(route('attendance.present.print'));

        $response->assertForbidden();
    }

    public function test_administrator_can_open_print_page_without_explicit_permission_and_sees_present_employees(): void
    {
        $administrator = $this->createAdministratorUser();

        $now = now();

        $employee = Employee::query()->create([
            'rfid_uid' => 'UID-ADMIN-1',
            'full_name' => 'Admin Present Person',
            'department' => 'Magazyn',
        ]);

        $this->insertAttendanceEvent($employee->id, 'entry', $now->copy()->subMinutes(5));

        $response = $this->actingAs($administrator)->get(route('attendance.present.print'));

        $response->assertOk();
        $response->assertSeeText('Admin Present Person');
        $response->assertSeeText('Magazyn');
    }

    public function test_user_with_permission_sees_print_page_and_only_present_employees(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'attendance.present.print', 'Widok na drukowanie listy pracowników');

        $now = now();

        $magazynPresent = Employee::query()->create([
            'rfid_uid' => 'UID-M1',
            'full_name' => 'Adam Magazyn',
            'department' => 'Magazyn',
        ]);

        $produkcjaPresent = Employee::query()->create([
            'rfid_uid' => 'UID-P1',
            'full_name' => 'Beata Produkcja',
            'department' => 'Produkcja',
        ]);

        $notPresent = Employee::query()->create([
            'rfid_uid' => 'UID-X1',
            'full_name' => 'Cezary Wyszedł',
            'department' => 'Magazyn',
        ]);

        $this->insertAttendanceEvent($magazynPresent->id, 'entry', $now->copy()->subMinutes(10));
        $this->insertAttendanceEvent($produkcjaPresent->id, 'entry', $now->copy()->subMinutes(20));

        $this->insertAttendanceEvent($notPresent->id, 'entry', $now->copy()->subMinutes(25));
        $this->insertAttendanceEvent($notPresent->id, 'exit', $now->copy()->subMinutes(5));

        $response = $this->actingAs($user)->get(route('attendance.present.print'));

        $response->assertOk();

        $response->assertSeeText('Magazyn');
        $response->assertSeeText('Produkcja');

        $response->assertSeeText('Adam Magazyn');
        $response->assertSeeText('Beata Produkcja');
        $response->assertDontSeeText('Cezary Wyszedł');
    }

    public function test_deleted_employee_is_not_listed_even_if_present(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'attendance.present.print', 'Widok na drukowanie listy pracowników');

        $employee = Employee::query()->create([
            'rfid_uid' => 'UID-DEL',
            'full_name' => 'Usunięty Pracownik',
            'department' => 'Magazyn',
        ]);

        $employee->delete();

        $this->insertAttendanceEvent($employee->id, 'entry', now()->subMinutes(3));

        $response = $this->actingAs($user)->get(route('attendance.present.print'));

        $response->assertOk();
        $response->assertDontSeeText('Usunięty Pracownik');
    }

    private function createStandardUser(): User
    {
        $role = Role::query()->firstOrCreate(['name' => 'Standard']);

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    private function createAdministratorUser(): User
    {
        $role = Role::query()->firstOrCreate(['name' => 'Administrator']);

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    private function grantPermission(User $user, string $key, string $label): void
    {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $key],
            ['label' => $label]
        );

        $user->permissions()->syncWithoutDetaching([$permission->id]);
    }

    private function insertAttendanceEvent(int $employeeId, string $eventType, \DateTimeInterface $occurredAt): void
    {
        DB::table('attendance_events')->insert([
            'employee_id' => $employeeId,
            'attendance_device_id' => null,
            'recorded_by_user_id' => null,
            'event_type' => $eventType,
            'occurred_at' => $occurredAt,
            'metadata' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
