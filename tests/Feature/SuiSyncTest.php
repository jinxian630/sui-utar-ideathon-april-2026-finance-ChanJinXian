<?php

namespace Tests\Feature;

use App\Models\SavingsEntry;
use App\Models\User;
use App\Services\SuiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuiSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_sui_finance_profile_id(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_profile']);
        $profileId = '0x' . str_repeat('a', 64);

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

    public function test_user_can_mark_own_savings_entry_on_chain(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_mark_entry']);
        $entry = SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => null,
            'type' => 'income',
            'amount' => 25.00,
            'round_up_amount' => 0,
            'note' => 'Important income',
            'description' => 'Important income',
            'category' => 'other',
            'entry_date' => now()->toDateString(),
        ]);

        $this->mock(SuiService::class, function ($mock) use ($entry, $user) {
            $mock->shouldReceive('syncSavingsEntry')
                ->once()
                ->withArgs(fn ($actualEntry, $actualUser) => $actualEntry->is($entry) && $actualUser->is($user))
                ->andReturn([
                    'profile_object_id' => '0x' . str_repeat('b', 64),
                    'profile_digest' => '4ProfileDigest111111111111111111111111111111111111111',
                    'digest' => '5QbBFQ5nG6QKXawY6Dfv4myqDhYgXqAjf3kpUwnxQvKx',
                ]);
        });

        $this->actingAs($user)
            ->postJson(route('sui.savings.mark-on-chain', $entry))
            ->assertOk()
            ->assertJson([
                'synced_on_chain' => true,
                'sui_digest' => '5QbBFQ5nG6QKXawY6Dfv4myqDhYgXqAjf3kpUwnxQvKx',
                'profile_object_id' => '0x' . str_repeat('b', 64),
            ]);

        $entry->refresh();
        $this->assertTrue($entry->synced_on_chain);
        $this->assertSame('5QbBFQ5nG6QKXawY6Dfv4myqDhYgXqAjf3kpUwnxQvKx', $entry->sui_digest);
        $this->assertSame('0x' . str_repeat('b', 64), $user->fresh()->sui_finance_profile_id);
    }

    public function test_user_can_mark_own_savings_entry_on_chain_from_form_post(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_mark_entry_form']);
        $entry = SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => null,
            'type' => 'income',
            'amount' => 25.00,
            'round_up_amount' => 0,
            'note' => 'Important income',
            'description' => 'Important income',
            'category' => 'other',
            'entry_date' => now()->toDateString(),
        ]);

        $this->mock(SuiService::class, function ($mock) use ($entry, $user) {
            $mock->shouldReceive('syncSavingsEntry')
                ->once()
                ->withArgs(fn ($actualEntry, $actualUser) => $actualEntry->is($entry) && $actualUser->is($user))
                ->andReturn([
                    'profile_object_id' => '0x' . str_repeat('c', 64),
                    'profile_digest' => '4ProfileDigest222222222222222222222222222222222222222',
                    'digest' => 'Gj5R1tou5TkFavDk4VdZ4HG5mintX4sczuRb7mqHmKpW',
                ]);
        });

        $this->actingAs($user)
            ->from(route('savings.index'))
            ->post(route('sui.savings.mark-on-chain', $entry))
            ->assertRedirect(route('savings.index'))
            ->assertSessionHas('status', 'Published to Sui Testnet.')
            ->assertSessionHas('suivision_url', 'https://testnet.suivision.xyz/txblock/Gj5R1tou5TkFavDk4VdZ4HG5mintX4sczuRb7mqHmKpW');

        $entry->refresh();
        $this->assertTrue($entry->synced_on_chain);
        $this->assertSame('Gj5R1tou5TkFavDk4VdZ4HG5mintX4sczuRb7mqHmKpW', $entry->sui_digest);
        $this->assertSame('0x' . str_repeat('c', 64), $user->fresh()->sui_finance_profile_id);
    }

    public function test_user_cannot_mark_another_users_entry_on_chain(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create(['wallet_address' => '0xwallet_other_user']);
        $entry = SavingsEntry::create([
            'user_id' => $owner->id,
            'goal_id' => null,
            'type' => 'income',
            'amount' => 25.00,
            'round_up_amount' => 0,
            'note' => 'Private income',
            'description' => 'Private income',
            'category' => 'other',
            'entry_date' => now()->toDateString(),
        ]);

        $this->actingAs($other)
            ->postJson(route('sui.savings.mark-on-chain', $entry))
            ->assertForbidden();

        $this->assertFalse($entry->fresh()->synced_on_chain);
    }

    public function test_user_cannot_overwrite_already_synced_entry_digest(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_synced_entry']);
        $entry = SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => null,
            'type' => 'income',
            'amount' => 25.00,
            'round_up_amount' => 0,
            'note' => 'Settled income',
            'description' => 'Settled income',
            'category' => 'other',
            'entry_date' => now()->toDateString(),
            'synced_on_chain' => true,
            'sui_digest' => '5QbBFQ5nG6QKXawY6Dfv4myqDhYgXqAjf3kpUwnxQvKx',
        ]);

        $this->actingAs($user)
            ->postJson(route('sui.savings.mark-on-chain', $entry))
            ->assertStatus(409);

        $this->assertSame('5QbBFQ5nG6QKXawY6Dfv4myqDhYgXqAjf3kpUwnxQvKx', $entry->fresh()->sui_digest);
    }
}
