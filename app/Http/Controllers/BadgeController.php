<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('user_id', auth()->id())->latest('id')->get();

        $totalIncome   = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        $totalBalance  = $totalIncome - $totalExpenses;
        $totalSaved    = $totalIncome;

        $milestones = [
            1 => ['threshold' => 100,   'name' => 'Saver Lv.1',    'icon' => '🥉', 'color' => 'from-amber-700 to-amber-500'],
            2 => ['threshold' => 500,   'name' => 'Investor',       'icon' => '🥈', 'color' => 'from-slate-500 to-slate-300'],
            3 => ['threshold' => 1000,  'name' => 'Wealth Builder', 'icon' => '🥇', 'color' => 'from-yellow-600 to-yellow-400'],
            4 => ['threshold' => 5000,  'name' => 'Diamond Saver',  'icon' => '💎', 'color' => 'from-cyan-600 to-cyan-400'],
            5 => ['threshold' => 10000, 'name' => 'Finance Master', 'icon' => '👑', 'color' => 'from-purple-600 to-violet-400'],
        ];

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
            'nextMilestone', 'nextLevel', 'nextName', 'pct', 'gap', 'nextThreshold', 'prevThreshold'
        ));
    }
}
