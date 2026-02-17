<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_rendered_and_contains_form_fields_and_csrf(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Logowanie');
        $response->assertSee('Adres e-mail');
        $response->assertSee('Hasło');
        $response->assertSee('Zapamiętaj mnie');
        $response->assertSee('Zaloguj się');

        $response->assertSee('method="POST"', false);
        $response->assertSee('action="' . route('login') . '"', false);
        $response->assertSee('name="_token"', false);

        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="remember"', false);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'not-an-email',
            'password' => 'AnyPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
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

    public function test_user_cannot_login_with_invalid_password_and_email_is_flashed_but_password_is_not(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('ValidPassword123!'),
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'InvalidPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors();
        $response->assertSessionHasInput('email', $user->email);
        $response->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_login_without_remember_me_does_not_set_recaller_cookie_and_does_not_persist_remember_token(): void
    {
        $plainPassword = 'ValidPassword123!';
        $user = User::factory()->create([
            'password' => Hash::make($plainPassword),
            'remember_token' => null,
        ]);

        $recallerCookieName = Auth::guard()->getRecallerName();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $plainPassword,
        ]);

        $response->assertRedirectToRoute('dashboard');
        $response->assertCookieMissing($recallerCookieName);

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_login_with_remember_me_sets_recaller_cookie_persists_token_and_allows_reauthentication_after_session_flush(): void
    {
        $plainPassword = 'ValidPassword123!';
        $user = User::factory()->create([
            'password' => Hash::make($plainPassword),
            'remember_token' => null,
        ]);

        $recallerCookieName = Auth::guard()->getRecallerName();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $plainPassword,
            'remember' => true,
        ]);

        $response->assertRedirectToRoute('dashboard');
        $response->assertCookie($recallerCookieName);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);

        $cookies = $response->headers->getCookies();
        $recallerCookieValue = null;

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $recallerCookieName) {
                $recallerCookieValue = $cookie->getValue();
                break;
            }
        }

        $this->assertNotNull($recallerCookieValue);

        $this->flushSession();

        $followUp = $this
            ->withCookie($recallerCookieName, $recallerCookieValue)
            ->get(route('dashboard'));

        $followUp->assertOk();
        $this->assertAuthenticated();
    }
    
    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirectToRoute('dashboard');
    }
}
