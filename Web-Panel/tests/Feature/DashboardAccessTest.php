<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_authenticated_user_can_open_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
    }
}
