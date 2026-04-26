<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ZkLoginOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_shows_zklogin_choice_and_traditional_form_copy(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Sign up with Google zkLogin')
            ->assertSee('Powered by Sui zkLogin')
            ->assertSee('Or register with email/password');
    }

    public function test_traditional_registration_still_redirects_to_dashboard(): void
    {
        $this->post('/register', [
            'name' => 'Traditional User',
            'email' => 'traditional@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'traditional@example.com',
            'role' => 'user',
            'wallet_address' => null,
        ]);
    }

    public function test_zklogin_creates_user_with_wallet_and_hashed_pin_verifier(): void
    {
        $verifier = hash('sha256', 'google-sub|zk@example.com|123456');

        $this->postJson('/auth/zklogin', [
            'wallet_address' => '0xzkloginwallet',
            'email' => 'zk@example.com',
            'name' => 'ZK User',
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
        $this->assertNotSame($verifier, $user->zk_pin_hash);
        $this->assertTrue(Hash::check($verifier, $user->zk_pin_hash));
        $this->assertNull($user->wallet_onboarded_at);
        $this->assertAuthenticatedAs($user);
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
            'wallet_address' => '0xreturningwallet',
            'email' => 'returning@example.com',
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
