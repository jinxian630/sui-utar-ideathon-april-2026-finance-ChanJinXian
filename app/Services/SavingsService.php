<?php

namespace App\Services;

use App\Models\SavingsEntry;
use App\Models\Goal;
use Illuminate\Support\Facades\DB;

class SavingsService
{
    /**
     * Round amount up to the nearest $nearest, return the difference.
     *
     * Examples (nearest = 10):
     *   amount=7.50  → roundUp=2.50  (next RM 10 = 10.00)
     *   amount=13.00 → roundUp=7.00  (next RM 10 = 20.00)
     *   amount=20.00 → roundUp=0.00  (already on boundary)
     */
    public function calculateRoundUp(float $amount, int $nearest = 10): float
    {
        $remainder = fmod($amount, $nearest);
        return $remainder > 0 ? round($nearest - $remainder, 4) : 0.00;
    }

    /**
     * Recalculate and persist users.total_saved.
     *
     * Wallet logic:
     *  - Income transactions that are NOT linked to a goal → ADD to wallet
     *  - Expense transactions → DEDUCT from wallet
     *  - Goal deposits are tracked separately in goals.current_amount.
     *    They are NOT included in total_saved here because the wallet is
     *    decremented at the point of deposit (in SavingsController@store).
     *
     * wallet_balance is managed separately (includes rebates and is decremented
     * on goal deposit). total_saved reflects only the non-goal income.
     */
    public function syncUserTotal(int $userId): void
    {
        $goalDepositTotal = SavingsEntry::where('user_id', $userId)
            ->where('type', 'income')
            ->whereNotNull('goal_id')
            ->sum(DB::raw('amount + COALESCE(round_up_amount, 0)'));

        DB::table('users')
            ->where('id', $userId)
            ->update(['total_saved' => max(0, $goalDepositTotal)]);
    }

    /**
     * Recalculate a goal's current_amount.
     * Sums all income savings entries assigned to the goal.
     */
    public function syncGoalTotal(int $goalId): void
    {
        $total = SavingsEntry::where('goal_id', $goalId)
            ->where('type', 'income')
            ->sum(DB::raw('amount + COALESCE(round_up_amount, 0)'));

        Goal::where('id', $goalId)->update(['current_amount' => $total]);
    }

    /**
     * Get a user's effective available balance.
     * This is total_saved (non-goal income minus expenses) + wallet_balance (rebates)
     * minus any already-deposited-to-goals amounts.
     *
     * For simplicity and correctness, we track this as:
     *   wallet_balance column = running spendable balance updated on each transaction.
     */
    public function getAvailableBalance(int $userId): float
    {
        $user = DB::table('users')->where('id', $userId)->first();
        return (float) ($user->wallet_balance ?? 0);
    }

    /**
     * Add income to the wallet balance (called when user adds a non-goal income transaction).
     */
    public function creditWallet(int $userId, float $amount): void
    {
        DB::table('users')
            ->where('id', $userId)
            ->increment('wallet_balance', $amount);
    }

    /**
     * Deduct from wallet balance (called when user deposits to a goal or adds expense).
     * Returns false if insufficient balance.
     */
    public function debitWallet(int $userId, float $amount): bool
    {
        $balance = $this->getAvailableBalance($userId);
        if ($balance < $amount) {
            return false;
        }
        DB::table('users')
            ->where('id', $userId)
            ->decrement('wallet_balance', $amount);
        return true;
    }
}
