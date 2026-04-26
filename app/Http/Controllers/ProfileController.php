<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isPasswordlessWalletAccount = empty($user->password)
            && !empty($user->wallet_address ?? $user->sui_address);

        if ($isPasswordlessWalletAccount) {
            $request->validateWithBag('userDeletion', [
                'zk_pin' => ['required', 'digits:6'],
            ]);

            if (!$user->zk_subject || !$user->zk_pin_hash) {
                throw ValidationException::withMessages([
                    'zk_pin' => 'Please sign out and sign in again with Google zkLogin before deleting this account.',
                ])->errorBag('userDeletion');
            }

            $pinVerifier = hash('sha256', $user->zk_subject . '|' . $user->email . '|' . $request->zk_pin);

            if (!Hash::check($pinVerifier, $user->zk_pin_hash)) {
                throw ValidationException::withMessages([
                    'zk_pin' => 'The Nuance PIN is incorrect.',
                ])->errorBag('userDeletion');
            }
        } else {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
        }

        Auth::logout();

        $user->forceFill([
            'email' => 'deleted-user-' . $user->id . '-' . now()->timestamp . '@deleted.local',
            'password' => null,
            'wallet_address' => null,
            'sui_address' => null,
            'zk_pin_hash' => null,
            'zk_subject' => null,
            'remember_token' => null,
        ])->save();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
