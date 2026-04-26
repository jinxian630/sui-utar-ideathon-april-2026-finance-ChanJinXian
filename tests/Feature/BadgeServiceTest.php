<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\SavingsEntry;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\SuiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_badge_service_stores_sui_object_metadata_when_badge_is_minted(): void
    {
        $user = User::factory()->create([
            'wallet_address' => '0x' . str_repeat('a', 64),
        ]);

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
            'amount' => 100.00,
            'type' => 'income',
            'entry_date' => now()->toDateString(),
        ]);

        $this->mock(SuiService::class, function ($mock) use ($user) {
            $mock->shouldReceive('claimMilestoneBadge')
                ->once()
                ->withArgs(fn (
                    string $recipientAddress,
                    string $tierSlug,
                    int $tierLevel,
                    int $thresholdCents,
                    int $tierBonusBps,
                    bool $isStacked
                ) => $recipientAddress === $user->wallet_address
                    && $tierSlug === 'saver'
                    && $tierLevel === 1
                    && $thresholdCents === 10000
                    && $tierBonusBps === 0
                    && $isStacked === false)
                ->andReturn([
                    'digest' => '9BadgeDigest111111111111111111111111111111111111111',
                    'badge_object_id' => '0x' . str_repeat('b', 64),
                    'badge_level' => 1,
                    'suivision_url' => 'https://testnet.suivision.xyz/object/0x' . str_repeat('b', 64),
                ]);
        });

        app(BadgeService::class)->checkMilestonesAndAwardRebate($user->fresh());

        $this->assertDatabaseHas('badges', [
            'user_id' => $user->id,
            'slug' => 'saver',
            'level' => 1,
            'sui_digest' => '9BadgeDigest111111111111111111111111111111111111111',
            'sui_object_id' => '0x' . str_repeat('b', 64),
            'suivision_url' => 'https://testnet.suivision.xyz/object/0x' . str_repeat('b', 64),
        ]);
    }

    public function test_transaction_income_does_not_unlock_badges(): void
    {
        $user = User::factory()->create([
            'wallet_address' => '0x' . str_repeat('a', 64),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Salary',
            'amount' => 1000.00,
            'type' => 'income',
        ]);

        $this->mock(SuiService::class, function ($mock) {
            $mock->shouldReceive('claimMilestoneBadge')->never();
        });

        app(BadgeService::class)->checkMilestonesAndAwardRebate($user->fresh());

        $this->assertDatabaseMissing('badges', [
            'user_id' => $user->id,
        ]);
    }
}
