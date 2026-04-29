<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_zklogin_only(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in with Google zkLogin')
            ->assertDontSee('Email Address')
            ->assertDontSee('Forgot password?');
    }

    public function test_register_page_is_zklogin_only(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Sign up with Google zkLogin')
            ->assertDontSee('Or register with email/password')
            ->assertDontSee('Confirm Password');
    }

    public function test_traditional_auth_routes_are_unavailable(): void
    {
        $this->post('/login')->assertStatus(405);
        $this->post('/register')->assertStatus(405);
        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password')->assertNotFound();
        $this->get('/reset-password/token')->assertNotFound();
        $this->post('/reset-password')->assertNotFound();
        $this->get('/confirm-password')->assertNotFound();
        $this->post('/confirm-password')->assertNotFound();
        $this->put('/password')->assertNotFound();
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/dashboard')
            ->assertStatus(302)
            ->assertRedirect('/login');
    }
}
