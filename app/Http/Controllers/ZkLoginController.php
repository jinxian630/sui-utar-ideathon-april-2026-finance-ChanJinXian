<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ZkLoginController extends Controller
{
    /**
     * Authenticate or register a user via Sui zkLogin
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'wallet_address' => 'required|string',
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'zk_pin_verifier' => 'required|string|min:32|max:255',
        ]);

        $walletAddress = $request->wallet_address;
        $email = $request->email;
        $wasOnboarded = false;

        // Find existing user by wallet or email
        $user = User::where('wallet_address', $walletAddress)
                    ->orWhere('email', $email)
                    ->first();

        if (!$user) {
            // New user registration via zkLogin
            $user = User::create([
                'name' => $request->name ?: 'Web3 User ' . substr($walletAddress, 0, 6),
                'email' => $email,
                'password' => null, // zkLogin doesn't need password
                'wallet_address' => $walletAddress,
                'zk_pin_hash' => Hash::make($request->zk_pin_verifier),
                'kyc_status' => 'unverified',
                'role' => 'user'
            ]);
        } else {
            $wasOnboarded = (bool) $user->wallet_onboarded_at;

            if ($user->zk_pin_hash && !Hash::check($request->zk_pin_verifier, $user->zk_pin_hash)) {
                return response()->json([
                    'message' => 'Invalid Nuance PIN. Please use the PIN you created during zkLogin registration.',
                ], 422);
            }

            // Update wallet if matched by email but wallet is unset
            if (!$user->wallet_address) {
                $user->wallet_address = $walletAddress;
            }

            $updates = [
                'name' => $user->name ?: ($request->name ?: 'Web3 User ' . substr($walletAddress, 0, 6)),
            ];

            if (!$user->zk_pin_hash) {
                $updates['zk_pin_hash'] = Hash::make($request->zk_pin_verifier);
            }

            $user->forceFill($updates)->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Authenticated successfully',
            'redirect' => $wasOnboarded ? route('dashboard') : route('wallet.welcome')
        ]);
    }
}
