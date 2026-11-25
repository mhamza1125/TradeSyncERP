<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Owner decision D1: there is no self-registration; only an Admin creates users.
 * This replaces Breeze's "registration screen can be rendered" / "new users can register"
 * tests, which tested a feature that has been removed on purpose.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_registration_post_does_not_exist_and_creates_no_user(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_login_page_has_no_register_link(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('Create an Account');
    }
}
