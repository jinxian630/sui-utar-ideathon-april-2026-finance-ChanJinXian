<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use App\Models\SavingsEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_savings_entry(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_create_savings']);
        $this->actingAs($user)
            ->post(route('savings.store'), [
                'amount'      => 12.50,
                'type'        => 'income',
                'note'        => 'Lunch savings',
                'description' => 'Saved by skipping lunch out',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('savings_entries', [
            'user_id' => $user->id,
            'amount' => 12.50,
            'type' => 'income',
            'note' => 'Lunch savings',
        ]);
    }

    public function test_amount_must_be_greater_than_zero(): void
    {
        $this->actingAs(User::factory()->create(['wallet_address' => '0xwallet_invalid_amount']))
            ->post(route('savings.store'), ['amount' => 0, 'type' => 'income'])
            ->assertSessionHasErrors('amount');
    }

    public function test_total_saved_is_synced_after_creation(): void
    {
        $user = User::factory()->create();
        $goal = Goal::create([
            'user_id' => $user->id,
            'name' => 'Emergency Fund',
            'target_amount' => 500.00,
            'current_amount' => 0,
            'emoji' => 'EF',
            'color' => '4F46E5',
            'is_active' => true,
        ]);

        SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'amount' => 50.00,
            'type' => 'income',
            'entry_date' => now()->toDateString(),
        ]);
        SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'amount' => 25.00,
            'type' => 'income',
            'entry_date' => now()->toDateString(),
        ]);

        // total_saved gets populated through the observer
        $this->assertEquals(75.00, $user->fresh()->total_saved);
    }

    public function test_non_goal_savings_entry_does_not_count_toward_total_saved(): void
    {
        $user = User::factory()->create();

        SavingsEntry::create([
            'user_id' => $user->id,
            'amount' => 75.00,
            'type' => 'income',
            'entry_date' => now()->toDateString(),
        ]);

        $this->assertEquals(0.00, (float) $user->fresh()->total_saved);
    }

    public function test_goal_deposit_reduces_wallet_and_updates_goal_savings(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_goal_deposit', 'wallet_balance' => 200.00, 'total_saved' => 0.00]);
        $goal = Goal::create([
            'user_id' => $user->id,
            'name' => 'Trip',
            'target_amount' => 500.00,
            'current_amount' => 0,
            'emoji' => 'TR',
            'color' => '4F46E5',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('savings.store'), [
                'amount' => 50.00,
                'type' => 'income',
                'note' => 'Goal deposit',
                'description' => 'Move money into goal',
                'goal_id' => $goal->id,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertEquals(150.00, (float) $user->fresh()->wallet_balance);
        $this->assertEquals(50.00, (float) $user->fresh()->total_saved);
        $this->assertEquals(50.00, (float) $goal->fresh()->current_amount);
    }

    public function test_creating_goal_does_not_reduce_wallet_or_savings_progress(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_create_goal', 'wallet_balance' => 200.00, 'total_saved' => 0.00]);

        $this->actingAs($user)
            ->post(route('goals.store'), [
                'name' => 'Laptop',
                'target_amount' => 1000.00,
                'emoji' => 'PC',
                'color' => '4F46E5',
            ]);

        $this->assertDatabaseHas('goals', [
            'user_id' => $user->id,
            'name' => 'Laptop',
        ]);
        $this->assertEquals(200.00, (float) $user->fresh()->wallet_balance);
        $this->assertEquals(0.00, (float) $user->fresh()->total_saved);
    }

    public function test_stake_choice_is_saved_when_creating_savings_entry(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_stake_choice', 'wallet_balance' => 800.00]);
        $goal = Goal::create([
            'user_id' => $user->id,
            'name' => 'Travel',
            'target_amount' => 700.00,
            'current_amount' => 0,
            'emoji' => 'TR',
            'color' => '4F46E5',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('savings.store'), [
                'amount' => 700.00,
                'type' => 'income',
                'goal_id' => $goal->id,
                'enable_stake' => 1,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('savings_entries', [
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'amount' => 700.00,
            'staked' => true,
        ]);
    }

    public function test_withdrawing_staked_goal_includes_milestone_rebate_rate(): void
    {
        $user = User::factory()->create([
            'wallet_address' => '0xwallet_withdraw_goal',
            'wallet_balance' => 0.00,
            'rebate_earned' => 0.00,
        ]);
        $goal = Goal::create([
            'user_id' => $user->id,
            'name' => 'Travel',
            'target_amount' => 700.00,
            'current_amount' => 700.00,
            'emoji' => 'TR',
            'color' => '4F46E5',
            'is_active' => true,
        ]);

        SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'amount' => 700.00,
            'type' => 'income',
            'staked' => true,
            'stake_digest' => null,
            'entry_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('goals.withdraw', $goal))
            ->assertSessionHas('success');

        $this->assertEquals(710.50, (float) $user->fresh()->wallet_balance);
        $this->assertEquals(10.50, (float) $user->fresh()->rebate_earned);
        $this->assertEquals(0.00, (float) $goal->fresh()->current_amount);
        $this->assertFalse($goal->fresh()->is_active);
    }

    public function test_savings_update_changes_description_only(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_update_savings']);
        $entry = SavingsEntry::create([
            'user_id' => $user->id,
            'amount' => 75.00,
            'type' => 'income',
            'note' => 'Original note',
            'description' => 'Original description',
            'category' => 'food',
            'entry_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->patch(route('savings.update', $entry), [
                'note' => 'Changed note',
                'description' => 'Changed description',
                'category' => 'transport',
                'entry_date' => now()->subDay()->toDateString(),
            ])
            ->assertRedirect(route('savings.index'));

        $entry->refresh();
        $this->assertSame('Original note', $entry->note);
        $this->assertSame('Changed description', $entry->description);
        $this->assertSame('food', $entry->category);
    }

    public function test_savings_delete_request_is_rejected(): void
    {
        $user = User::factory()->create(['wallet_address' => '0xwallet_delete_savings']);
        $entry = SavingsEntry::create([
            'user_id' => $user->id,
            'amount' => 75.00,
            'type' => 'income',
            'entry_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->delete(route('savings.destroy', $entry))
            ->assertSessionHasErrors('savings');

        $this->assertDatabaseHas('savings_entries', [
            'id' => $entry->id,
            'deleted_at' => null,
        ]);
    }
}
