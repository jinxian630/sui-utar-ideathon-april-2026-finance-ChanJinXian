<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\SavingsEntry;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BadgeController extends Controller
{
    public function __construct(private BadgeService $badgeService) {}

    // Milestone definitions — must match BadgeService exactly
    private array $milestones = [
        1 => ['slug' => 'saver',          'threshold' => 100,   'name' => 'Saver Lv.1',    'icon' => '🥉', 'color' => 'from-amber-700 to-amber-500'],
        2 => ['slug' => 'investor',       'threshold' => 500,   'name' => 'Investor',       'icon' => '🥈', 'color' => 'from-slate-500 to-slate-300'],
        3 => ['slug' => 'wealth_builder', 'threshold' => 1000,  'name' => 'Wealth Builder', 'icon' => '🥇', 'color' => 'from-yellow-600 to-yellow-400'],
        4 => ['slug' => 'diamond_saver',  'threshold' => 5000,  'name' => 'Diamond Saver',  'icon' => '💎', 'color' => 'from-cyan-600 to-cyan-400'],
        5 => ['slug' => 'finance_master', 'threshold' => 10000, 'name' => 'Finance Master', 'icon' => '👑', 'color' => 'from-purple-600 to-violet-400'],
    ];

    public function index()
    {
        $user = auth()->user();

        // ── 1. Calculate TRUE total savings ─────────────────────────────────
        // Combine: income transactions (non-goal) + all goal deposits (savings_entries)
        $goalDeposits = SavingsEntry::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereNotNull('goal_id')
            ->sum(DB::raw('amount + COALESCE(round_up_amount, 0)'));

        $totalSaved    = max(0, $goalDeposits);
        $totalIncome   = Transaction::where('user_id', $user->id)->where('type', 'income')->sum('amount');
        $totalExpenses = Transaction::where('user_id', $user->id)->where('type', 'expense')->sum('amount');
        $totalBalance  = (float) ($user->wallet_balance ?? 0);

        // ── 2. Auto-check & mint any newly earned badges ─────────────────────
        // This catches users who earned thresholds via income transactions
        // or any path other than a direct goal deposit.
        $this->badgeService->checkMilestonesAndAwardRebate($user->fresh());

        // ── 3. Load DB-earned badges (after potential new minting above) ───────
        $earnedBadges = $user->badges()->get()->keyBy('slug');
        $earnedSlugs = $earnedBadges->keys()->toArray();

        $milestones = $this->milestones;

        // ── 3. Next milestone logic ────────────────────────────────────────────
        $nextMilestone = null;
        $nextLevel     = null;
        foreach ($milestones as $lvl => $m) {
            if ($totalSaved < $m['threshold']) {
                $nextMilestone = $m;
                $nextLevel     = $lvl;
                break;
            }
        }
        if (!$nextMilestone) {
            $nextMilestone = end($milestones);
            $nextLevel     = array_key_last($milestones);
        }

        $prevThreshold = $nextLevel > 1 ? $milestones[$nextLevel - 1]['threshold'] : 0;
        $nextThreshold = $nextMilestone['threshold'];
        $nextName      = $nextMilestone['name'];
        $pct           = $nextThreshold > 0
            ? min(100, round((($totalSaved - $prevThreshold) / ($nextThreshold - $prevThreshold)) * 100))
            : 100;
        $gap = max(0, $nextThreshold - $totalSaved);

        return view('badges', compact(
            'milestones', 'totalSaved', 'totalIncome', 'totalExpenses', 'totalBalance',
            'nextMilestone', 'nextLevel', 'nextName', 'pct', 'gap', 'nextThreshold', 'prevThreshold',
            'earnedSlugs', 'earnedBadges'
        ));
    }
}
