<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Sign in with Google zkLogin')
            ->assertDontSee('Email Address');
    }

    public function test_password_login_route_is_unavailable(): void
    {
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertStatus(405);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
