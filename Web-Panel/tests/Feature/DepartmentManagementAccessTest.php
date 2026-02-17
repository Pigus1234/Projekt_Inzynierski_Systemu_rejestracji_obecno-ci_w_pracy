<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_departments_index(): void
    {
        $response = $this->get(route('departments.index'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_without_departments_manage_permission_is_forbidden(): void
    {
        $user = $this->createStandardUser();

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertForbidden();
    }

    public function test_administrator_can_open_departments_index_without_explicit_permission(): void
    {
        $user = $this->createAdministratorUser();

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertOk();
        $response->assertViewIs('departments.index');
    }

    public function test_user_with_departments_manage_permission_can_open_departments_index(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertOk();
        $response->assertViewIs('departments.index');
        $response->assertViewHas('departments');
    }

    public function test_user_with_departments_manage_permission_can_open_create_page(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->get(route('departments.create'));

        $response->assertOk();
        $response->assertViewIs('departments.create');
    }

    public function test_store_requires_name(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('departments', 0);
    }

    public function test_user_with_departments_manage_permission_can_create_department(): void
    {
        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => 'Logistyka',
        ]);

        $response->assertRedirectToRoute('departments.index');
        $this->assertDatabaseHas('departments', [
            'name' => 'Logistyka',
        ]);
    }

    public function test_store_requires_unique_name(): void
    {
        Department::query()->create(['name' => 'Produkcja']);

        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => 'Produkcja',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('departments', 1);
    }

    public function test_user_with_departments_manage_permission_can_open_edit_page(): void
    {
        $department = Department::query()->create(['name' => 'Magazyn']);

        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->get(route('departments.edit', $department));

        $response->assertOk();
        $response->assertViewIs('departments.edit');
        $response->assertViewHas('department');
    }

    public function test_update_requires_unique_name_ignoring_current_department(): void
    {
        $departmentA = Department::query()->create(['name' => 'A']);
        $departmentB = Department::query()->create(['name' => 'B']);

        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->put(route('departments.update', $departmentA), [
            'name' => 'B',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);

        $this->assertDatabaseHas('departments', [
            'id' => $departmentA->id,
            'name' => 'A',
        ]);
    }

    public function test_user_with_departments_manage_permission_can_update_department(): void
    {
        $department = Department::query()->create(['name' => 'Stary dział']);

        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => 'Nowy dział',
        ]);

        $response->assertRedirectToRoute('departments.index');
        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Nowy dział',
        ]);
    }

    public function test_user_with_departments_manage_permission_can_delete_department(): void
    {
        $department = Department::query()->create(['name' => 'Do usunięcia']);

        $user = $this->createStandardUser();
        $this->grantPermission($user, 'departments.manage', 'Zarządzanie działami');

        $response = $this->actingAs($user)->delete(route('departments.destroy', $department));

        $response->assertRedirectToRoute('departments.index');
        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
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
}