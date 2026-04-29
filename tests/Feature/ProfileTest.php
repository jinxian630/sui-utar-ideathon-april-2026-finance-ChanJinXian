<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('original@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_profile_page_displays_email_as_locked_identity(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('locked@example.com')
            ->assertSee('Email cannot be changed because it is linked to your Google zkLogin identity.')
            ->assertDontSee('name="email"', false);
    }

    public function test_zklogin_user_can_update_pin_with_current_pin(): void
    {
        $oldVerifier = hash('sha256', 'google-sub|zk@example.com|123456');
        $newVerifier = hash('sha256', 'google-sub|zk@example.com|654321');

        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make($oldVerifier),
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile/pin', [
                'current_pin' => '123456',
                'new_pin' => '654321',
                'new_pin_confirmation' => '654321',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check($newVerifier, $user->fresh()->zk_pin_hash));
        $this->assertFalse(Hash::check($oldVerifier, $user->fresh()->zk_pin_hash));
    }

    public function test_zklogin_user_cannot_update_pin_with_wrong_current_pin(): void
    {
        $verifier = hash('sha256', 'google-sub|zk@example.com|123456');

        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make($verifier),
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile/pin', [
                'current_pin' => '999999',
                'new_pin' => '654321',
                'new_pin_confirmation' => '654321',
            ])
            ->assertSessionHasErrorsIn('updatePin', 'current_pin')
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check($verifier, $user->fresh()->zk_pin_hash));
    }

    public function test_zklogin_user_cannot_update_pin_when_confirmation_mismatches(): void
    {
        $verifier = hash('sha256', 'google-sub|zk@example.com|123456');

        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make($verifier),
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile/pin', [
                'current_pin' => '123456',
                'new_pin' => '654321',
                'new_pin_confirmation' => '111111',
            ])
            ->assertSessionHasErrorsIn('updatePin', 'new_pin')
            ->assertRedirect('/profile');
    }

    public function test_verify_pin_returns_success_for_correct_pin(): void
    {
        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make(hash('sha256', 'google-sub|zk@example.com|123456')),
        ]);

        $this->actingAs($user)
            ->postJson('/profile/verify-pin', [
                'zk_pin' => '123456',
            ])
            ->assertOk()
            ->assertJson([
                'verified' => true,
            ]);
    }

    public function test_verify_pin_rejects_wrong_pin(): void
    {
        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make(hash('sha256', 'google-sub|zk@example.com|123456')),
        ]);

        $this->actingAs($user)
            ->postJson('/profile/verify-pin', [
                'zk_pin' => '999999',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('zk_pin');
    }

    public function test_zklogin_user_can_delete_their_account_with_correct_pin(): void
    {
        $pinVerifier = hash('sha256', 'google-sub|zk@example.com|123456');

        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'wallet_address' => '0x' . str_repeat('a', 64),
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make($pinVerifier),
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'zk_pin' => '123456',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_deleted_zklogin_user_can_register_again_with_new_pin(): void
    {
        $oldVerifier = hash('sha256', 'google-sub|zk@example.com|123456');
        $newVerifier = hash('sha256', 'google-sub|zk@example.com|654321');

        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'wallet_address' => '0x' . str_repeat('a', 64),
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make($oldVerifier),
        ]);

        $this->actingAs($user)
            ->delete('/profile', [
                'zk_pin' => '123456',
            ])
            ->assertRedirect('/');

        $this->assertNull($user->fresh());

        $this->postJson('/auth/zklogin', [
            'wallet_address' => '0x' . str_repeat('b', 64),
            'email' => 'zk@example.com',
            'name' => 'ZK User Again',
            'zk_subject' => 'google-sub',
            'zk_pin_verifier' => $newVerifier,
        ])->assertOk()
            ->assertJson([
                'redirect' => route('wallet.welcome'),
            ]);

        $newUser = User::where('email', 'zk@example.com')->firstOrFail();

        $this->assertSame('ZK User Again', $newUser->name);
        $this->assertSame('0x' . str_repeat('b', 64), $newUser->wallet_address);
        $this->assertTrue(Hash::check($newVerifier, $newUser->zk_pin_hash));
        $this->assertFalse(Hash::check($oldVerifier, $newUser->zk_pin_hash));
        $this->assertAuthenticatedAs($newUser);
    }

    public function test_zklogin_user_cannot_delete_account_with_wrong_pin(): void
    {
        $pinVerifier = hash('sha256', 'google-sub|zk@example.com|123456');

        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'wallet_address' => '0x' . str_repeat('a', 64),
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make($pinVerifier),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'zk_pin' => '999999',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'zk_pin')
            ->assertRedirect('/profile');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
    }

    public function test_zklogin_user_must_enter_pin_to_delete_account(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'wallet_address' => '0x' . str_repeat('a', 64),
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make(hash('sha256', 'google-sub|' . 'user@example.com' . '|123456')),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile');

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'zk_pin')
            ->assertRedirect('/profile');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
    }

    public function test_password_cannot_be_used_to_delete_account(): void
    {
        $user = User::factory()->create([
            'email' => 'zk@example.com',
            'wallet_address' => '0x' . str_repeat('a', 64),
            'zk_subject' => 'google-sub',
            'zk_pin_hash' => Hash::make(hash('sha256', 'google-sub|zk@example.com|123456')),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'zk_pin')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
