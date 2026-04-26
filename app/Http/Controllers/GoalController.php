<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoalController extends Controller
{
    private const STAKING_REBATE_TIERS = [
        ['threshold' => 10000, 'rate_bps' => 500],
        ['threshold' => 5000, 'rate_bps' => 300],
        ['threshold' => 1000, 'rate_bps' => 200],
        ['threshold' => 500, 'rate_bps' => 150],
        ['threshold' => 100, 'rate_bps' => 100],
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'deadline' => 'nullable|date',
        ]);

        auth()->user()->goals()->create($validated);

        return back()->with('success', 'Goal created successfully!');
    }

    public function update(Request $request, Goal $goal)
    {
        if ($goal->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'target_amount' => 'numeric|min:0',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'deadline' => 'nullable|date',
        ]);

        $goal->update($validated);

        return back()->with('success', 'Goal updated successfully!');
    }

    public function destroy(Goal $goal)
    {
        if ($goal->user_id !== auth()->id()) abort(403);

        $goal->update(['is_active' => false]);

        return back()->with('success', 'Goal archived successfully!');
    }

    public function withdraw(Goal $goal): RedirectResponse
    {
        if ($goal->user_id !== auth()->id()) abort(403);

        if ($goal->current_amount < $goal->target_amount) {
            return back()->withErrors(['withdraw' => 'Goal is not yet complete. You cannot withdraw yet.']);
        }

        $principalAmount = (float) $goal->current_amount;
        $stakingRebate = $this->calculateStakingRebate($goal, $principalAmount);
        $withdrawAmount = round($principalAmount + $stakingRebate, 2);

        // Credit the goal balance plus any staking rebate back to the user's wallet.
        DB::table('users')
            ->where('id', auth()->id())
            ->increment('wallet_balance', $withdrawAmount);

        if ($stakingRebate > 0) {
            DB::table('users')
                ->where('id', auth()->id())
                ->increment('rebate_earned', $stakingRebate);
        }

        $goal->forceFill([
            'current_amount' => 0,
            'is_active' => false,
            'withdrawn_at' => now(),
        ])->save();

        $message = 'Goal "' . $goal->name . '" withdrawn! RM ' .
            number_format($withdrawAmount, 2) . ' has been returned to your wallet.';

        if ($stakingRebate > 0) {
            $message .= ' Includes RM ' . number_format($stakingRebate, 2) . ' staking rebate.';
        }

        return back()->with('success', $message);
    }

    private function calculateStakingRebate(Goal $goal, float $amount): float
    {
        $hasStakeRequested = $goal->savingsEntries()
            ->where('staked', true)
            ->exists();

        if (!$hasStakeRequested) {
            return 0.0;
        }

        foreach (self::STAKING_REBATE_TIERS as $tier) {
            if ($amount >= $tier['threshold']) {
                return round($amount * $tier['rate_bps'] / 10000, 2);
            }
        }

        return 0.0;
    }
}
