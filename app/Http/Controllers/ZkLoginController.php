<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ZkLoginController extends Controller
{
    /**
     * Check whether a Google account already has a Nuance PIN before login asks for one.
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !$user->zk_pin_hash) {
            return response()->json([
                'has_pin' => false,
                'message' => 'No Nuance PIN found for this Google account. Please register with Google zkLogin to create your PIN.',
                'redirect' => route('register'),
            ], 404);
        }

        return response()->json([
            'has_pin' => true,
        ]);
    }

    /**
     * Authenticate or register a user via Sui zkLogin
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'mode' => 'nullable|in:login,register',
            'wallet_address' => 'required|string',
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'zk_subject' => 'nullable|string|max:255',
            'zk_pin_verifier' => 'required|string|min:32|max:255',
        ]);

        $walletAddress = $request->wallet_address;
        $email = $request->email;
        $mode = $request->input('mode', 'register');
        $wasOnboarded = false;

        // Find existing user by wallet or email
        $user = User::where('wallet_address', $walletAddress)
                    ->orWhere('email', $email)
                    ->first();

        if (!$user) {
            if ($mode === 'login') {
                return response()->json([
                    'message' => 'No Nuance PIN found for this Google account. Please register with Google zkLogin to create your PIN.',
                    'redirect' => route('register'),
                ], 409);
            }

            // New user registration via zkLogin
            $user = User::create([
                'name' => $request->name ?: 'Web3 User ' . substr($walletAddress, 0, 6),
                'email' => $email,
                'wallet_address' => $walletAddress,
                'zk_pin_hash' => Hash::make($request->zk_pin_verifier),
                'zk_subject' => $request->zk_subject,
                'kyc_status' => 'unverified',
                'role' => 'user'
            ]);
        } else {
            $wasOnboarded = (bool) $user->wallet_onboarded_at;

            if (!$user->zk_pin_hash && $mode === 'login') {
                return response()->json([
                    'message' => 'No Nuance PIN found for this Google account. Please register with Google zkLogin to create your PIN.',
                    'redirect' => route('register'),
                ], 409);
            }

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
                'zk_subject' => $user->zk_subject ?: $request->zk_subject,
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
