<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePagesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get(route('attendance.present'))->assertRedirectToRoute('login');
        $this->get(route('attendance.changelog'))->assertRedirectToRoute('login');
    }

    public function testStandardUserIsForbidden(): void
    {
        $standardRole = Role::query()->create(['name' => 'Standard']);
        $standardUser = User::factory()->create(['role_id' => $standardRole->id]);

        $this->actingAs($standardUser)->get(route('attendance.present'))->assertForbidden();
        $this->actingAs($standardUser)->get(route('attendance.changelog'))->assertForbidden();
    }

    public function testAdministratorUserCanOpenPages(): void
    {
        $administratorRole = Role::query()->create(['name' => 'Administrator']);
        $administratorUser = User::factory()->create(['role_id' => $administratorRole->id]);

        $this->actingAs($administratorUser)->get(route('attendance.present'))->assertOk();
        $this->actingAs($administratorUser)->get(route('attendance.changelog'))->assertOk();
    }
}
