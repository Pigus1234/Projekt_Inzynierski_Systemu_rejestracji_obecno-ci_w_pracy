<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_employees_index(): void
    {
        $response = $this->get(route('employees.index'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_without_employees_manage_view_is_forbidden_for_index(): void
    {
        $user = $this->createStandardUserWithPermissions([]);

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertForbidden();
    }

    public function test_user_with_employees_manage_view_can_open_index(): void
    {
        $user = $this->createStandardUserWithPermissions(['employees.manage.view']);

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertOk();
        $response->assertViewIs('employees.index');
        $response->assertViewHas('employees');
        $response->assertSeeText('Lista pracowników');
    }

    public function test_user_without_employees_manage_create_is_forbidden_for_create_page(): void
    {
        $user = $this->createStandardUserWithPermissions(['employees.manage.view']);

        $response = $this->actingAs($user)->get(route('employees.create'));

        $response->assertForbidden();
    }

    public function test_user_with_employees_manage_create_can_open_create_page_and_form_contains_csrf_token(): void
    {
        Department::query()->create(['name' => 'IT']);

        $user = $this->createStandardUserWithPermissions(['employees.manage.view', 'employees.manage.create']);

        $response = $this->actingAs($user)->get(route('employees.create'));

        $response->assertOk();
        $response->assertViewIs('employees.create');
        $response->assertSee('name="_token"', false);
        $response->assertSeeText('Dodaj pracownika');
        $response->assertSeeText('IT');
    }

    public function test_store_requires_valid_department_name_when_provided(): void
    {
        Department::query()->create(['name' => 'IT']);

        $user = $this->createStandardUserWithPermissions(['employees.manage.view', 'employees.manage.create']);

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'rfid_uid' => $this->randomRfidUid(),
            'full_name' => 'Jan Kowalski',
            'department' => 'NIEISTNIEJE',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['department']);
    }

    public function test_user_with_employees_manage_create_can_store_employee_without_department(): void
    {
        $user = $this->createStandardUserWithPermissions(['employees.manage.view', 'employees.manage.create']);

        $rfidUid = $this->randomRfidUid();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'rfid_uid' => $rfidUid,
            'full_name' => 'Anna Nowak',
            'department' => '',
        ]);

        $response->assertRedirectToRoute('employees.index');
        $response->assertSessionHas('success');

        $this->assertNotNull(Employee::query()->where('rfid_uid', $rfidUid)->first());
    }

    public function test_user_without_employees_manage_update_is_forbidden_for_edit_page(): void
    {
        $employee = Employee::query()->create([
            'rfid_uid' => $this->randomRfidUid(),
            'full_name' => 'Test Employee',
            'department' => null,
        ]);

        $user = $this->createStandardUserWithPermissions(['employees.manage.view']);

        $response = $this->actingAs($user)->get(route('employees.edit', $employee));

        $response->assertForbidden();
    }

    public function test_user_with_employees_manage_update_can_open_edit_page_and_form_contains_csrf_and_method_put(): void
    {
        Department::query()->create(['name' => 'IT']);

        $employee = Employee::query()->create([
            'rfid_uid' => $this->randomRfidUid(),
            'full_name' => 'Test Employee',
            'department' => 'IT',
        ]);

        $user = $this->createStandardUserWithPermissions(['employees.manage.view', 'employees.manage.update']);

        $response = $this->actingAs($user)->get(route('employees.edit', $employee));

        $response->assertOk();
        $response->assertViewIs('employees.edit');
        $response->assertSee('name="_token"', false);
        $response->assertSee('name="_method"', false);
        $response->assertSee('value="PUT"', false);
        $response->assertSeeText('Edytuj pracownika');
    }

    public function test_update_rejects_duplicate_rfid_uid(): void
    {
        $employeeOne = Employee::query()->create([
            'rfid_uid' => 'DUPLICATE01',
            'full_name' => 'First Employee',
            'department' => null,
        ]);

        $employeeTwo = Employee::query()->create([
            'rfid_uid' => 'DUPLICATE02',
            'full_name' => 'Second Employee',
            'department' => null,
        ]);

        $user = $this->createStandardUserWithPermissions(['employees.manage.view', 'employees.manage.update']);

        $response = $this->actingAs($user)->put(route('employees.update', $employeeTwo), [
            'rfid_uid' => $employeeOne->rfid_uid,
            'full_name' => 'Second Employee Updated',
            'department' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['rfid_uid']);
    }

    public function test_user_without_employees_manage_archive_is_forbidden_for_archive(): void
    {
        $employee = Employee::query()->create([
            'rfid_uid' => $this->randomRfidUid(),
            'full_name' => 'Archive Employee',
            'department' => null,
        ]);

        $user = $this->createStandardUserWithPermissions(['employees.manage.view']);

        $response = $this->actingAs($user)->delete(route('employees.archive', $employee));

        $response->assertForbidden();
    }

    public function test_user_with_employees_manage_archive_can_soft_delete_employee_and_employee_disappears_from_index(): void
    {
        $employee = Employee::query()->create([
            'rfid_uid' => $this->randomRfidUid(),
            'full_name' => 'Archive Employee',
            'department' => null,
        ]);

        $user = $this->createStandardUserWithPermissions(['employees.manage.view', 'employees.manage.archive']);

        $response = $this->actingAs($user)->delete(route('employees.archive', $employee));

        $response->assertRedirectToRoute('employees.index');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);

        $indexResponse = $this->actingAs($user)->get(route('employees.index'));
        $indexResponse->assertOk();
        $indexResponse->assertDontSeeText('Archive Employee');
    }

    public function test_user_without_employees_manage_restore_is_forbidden_for_archived_and_restore(): void
    {
        $employee = Employee::query()->create([
            'rfid_uid' => $this->randomRfidUid(),
            'full_name' => 'Trashed Employee',
            'department' => null,
        ]);
        $employee->delete();

        $user = $this->createStandardUserWithPermissions(['employees.manage.view']);

        $archivedResponse = $this->actingAs($user)->get(route('employees.archived'));
        $archivedResponse->assertForbidden();

        $restoreResponse = $this->actingAs($user)->post(route('employees.restore', ['employeeId' => $employee->id]));
        $restoreResponse->assertForbidden();
    }

    public function test_user_with_employees_manage_restore_can_view_archived_and_restore_employee(): void
    {
        $employee = Employee::query()->create([
            'rfid_uid' => $this->randomRfidUid(),
            'full_name' => 'Trashed Employee',
            'department' => null,
        ]);
        $employee->delete();

        $user = $this->createStandardUserWithPermissions(['employees.manage.view', 'employees.manage.restore']);

        $archivedResponse = $this->actingAs($user)->get(route('employees.archived'));

        $archivedResponse->assertOk();
        $archivedResponse->assertViewIs('employees.archived');
        $archivedResponse->assertSeeText('Archiwum');

        $restoreResponse = $this->actingAs($user)->post(route('employees.restore', ['employeeId' => $employee->id]));

        $restoreResponse->assertRedirectToRoute('employees.archived');
        $restoreResponse->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'deleted_at' => null,
        ]);
    }

    private function createStandardUserWithPermissions(array $permissionKeys): User
    {
        $standardRole = Role::query()->firstOrCreate(['name' => 'Standard']);

        $user = User::factory()->create([
            'role_id' => $standardRole->id,
        ]);

        foreach ($permissionKeys as $permissionKey) {
            $permission = $this->firstOrCreatePermission($permissionKey, $permissionKey);
            $user->permissions()->syncWithoutDetaching([$permission->id]);
        }

        return $user;
    }

    private function firstOrCreatePermission(string $key, string $label): Permission
    {
        $existing = Permission::query()->where('key', $key)->first();
        if ($existing) {
            return $existing;
        }

        $now = now();

        DB::table('permissions')->insert([
            'key' => $key,
            'label' => $label,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Permission::query()->where('key', $key)->firstOrFail();
    }

    private function randomRfidUid(): string
    {
        return strtoupper(bin2hex(random_bytes(4)));
    }
}
