<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ZkLoginOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_shows_zklogin_only(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Sign up with Google zkLogin')
            ->assertSee('Powered by Sui zkLogin')
            ->assertDontSee('Or register with email/password')
            ->assertDontSee('Confirm Password');
    }

    public function test_zklogin_creates_user_with_wallet_and_hashed_pin_verifier(): void
    {
        $verifier = hash('sha256', 'google-sub|zk@example.com|123456');

        $this->postJson('/auth/zklogin', [
            'mode' => 'register',
            'wallet_address' => '0xzkloginwallet',
            'email' => 'zk@example.com',
            'name' => 'ZK User',
            'zk_subject' => 'google-sub',
            'zk_pin_verifier' => $verifier,
        ])->assertOk()
            ->assertJson([
                'message' => 'Authenticated successfully',
                'redirect' => route('wallet.welcome'),
            ]);

        $user = User::where('email', 'zk@example.com')->firstOrFail();

        $this->assertSame('ZK User', $user->name);
        $this->assertSame('user', $user->role);
        $this->assertSame('0xzkloginwallet', $user->wallet_address);
        $this->assertSame('google-sub', $user->zk_subject);
        $this->assertNotSame($verifier, $user->zk_pin_hash);
        $this->assertTrue(Hash::check($verifier, $user->zk_pin_hash));
        $this->assertNull($user->wallet_onboarded_at);
        $this->assertAuthenticatedAs($user);
        $this->assertFalse(Schema::hasColumn('users', 'password'));
    }

    public function test_returning_onboarded_zklogin_user_redirects_to_dashboard(): void
    {
        $verifier = hash('sha256', 'google-sub|returning@example.com|123456');

        $user = User::factory()->create([
            'email' => 'returning@example.com',
            'wallet_address' => '0xreturningwallet',
            'wallet_onboarded_at' => now(),
            'zk_pin_hash' => Hash::make($verifier),
        ]);

        $this->postJson('/auth/zklogin', [
            'mode' => 'login',
            'wallet_address' => '0xreturningwallet',
            'email' => 'returning@example.com',
            'zk_subject' => 'google-sub',
            'zk_pin_verifier' => $verifier,
        ])->assertOk()
            ->assertJson([
                'redirect' => route('dashboard'),
            ]);

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_returning_zklogin_user_cannot_login_with_wrong_pin(): void
    {
        User::factory()->create([
            'email' => 'returning@example.com',
            'wallet_address' => '0xreturningwallet',
            'wallet_onboarded_at' => now(),
            'zk_pin_hash' => Hash::make(hash('sha256', 'google-sub|returning@example.com|123456')),
        ]);

        $this->postJson('/auth/zklogin', [
            'mode' => 'login',
            'wallet_address' => '0xwrongwalletfromwrongpin',
            'email' => 'returning@example.com',
            'zk_pin_verifier' => hash('sha256', 'google-sub|returning@example.com|999999'),
        ])->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid Nuance PIN. Please use the PIN you created during zkLogin registration.',
            ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'returning@example.com',
            'wallet_address' => '0xwrongwalletfromwrongpin',
        ]);
    }

    public function test_login_mode_redirects_new_google_user_to_register_to_create_pin(): void
    {
        $this->postJson('/auth/zklogin', [
            'mode' => 'login',
            'wallet_address' => '0xnewwallet',
            'email' => 'new@example.com',
            'name' => 'New User',
            'zk_subject' => 'google-sub',
            'zk_pin_verifier' => hash('sha256', 'google-sub|new@example.com|123456'),
        ])->assertStatus(409)
            ->assertJson([
                'redirect' => route('register'),
            ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'new@example.com',
        ]);
    }

    public function test_zklogin_status_redirects_new_google_user_to_register_before_pin_prompt(): void
    {
        $this->postJson('/auth/zklogin/status', [
            'email' => 'new@example.com',
        ])->assertNotFound()
            ->assertJson([
                'has_pin' => false,
                'redirect' => route('register'),
            ]);
    }

    public function test_zklogin_status_allows_existing_google_user_with_pin_to_login(): void
    {
        User::factory()->create([
            'email' => 'ready@example.com',
            'zk_pin_hash' => Hash::make(hash('sha256', 'google-sub|ready@example.com|123456')),
        ]);

        $this->postJson('/auth/zklogin/status', [
            'email' => 'ready@example.com',
        ])->assertOk()
            ->assertJson([
                'has_pin' => true,
            ]);
    }

    public function test_login_mode_redirects_user_without_pin_to_register_to_create_pin(): void
    {
        User::factory()->create([
            'email' => 'nopin@example.com',
            'wallet_address' => null,
            'zk_pin_hash' => null,
        ]);

        $this->postJson('/auth/zklogin', [
            'mode' => 'login',
            'wallet_address' => '0xnopinwallet',
            'email' => 'nopin@example.com',
            'name' => 'No Pin User',
            'zk_subject' => 'google-sub',
            'zk_pin_verifier' => hash('sha256', 'google-sub|nopin@example.com|123456'),
        ])->assertStatus(409)
            ->assertJson([
                'redirect' => route('register'),
            ]);

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'nopin@example.com',
            'zk_pin_hash' => null,
        ]);
    }

    public function test_wallet_welcome_requires_auth_and_wallet(): void
    {
        $this->get(route('wallet.welcome'))->assertRedirect(route('login'));

        $userWithoutWallet = User::factory()->create([
            'wallet_address' => null,
            'sui_address' => null,
        ]);

        $this->actingAs($userWithoutWallet)
            ->get(route('wallet.welcome'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_wallet_welcome_can_be_completed(): void
    {
        $user = User::factory()->create([
            'wallet_address' => '0xwelcome',
            'wallet_onboarded_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('wallet.welcome'))
            ->assertOk()
            ->assertSee('0xwelcome')
            ->assertSee('Your Sui Testnet vault is ready');

        $this->actingAs($user)
            ->post(route('wallet.welcome.complete'))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($user->fresh()->wallet_onboarded_at);
    }
}
