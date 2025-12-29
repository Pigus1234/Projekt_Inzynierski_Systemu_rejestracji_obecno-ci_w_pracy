<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_rendered_and_contains_remember_me_checkbox(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Logowanie');
        $response->assertSee('Zapamiętaj mnie');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $plainPassword = 'ValidPassword123!';
        $user = User::factory()->create([
            'password' => Hash::make($plainPassword),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $plainPassword,
        ]);

        $response->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('ValidPassword123!'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'InvalidPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
