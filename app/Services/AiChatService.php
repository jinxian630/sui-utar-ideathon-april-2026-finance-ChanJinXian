<?php

namespace App\Services;

use App\Models\ChatLog;
use App\Models\SavingsEntry;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private array $milestones = [
        ['slug' => 'saver', 'level' => 1, 'threshold' => 100, 'name' => 'Saver Level 1'],
        ['slug' => 'investor', 'level' => 2, 'threshold' => 500, 'name' => 'Investor Level 2'],
        ['slug' => 'wealth_builder', 'level' => 3, 'threshold' => 1000, 'name' => 'Wealth Builder Level 3'],
        ['slug' => 'diamond_saver', 'level' => 4, 'threshold' => 5000, 'name' => 'Diamond Saver Level 4'],
        ['slug' => 'finance_master', 'level' => 5, 'threshold' => 10000, 'name' => 'Finance Master Level 5'],
    ];

    public function generateForecast(array $savingsData): string
    {
        if ($savingsData !== []) {
            $total = collect($savingsData)->sum(fn (array $entry) => (float) ($entry['amount'] ?? 0) + (float) ($entry['round_up_amount'] ?? 0));
            $count = count($savingsData);

            return 'Based on ' . $count . ' savings entries totalling RM ' . number_format($total, 2) .
                ', you are building steady progress toward your goals.';
        }

        return "Based on your recent savings data, you are on track to hit your goals! Keep up the great work.";
    }

    public function chat(User $user, string $message): string
    {
        $context = $this->buildContext($user);
        $history = $this->recentConversation($user);
        $reply = $this->callGemini($context, $history, $message);

        ChatLog::create([
            'user_id' => $user->id,
            'role' => 'user',
            'message' => $message,
        ]);

        ChatLog::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'message' => $reply,
        ]);

        return $reply;
    }

    public function buildContext(User $user): array
    {
        $totalSaved = (float) SavingsEntry::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereNotNull('goal_id')
            ->sum(DB::raw('amount + COALESCE(round_up_amount, 0)'));

        $recentSavings = SavingsEntry::where('user_id', $user->id)
            ->latest('entry_date')
            ->latest('id')
            ->take(5)
            ->get(['type', 'amount', 'round_up_amount', 'description', 'category', 'entry_date', 'synced_on_chain'])
            ->map(fn (SavingsEntry $entry) => [
                'type' => $entry->type,
                'amount_rm' => (float) $entry->amount,
                'round_up_rm' => (float) $entry->round_up_amount,
                'description' => $entry->description,
                'category' => $entry->category,
                'entry_date' => optional($entry->entry_date)->toDateString(),
                'synced_on_chain' => (bool) $entry->synced_on_chain,
            ])
            ->values()
            ->all();

        $recentTransactions = Transaction::where('user_id', $user->id)
            ->latest('id')
            ->take(5)
            ->get(['description', 'amount', 'type'])
            ->map(fn (Transaction $transaction) => [
                'description' => $transaction->description,
                'amount_rm' => (float) $transaction->amount,
                'type' => $transaction->type,
            ])
            ->values()
            ->all();

        $highestBadge = $user->badges()
            ->orderByDesc('level')
            ->orderByDesc('threshold')
            ->first(['name', 'slug', 'level', 'threshold', 'sui_object_id', 'sui_digest']);

        $nextBadge = collect($this->milestones)->first(fn (array $milestone) => $totalSaved < $milestone['threshold'])
            ?? $this->milestones[array_key_last($this->milestones)];

        $activeGoals = $user->goals()
            ->active()
            ->orderBy('deadline')
            ->get(['name', 'target_amount', 'current_amount', 'deadline'])
            ->map(fn ($goal) => [
                'name' => $goal->name,
                'target_amount_rm' => (float) $goal->target_amount,
                'current_amount_rm' => (float) $goal->current_amount,
                'remaining_rm' => max(0, (float) $goal->target_amount - (float) $goal->current_amount),
                'deadline' => optional($goal->deadline)->toDateString(),
            ])
            ->values()
            ->all();

        return [
            'user' => [
                'name' => $user->name,
                'wallet_connected' => filled($user->wallet_address) || filled($user->sui_address),
                'wallet_address' => $user->wallet_address ?? $user->sui_address,
                'sui_finance_profile_id' => $user->sui_finance_profile_id,
            ],
            'balances' => [
                'goal_deposit_total_rm' => round($totalSaved, 2),
                'wallet_balance_rm' => round((float) ($user->wallet_balance ?? 0), 2),
                'rebate_earned_rm' => round((float) ($user->rebate_earned ?? 0), 2),
            ],
            'badges' => [
                'current' => $highestBadge ? [
                    'name' => $highestBadge->name,
                    'slug' => $highestBadge->slug,
                    'level' => (int) $highestBadge->level,
                    'threshold_rm' => (float) $highestBadge->threshold,
                    'has_on_chain_object' => filled($highestBadge->sui_object_id),
                    'has_sui_digest' => filled($highestBadge->sui_digest),
                ] : null,
                'next' => [
                    'name' => $nextBadge['name'],
                    'slug' => $nextBadge['slug'],
                    'level' => $nextBadge['level'],
                    'threshold_rm' => $nextBadge['threshold'],
                    'gap_rm' => max(0, round($nextBadge['threshold'] - $totalSaved, 2)),
                ],
            ],
            'activity' => [
                'recent_savings_entries' => $recentSavings,
                'recent_transactions' => $recentTransactions,
            ],
            'goals' => $activeGoals,
            'forecast' => $this->forecastFromSavings($user, $totalSaved),
            'on_chain_status' => [
                'finance_profile_created' => filled($user->sui_finance_profile_id),
                'recent_savings_all_synced' => collect($recentSavings)->isNotEmpty()
                    ? collect($recentSavings)->every(fn (array $entry) => $entry['synced_on_chain'])
                    : false,
                'verification_scope' => 'Local MySQL Sui metadata only; no live Sui RPC audit was performed for this chat.',
            ],
        ];
    }

    private function recentConversation(User $user): array
    {
        return ChatLog::where('user_id', $user->id)
            ->latest('id')
            ->take(10)
            ->get(['role', 'message'])
            ->reverse()
            ->values()
            ->map(fn (ChatLog $log) => [
                'role' => $log->role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $log->message],
                ],
            ])
            ->all();
    }

    private function forecastFromSavings(User $user, float $totalSaved): array
    {
        $entries = SavingsEntry::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereNotNull('goal_id')
            ->orderBy('entry_date')
            ->get(['amount', 'round_up_amount', 'entry_date']);

        if ($entries->count() < 2) {
            return [
                'average_weekly_saving_rm' => null,
                'note' => 'At least two goal savings entries are needed for a date forecast.',
            ];
        }

        $firstDate = $entries->first()->entry_date;
        $lastDate = $entries->last()->entry_date;
        $days = max(1, $firstDate->diffInDays($lastDate) + 1);
        $weeks = max(1, $days / 7);
        $averageWeekly = $entries->sum(fn (SavingsEntry $entry) => (float) $entry->amount + (float) $entry->round_up_amount) / $weeks;

        $nextBadge = collect($this->milestones)->first(fn (array $milestone) => $totalSaved < $milestone['threshold']);
        $weeksToNext = $nextBadge && $averageWeekly > 0
            ? (int) ceil(max(0, $nextBadge['threshold'] - $totalSaved) / $averageWeekly)
            : null;

        return [
            'average_weekly_saving_rm' => round($averageWeekly, 2),
            'next_badge_estimated_date' => $weeksToNext !== null ? now()->addWeeks($weeksToNext)->toDateString() : null,
        ];
    }

    private function callGemini(array $context, array $history, string $message): string
    {
        $apiKey = config('services.gemini.key');

        if (blank($apiKey)) {
            return $this->fallbackReply($context);
        }

        $model = config('services.gemini.model', 'gemini-3-flash-preview');
        $baseUrl = rtrim(config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $url = "{$baseUrl}/models/{$model}:generateContent";

        $contents = array_merge($history, [[
            'role' => 'user',
            'parts' => [
                ['text' => $message],
            ],
        ]]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [[
                            'text' => $this->systemPrompt($context),
                        ]],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.45,
                        'maxOutputTokens' => 700,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Gemini chat request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackReply($context);
            }

            $reply = trim(collect(data_get($response->json(), 'candidates.0.content.parts', []))
                ->pluck('text')
                ->filter()
                ->implode("\n"));

            return $reply !== '' ? $reply : $this->fallbackReply($context);
        } catch (\Throwable $exception) {
            Log::warning('Gemini chat request errored', [
                'error' => $exception->getMessage(),
            ]);

            return $this->fallbackReply($context);
        }
    }

    private function systemPrompt(array $context): string
    {
        return "You are the Finance Tracker AI, a concise financial planning assistant for a Laravel savings app.\n"
            . "Use only the supplied user snapshot for account facts. Do not invent balances, badges, transactions, or on-chain verification.\n"
            . "Give practical savings guidance in Malaysian Ringgit (RM). Mention that advice is educational, not guaranteed financial advice, when appropriate.\n"
            . "For badge forecasts, use the supplied forecast values and explain the assumptions. For on-chain questions, state the verification scope exactly.\n"
            . "Prefer 2-4 short paragraphs or bullets.\n\n"
            . "Current user snapshot:\n"
            . json_encode($context, JSON_PRETTY_PRINT);
    }

    private function fallbackReply(array $context): string
    {
        $next = $context['badges']['next'];
        $total = $context['balances']['goal_deposit_total_rm'];

        return 'I cannot reach Gemini right now, but based on your saved goal deposits of RM ' .
            number_format($total, 2) . ', your next badge is ' . $next['name'] .
            '. You need RM ' . number_format($next['gap_rm'], 2) .
            ' more. A practical next step is to make a small goal deposit after each income entry and use round-ups where possible.';
    }
}
