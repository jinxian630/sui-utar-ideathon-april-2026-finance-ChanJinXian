<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ZkLoginController extends Controller
{
    /**
     * Authenticate or register a user via Sui zkLogin
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'wallet_address' => 'required|string',
            'email' => 'required|email' // Typically extracted from Google JWT
        ]);

        $walletAddress = $request->wallet_address;
        $email = $request->email;

        // Find existing user by wallet or email
        $user = User::where('wallet_address', $walletAddress)
                    ->orWhere('email', $email)
                    ->first();

        if (!$user) {
            // New user registration via zkLogin
            $user = User::create([
                'name' => 'Web3 User ' . substr($walletAddress, 0, 6),
                'email' => $email,
                'password' => null, // zkLogin doesn't need password
                'wallet_address' => $walletAddress,
                'kyc_status' => 'unverified',
                'role' => 'user'
            ]);
        } else {
            // Update wallet if matched by email but wallet is unset
            if (!$user->wallet_address) {
                $user->wallet_address = $walletAddress;
                $user->save();
            }
        }

        Auth::login($user);

        return response()->json([
            'message' => 'Authenticated successfully',
            'redirect' => route('dashboard')
        ]);
    }
}
