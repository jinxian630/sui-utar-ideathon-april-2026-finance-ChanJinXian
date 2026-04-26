<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\SavingsEntry;
use App\Services\SavingsService;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct(
        private SavingsService $savingsService
    ) {}

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

        // READ: Get all transactions for the logged-in user
        $transactions = Transaction::where('user_id', $user->id)->latest('id')->get();

        $goalDeposits = SavingsEntry::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereNotNull('goal_id')
            ->sum(DB::raw('amount + COALESCE(round_up_amount, 0)'));

        // Transactions are wallet records only. Savings progress comes from goal deposits.
        $totalSaved = max(0, $goalDeposits);
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        $totalBalance = $this->savingsService->getAvailableBalance($user->id);

        // READ: Get all savings
        $savings = $user->savingsEntries()->latest()->get();
        $goals      = $user->goals()->where('is_active', true)->get();
        $goal       = 5000.00;
        $progress   = $goal > 0 ? min(100, ($totalSaved / $goal) * 100) : 0;

        $milestones = $this->milestones;
        $earnedSlugs = $user->badges()->pluck('slug')->toArray();

        $nextMilestone = null;
        $nextLevel = null;
        foreach ($milestones as $lvl => $milestone) {
            if ($totalSaved < $milestone['threshold']) {
                $nextMilestone = $milestone;
                $nextLevel = $lvl;
                break;
            }
        }
        if (!$nextMilestone) {
            $nextMilestone = end($milestones);
            $nextLevel = array_key_last($milestones);
        }

        $prevThreshold = $nextLevel > 1 ? $milestones[$nextLevel - 1]['threshold'] : 0;
        $nextThreshold = $nextMilestone['threshold'];
        $nextName = $nextMilestone['name'];
        $badgeProgress = $nextThreshold > 0
            ? min(100, round((($totalSaved - $prevThreshold) / ($nextThreshold - $prevThreshold)) * 100))
            : 100;
        $gap = max(0, $nextThreshold - $totalSaved);

        return view('dashboard', compact(
            'transactions', 'totalIncome', 'totalExpenses', 'totalBalance',
            'savings', 'totalSaved', 'goal', 'progress', 'goals',
            'milestones', 'earnedSlugs', 'nextMilestone', 'nextLevel',
            'nextName', 'badgeProgress', 'gap', 'nextThreshold', 'prevThreshold'
        ));
    }

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:income,expense',
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;

        if ($request->type === 'expense' && $this->savingsService->getAvailableBalance($user->id) < $amount) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount' => 'Insufficient wallet balance. You have RM ' .
                        number_format($this->savingsService->getAvailableBalance($user->id), 2) .
                        ' available.',
                ]);
        }

        DB::transaction(function () use ($request, $user, $amount) {
            Transaction::create([
                'user_id'     => $user->id,
                'savings_entry_id' => null,
                'description' => $request->description,
                'amount'      => $amount,
                'type'        => $request->type,
            ]);

            if ($request->type === 'income') {
                $this->savingsService->creditWallet($user->id, $amount);
            } else {
                $this->savingsService->debitWallet($user->id, $amount);
            }
        });

        return redirect()->back()
            ->with('success', 'Transaction added!');
    }

    public function destroy($id)
    {
        Transaction::where('user_id', auth()->id())->findOrFail($id);

        return back()->withErrors([
            'transaction' => 'Transactions are read-only after creation.',
        ]);
    }
}
