<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_protected_pages(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get(route('savings.index'))->assertRedirect(route('login'));
        $this->get(route('badges'))->assertRedirect(route('login'));
        $this->get(route('admin.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_access_standard_authenticated_pages(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get(route('savings.index'))->assertOk();
        $this->actingAs($user)->get(route('badges'))->assertOk();
    }

    public function test_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Admin Panel')
            ->assertSee('Users');
    }

    public function test_wallet_linked_route_rejects_user_without_wallet(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'wallet_address' => null,
            'sui_address' => null,
        ]);

        $this->actingAs($user)
            ->postJson(route('sui.profile.store'), [
                'profile_object_id' => '0x' . str_repeat('a', 64),
            ])
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Connect your Sui wallet before using wallet-linked financial actions.',
            ]);
    }

    public function test_wallet_linked_route_allows_user_with_wallet(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'wallet_address' => '0xwallet_allowed',
        ]);
        $profileId = '0x' . str_repeat('b', 64);

        $this->actingAs($user)
            ->postJson(route('sui.profile.store'), [
                'profile_object_id' => $profileId,
            ])
            ->assertOk()
            ->assertJson([
                'profile_object_id' => $profileId,
            ]);

        $this->assertSame($profileId, $user->fresh()->sui_finance_profile_id);
    }

    public function test_wallet_linked_html_route_redirects_without_wallet(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'wallet_address' => null,
            'sui_address' => null,
        ]);

        $this->actingAs($user)
            ->post(route('savings.store'), [
                'amount' => 25,
                'type' => 'income',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('error', 'Connect your Sui wallet before using wallet-linked financial actions.');
    }

    public function test_zklogin_regenerates_session_after_login(): void
    {
        $this->withSession(['probe' => 'before']);
        $beforeSessionId = session()->getId();

        $this->postJson('/auth/zklogin', [
            'wallet_address' => '0xzkloginwallet',
            'email' => 'zklogin@example.com',
            'zk_pin_verifier' => hash('sha256', 'zklogin@example.com|123456'),
        ])->assertOk()
            ->assertJson([
                'message' => 'Authenticated successfully',
                'redirect' => route('wallet.welcome'),
            ]);

        $this->assertAuthenticated();
        $this->assertNotSame($beforeSessionId, session()->getId());
    }
}
