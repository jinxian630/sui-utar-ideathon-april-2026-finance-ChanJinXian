<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        // READ: Get all transactions for the logged-in user
        $transactions = Transaction::where('user_id', auth()->id())->latest('id')->get();

        // Calculate Summaries
        $totalIncome   = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        $totalBalance  = $totalIncome - $totalExpenses;

        return view('dashboard', compact('transactions', 'totalIncome', 'totalExpenses', 'totalBalance'));
    }

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:income,expense',
        ]);

        // CREATE: Save data to database
        Transaction::create([
            'user_id'     => auth()->id(),
            'description' => $request->description,
            'amount'      => $request->amount,
            'type'        => $request->type,
        ]);

        return redirect()->back()->with('success', 'Transaction added!');
    }

    public function destroy($id)
    {
        // DELETE: Remove record from database (scoped to authenticated user)
        $transaction = Transaction::where('user_id', auth()->id())->findOrFail($id);
        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction deleted!');
    }
}
