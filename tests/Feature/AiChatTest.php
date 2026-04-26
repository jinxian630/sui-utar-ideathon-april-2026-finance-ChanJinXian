<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\ChatLog;
use App\Models\Goal;
use App\Models\SavingsEntry;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AiChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_with_wallet_can_chat_and_logs_messages(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'gemini-3-flash-preview',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => 'You need RM 250.00 more to reach Investor Level 2.',
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $user = User::factory()->create([
            'wallet_address' => '0xtestwallet',
            'wallet_balance' => 1000,
            'rebate_earned' => 10,
        ]);

        $response = $this->actingAs($user)->postJson('/api/chat', [
            'message' => 'Analyze my saving habits',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'reply' => 'You need RM 250.00 more to reach Investor Level 2.',
            ]);

        $this->assertDatabaseHas('chat_logs', [
            'user_id' => $user->id,
            'role' => 'user',
            'message' => 'Analyze my saving habits',
        ]);

        $this->assertDatabaseHas('chat_logs', [
            'user_id' => $user->id,
            'role' => 'assistant',
            'message' => 'You need RM 250.00 more to reach Investor Level 2.',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'models/gemini-3-flash-preview:generateContent')
            && $request->hasHeader('x-goog-api-key', 'test-key'));
    }

    public function test_user_without_wallet_is_blocked(): void
    {
        $user = User::factory()->create([
            'wallet_address' => null,
            'sui_address' => null,
        ]);

        $this->actingAs($user)->postJson('/api/chat', [
            'message' => 'Help me save more',
        ])->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Connect your Sui wallet before using AI financial planning.',
            ]);

        $this->assertDatabaseCount('chat_logs', 0);
    }

    public function test_guest_cannot_chat(): void
    {
        $this->postJson('/api/chat', [
            'message' => 'Help me save more',
        ])->assertUnauthorized();
    }

    public function test_context_snapshot_contains_financial_progress_and_sui_status(): void
    {
        $user = User::factory()->create([
            'wallet_address' => '0xtestwallet',
            'sui_finance_profile_id' => '0xprofile',
            'wallet_balance' => 300,
            'rebate_earned' => 12.50,
        ]);

        $goal = Goal::create([
            'user_id' => $user->id,
            'name' => 'Emergency Fund',
            'target_amount' => 5000,
            'current_amount' => 750,
            'deadline' => now()->addMonths(2)->toDateString(),
            'is_active' => true,
        ]);

        SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'type' => 'income',
            'amount' => 300,
            'round_up_amount' => 25,
            'description' => 'Salary deposit',
            'category' => 'Income',
            'entry_date' => now()->subWeek()->toDateString(),
            'synced_on_chain' => true,
        ]);

        SavingsEntry::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'type' => 'income',
            'amount' => 400,
            'round_up_amount' => 25,
            'description' => 'Bonus deposit',
            'category' => 'Income',
            'entry_date' => now()->toDateString(),
            'synced_on_chain' => true,
        ]);

        Badge::create([
            'user_id' => $user->id,
            'slug' => 'investor',
            'name' => 'Investor Level 2',
            'threshold' => 500,
            'level' => 2,
            'sui_object_id' => '0xbadge',
            'sui_digest' => 'digest',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Coffee',
            'amount' => 8,
            'type' => 'expense',
        ]);

        $context = app(AiChatService::class)->buildContext($user->fresh());

        $this->assertSame(750.0, $context['balances']['goal_deposit_total_rm']);
        $this->assertSame('Investor Level 2', $context['badges']['current']['name']);
        $this->assertSame('Wealth Builder Level 3', $context['badges']['next']['name']);
        $this->assertSame(250.0, $context['badges']['next']['gap_rm']);
        $this->assertTrue($context['on_chain_status']['finance_profile_created']);
        $this->assertTrue($context['on_chain_status']['recent_savings_all_synced']);
        $this->assertSame('Emergency Fund', $context['goals'][0]['name']);
        $this->assertNotNull($context['forecast']['average_weekly_saving_rm']);
    }

    public function test_gemini_failure_returns_fallback_and_does_not_crash(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'failed']], 500),
        ]);

        $user = User::factory()->create([
            'wallet_address' => '0xtestwallet',
            'wallet_balance' => 100,
        ]);

        $response = $this->actingAs($user)->postJson('/api/chat', [
            'message' => 'When will I reach Diamond Saver?',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertStringContainsString('I cannot reach Gemini right now', $response->json('reply'));
        $this->assertSame(2, ChatLog::where('user_id', $user->id)->count());
    }
}
