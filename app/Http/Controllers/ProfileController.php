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
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's Nuance PIN.
     */
    public function updatePin(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePin', [
            'current_pin' => ['required', 'digits:6'],
            'new_pin' => ['required', 'digits:6', 'confirmed'],
        ]);

        $user = $request->user();

        $this->assertValidPin($user, $validated['current_pin'], 'current_pin', 'updatePin');

        $newPinVerifier = $this->pinVerifier($user, $validated['new_pin']);

        $user->forceFill([
            'zk_pin_hash' => Hash::make($newPinVerifier),
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'pin-updated');
    }

    /**
     * Verify the user's Nuance PIN before showing destructive UI.
     */
    public function verifyPin(Request $request)
    {
        $validated = $request->validate([
            'zk_pin' => ['required', 'digits:6'],
        ]);

        $this->assertValidPin($request->user(), $validated['zk_pin'], 'zk_pin');

        return response()->json([
            'verified' => true,
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validateWithBag('userDeletion', [
            'zk_pin' => ['required', 'digits:6'],
        ]);

        $this->assertValidPin($user, $request->zk_pin, 'zk_pin', 'userDeletion');

        Auth::logout();

        $user->forceFill([
            'email' => 'deleted-user-' . $user->id . '-' . now()->timestamp . '@deleted.local',
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

    private function assertValidPin($user, string $pin, string $field, ?string $errorBag = null): void
    {
        if (!$user->zk_subject || !$user->zk_pin_hash) {
            $exception = ValidationException::withMessages([
                $field => 'Please sign out and sign in again with Google zkLogin before continuing.',
            ]);

            if ($errorBag) {
                $exception->errorBag($errorBag);
            }

            throw $exception;
        }

        if (!Hash::check($this->pinVerifier($user, $pin), $user->zk_pin_hash)) {
            $exception = ValidationException::withMessages([
                $field => 'The Nuance PIN is incorrect.',
            ]);

            if ($errorBag) {
                $exception->errorBag($errorBag);
            }

            throw $exception;
        }
    }

    private function pinVerifier($user, string $pin): string
    {
        return hash('sha256', $user->zk_subject . '|' . $user->email . '|' . $pin);
    }
}
