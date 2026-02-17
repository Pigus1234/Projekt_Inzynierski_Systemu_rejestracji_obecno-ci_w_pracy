<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_user_without_permissions_does_not_see_restricted_navigation_links(): void
    {
        $user = $this->createStandardUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();

        $response->assertSee('href="' . route('dashboard') . '"', false);

        $response->assertDontSee('href="' . route('users.index') . '"', false);
        $response->assertDontSee('href="' . route('employees.index') . '"', false);
        $response->assertDontSee('href="' . route('departments.index') . '"', false);
        $response->assertDontSee('href="' . route('attendance.present') . '"', false);
        $response->assertDontSee('href="' . route('attendance.present.print') . '"', false);
        $response->assertDontSee('href="' . route('attendance.changelog') . '"', false);
        $response->assertDontSee('href="' . route('administrator.attendance-devices.index') . '"', false);
    }

    public function test_standard_user_sees_only_links_granted_by_permissions_and_not_admin_only_link(): void
    {
        $user = $this->createStandardUser();

        $this->grantPermission($user, 'users.manage', 'Zarządzanie użytkownikami');
        $this->grantPermission($user, 'attendance.present.view', 'Widok na wyświetlanie listy pracowników');
        $this->grantPermission($user, 'attendance.present.print', 'Widok na drukowanie listy pracowników');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();

        $response->assertSee('href="' . route('dashboard') . '"', false);
        $response->assertSee('href="' . route('users.index') . '"', false);
        $response->assertSee('href="' . route('attendance.present') . '"', false);
        $response->assertSee('href="' . route('attendance.present.print') . '"', false);

        $response->assertDontSee('href="' . route('employees.index') . '"', false);
        $response->assertDontSee('href="' . route('departments.index') . '"', false);
        $response->assertDontSee('href="' . route('attendance.changelog') . '"', false);

        $response->assertDontSee('href="' . route('administrator.attendance-devices.index') . '"', false);
    }

    public function test_administrator_sees_all_navigation_links_without_explicit_permissions(): void
    {
        $administrator = $this->createAdministratorUser();

        $response = $this->actingAs($administrator)->get(route('dashboard'));

        $response->assertOk();

        $response->assertSee('href="' . route('dashboard') . '"', false);
        $response->assertSee('href="' . route('users.index') . '"', false);
        $response->assertSee('href="' . route('employees.index') . '"', false);
        $response->assertSee('href="' . route('departments.index') . '"', false);
        $response->assertSee('href="' . route('attendance.present') . '"', false);
        $response->assertSee('href="' . route('attendance.present.print') . '"', false);
        $response->assertSee('href="' . route('attendance.changelog') . '"', false);
        $response->assertSee('href="' . route('administrator.attendance-devices.index') . '"', false);
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
