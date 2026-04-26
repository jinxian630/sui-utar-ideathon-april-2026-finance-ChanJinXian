<?php

namespace App\Http\Controllers;

use App\Models\SavingsEntry;
use App\Http\Requests\StoreSavingsRequest;
use App\Http\Requests\UpdateSavingsRequest;
use App\Jobs\SyncSavingsToChainJob;
use App\Jobs\StakeJob;
use App\Services\SavingsService;
use App\Services\BadgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SavingsController extends Controller
{
    public function __construct(
        protected SavingsService $savingsService,
        protected BadgeService   $badgeService
    ) {}

    public function index(Request $request): View
    {
        $query = auth()->user()
            ->savingsEntries()
            ->with('goal')
            ->latest('created_at');

        // ── Preset Quick-Filters ──────────────────────────────────────────
        match ($request->preset) {
            'this_month' => $query->whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year),
            'pending'    => $query->where('synced_on_chain', false),
            'settled'    => $query->where('synced_on_chain', true),
            default      => null,
        };

        // ── Standard Filters ──────────────────────────────────────────────
        if ($request->filled('type'))      { $query->where('type', $request->type); }
        if ($request->filled('date_from')) { $query->whereDate('created_at', '>=', $request->date_from); }
        if ($request->filled('date_to'))   { $query->whereDate('created_at', '<=', $request->date_to); }
        if ($request->filled('goal_id'))   { $query->where('goal_id', $request->goal_id); }

        $entries = $query->paginate(15)->withQueryString();
        $goals   = auth()->user()->goals()->where('is_active', true)->get();
        $user    = auth()->user();

        // ── Data-Insight Metrics ──────────────────────────────────────────
        $totalSavedAll       = $user->savingsEntries()
                                    ->where('type', 'income')
                                    ->whereNotNull('goal_id')
                                    ->sum('amount');
        $savingsRateMonth    = $user->savingsEntries()->where('type', 'income')
                                    ->whereNotNull('goal_id')
                                    ->where('created_at', '>=', now()->subDays(30))->sum('amount');
        $activeGoalsCount    = $goals->count();
        $completedGoalsCount = $user->goals()->whereColumn('current_amount', '>=', 'target_amount')->count();

        // ── Smart Recommendation ──────────────────────────────────────────
        $closestGoal         = $goals->filter(fn($g) => $g->current_amount < $g->target_amount)
                                     ->sortByDesc('progress_percent')->first();
        $smartRecommendation = $closestGoal
            ? 'You are just RM ' . number_format($closestGoal->target_amount - $closestGoal->current_amount, 2)
              . " away from your \"{$closestGoal->name}\" goal — keep going!"
            : null;

        return view('savings.index', compact(
            'entries', 'goals', 'user',
            'totalSavedAll', 'savingsRateMonth',
            'activeGoalsCount', 'completedGoalsCount',
            'smartRecommendation'
        ));
    }

    public function create(): View
    {
        $goals = auth()->user()->goals()->where('is_active', true)->get();
        return view('savings.create', compact('goals'));
    }

    public function store(StoreSavingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        // ── 1. Wallet Balance Guard (only for goal deposits) ──────────────
        if (!empty($data['goal_id'])) {
            $availableBalance = $this->savingsService->getAvailableBalance($user->id);
            if ($availableBalance < (float) $data['amount']) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'amount' => "Insufficient wallet balance. You have RM " .
                                    number_format($availableBalance, 2) .
                                    " available. Please add income first."
                    ]);
            }
        }

        // ── 2. Handle dynamic goal assignment ────────────────────────────
        if (!empty($data['goal_name'])) {
            $goal = $user->goals()->firstOrCreate(
                ['name' => $data['goal_name']],
                ['target_amount' => 0, 'emoji' => '🎯', 'color' => '4F46E5', 'is_active' => true]
            );
            $data['goal_id'] = $goal->id;
        }
        unset($data['goal_name']);

        // ── 3. Calculate round-up if toggle is on ─────────────────────────
        if (!empty($data['enable_round_up'])) {
            $data['round_up_amount'] = $this->savingsService->calculateRoundUp($data['amount'], 10);
        }

        // ── 4. Persist savings entry to DB ────────────────────────────────
        if (!empty($data['enable_stake'])) {
            $data['staked'] = true;
        }

        $entry = $user->savingsEntries()->create($data);

        // ── 5. Wallet credit/debit logic ──────────────────────────────────
        if (!empty($data['goal_id'])) {
            // Deposit to goal → DEDUCT from wallet
            $this->savingsService->debitWallet($user->id, (float) $data['amount']);
        } else {
            // Regular income (non-goal) → ADD to wallet
            if (($data['type'] ?? '') === 'income') {
                $this->savingsService->creditWallet($user->id, (float) $data['amount']);
            } elseif (($data['type'] ?? '') === 'expense') {
                $this->savingsService->debitWallet($user->id, (float) $data['amount']);
            }
        }

        // ── 6. Recalculate total_saved (non-goal income net) ──────────────
        $this->savingsService->syncUserTotal($user->id);

        // ── 7. Update goal progress ───────────────────────────────────────
        if (!empty($data['goal_id'])) {
            $this->savingsService->syncGoalTotal($data['goal_id']);
        }

        // ── 8. Check milestone badges + award rebate (goal saves only) ────
        $rebate = 0.0;
        $badgePayloads = [];
        if (!empty($data['goal_id'])) {
            $rebate = $this->badgeService->checkMilestonesAndAwardRebate($user->fresh());
            $badgePayloads = $this->badgeService->newlyAwardedBadges();
        }

        // ── 9. Dispatch async blockchain sync ─────────────────────────────
        SyncSavingsToChainJob::dispatch($entry);

        // ── 10. Dispatch auto-stake if enabled ────────────────────────────
        if (!empty($data['enable_stake'])) {
            StakeJob::dispatch($entry);
        }

        // ── 11. Build success message ─────────────────────────────────────
        $successMsg = 'Savings deposited to your goal!';
        if ($rebate > 0) {
            $successMsg .= " 🎉 Milestone unlocked! Rebate of RM " . number_format($rebate, 2) . " added to your wallet.";
        }

        return redirect()->route('dashboard')
            ->with('success', $successMsg)
            ->with('badge_earned_payloads', $badgePayloads);
    }

    public function edit(SavingsEntry $saving): View
    {
        if ($saving->user_id !== auth()->id()) abort(403);
        return view('savings.edit', compact('saving'));
    }

    public function update(UpdateSavingsRequest $request, SavingsEntry $saving): RedirectResponse
    {
        if ($saving->user_id !== auth()->id()) abort(403);

        $saving->update($request->only('description'));

        return redirect()->route('savings.index')->with('success', 'Entry updated.');
    }

    public function destroy(SavingsEntry $saving): RedirectResponse
    {
        if ($saving->user_id !== auth()->id()) abort(403);

        return back()->withErrors([
            'savings' => 'Savings history is locked. You can edit the description only.',
        ]);
    }
}
