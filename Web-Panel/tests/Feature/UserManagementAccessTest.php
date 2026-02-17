<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_users_index(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_without_users_manage_permission_is_forbidden(): void
    {
        $role = $this->createRole('User');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_authenticated_user_with_users_manage_permission_can_open_users_index(): void
    {
        $role = $this->createRole('User');
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->grantPermission($user, $this->createPermission('users.manage', 'Zarządzanie użytkownikami'));

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');
        $response->assertSeeText('Użytkownicy');
    }

    public function test_create_page_excludes_administrator_role_for_non_administrator(): void
    {
        $administratorRole = $this->createRole('Administrator');
        $userRole = $this->createRole('User');

        $usersManagePermission = $this->createPermission('users.manage', 'Zarządzanie użytkownikami');

        $user = User::factory()->create(['role_id' => $userRole->id]);
        $this->grantPermission($user, $usersManagePermission);

        $response = $this->actingAs($user)->get(route('users.create'));

        $response->assertOk();
        $response->assertViewIs('users.create');

        $response->assertViewHas('roles', function ($roles) use ($administratorRole) {
            return ! $roles->contains(fn ($role) => (int) $role->id === (int) $administratorRole->id);
        });

        $response->assertViewHas('permissions');
    }

    public function test_user_with_users_manage_permission_can_create_user_with_allowed_role_and_permissions(): void
    {
        $userRole = $this->createRole('User');
        $actingUserRole = $this->createRole('Manager');

        $usersManagePermission = $this->createPermission('users.manage', 'Zarządzanie użytkownikami');
        $attendancePresentViewPermission = $this->createPermission('attendance.present.view', 'Widok na wyświetlanie listy pracowników');

        $actingUser = User::factory()->create(['role_id' => $actingUserRole->id]);
        $this->grantPermission($actingUser, $usersManagePermission);

        $payload = [
            'name' => 'Nowy Użytkownik',
            'email' => 'new.user@example.com',
            'password' => 'ValidPassword123!',
            'role_id' => $userRole->id,
            'permission_ids' => [$attendancePresentViewPermission->id],
        ];

        $response = $this->actingAs($actingUser)->post(route('users.store'), $payload);

        $response->assertRedirectToRoute('users.index');
        $response->assertSessionHas('success');

        $createdUser = User::query()->where('email', 'new.user@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertSame('Nowy Użytkownik', $createdUser->name);
        $this->assertSame((int) $userRole->id, (int) $createdUser->role_id);
        $this->assertTrue(Hash::check('ValidPassword123!', $createdUser->password));

        $createdUser->load('permissions');
        $this->assertTrue($createdUser->permissions->contains('id', $attendancePresentViewPermission->id));
    }

    public function test_non_administrator_cannot_assign_administrator_role_even_with_users_manage_permission(): void
    {
        $administratorRole = $this->createRole('Administrator');
        $actingUserRole = $this->createRole('Manager');

        $usersManagePermission = $this->createPermission('users.manage', 'Zarządzanie użytkownikami');

        $actingUser = User::factory()->create(['role_id' => $actingUserRole->id]);
        $this->grantPermission($actingUser, $usersManagePermission);

        $payload = [
            'name' => 'Próba Admina',
            'email' => 'attempt.admin@example.com',
            'password' => 'ValidPassword123!',
            'role_id' => $administratorRole->id,
            'permission_ids' => [],
        ];

        $response = $this->actingAs($actingUser)->post(route('users.store'), $payload);

        $response->assertForbidden();
    }

    public function test_non_administrator_cannot_edit_administrator_account(): void
    {
        $administratorRole = $this->createRole('Administrator');
        $actingUserRole = $this->createRole('Manager');

        $usersManagePermission = $this->createPermission('users.manage', 'Zarządzanie użytkownikami');

        $actingUser = User::factory()->create(['role_id' => $actingUserRole->id]);
        $this->grantPermission($actingUser, $usersManagePermission);

        $administratorAccount = User::factory()->create(['role_id' => $administratorRole->id]);

        $response = $this->actingAs($actingUser)->get(route('users.edit', $administratorAccount));

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $actingUserRole = $this->createRole('Manager');
        $usersManagePermission = $this->createPermission('users.manage', 'Zarządzanie użytkownikami');

        $actingUser = User::factory()->create(['role_id' => $actingUserRole->id]);
        $this->grantPermission($actingUser, $usersManagePermission);

        $response = $this->actingAs($actingUser)->delete(route('users.destroy', $actingUser));

        $response->assertRedirectToRoute('users.index');
        $response->assertSessionHas('error');

        $this->assertNotNull(User::query()->find($actingUser->id));
    }

    private function createRole(string $name): Role
    {
        return Role::query()->create([
            'name' => $name,
        ]);
    }

    private function createPermission(string $key, string $label): Permission
    {
        $now = now();

        DB::table('permissions')->insert([
            'key' => $key,
            'label' => $label,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Permission::query()->where('key', $key)->firstOrFail();
    }

    private function grantPermission(User $user, Permission $permission): void
    {
        $user->permissions()->syncWithoutDetaching([$permission->id]);
    }
}
