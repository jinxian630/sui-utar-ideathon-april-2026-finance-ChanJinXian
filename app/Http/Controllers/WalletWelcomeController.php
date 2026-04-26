<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletWelcomeController extends Controller
{
    public function show(Request $request): View
    {
        return view('wallet.welcome', [
            'walletAddress' => $request->user()->wallet_address ?? $request->user()->sui_address,
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        if (!$request->user()->wallet_onboarded_at) {
            $request->user()->forceFill([
                'wallet_onboarded_at' => now(),
            ])->save();
        }

        return redirect()->route('dashboard');
    }
}
