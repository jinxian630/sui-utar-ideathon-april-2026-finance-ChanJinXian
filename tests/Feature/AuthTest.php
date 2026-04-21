<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        
        // As per the Prompt 1 expectations occasionally it was looking for a 201 via API. 
        // We're handling standard Laravel redirects here for the full-stack app.
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correctpassword')
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        // PostJson handles ValidationException as 422 or we receive the manual 302 back.
        // Wait, manual `back()->withErrors` in our controller returns 302 redirect.
        // To strictly get a 422 validation error we can send bad email, or we'll just check session errors.
        
        $response2 = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
        
        $response2->assertSessionHasErrors('email');
        $response2->assertStatus(302);
        $this->assertGuest();
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
