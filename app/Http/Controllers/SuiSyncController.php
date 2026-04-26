<?php

namespace App\Http\Controllers;

use App\Models\SavingsEntry;
use App\Services\SuiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class SuiSyncController extends Controller
{
    public function __construct(private SuiService $suiService) {}

    public function storeProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'profile_object_id' => ['required', 'regex:/^0x[a-fA-F0-9]{64}$/'],
        ]);

        $request->user()->forceFill([
            'sui_finance_profile_id' => strtolower($validated['profile_object_id']),
        ])->save();

        return response()->json([
            'profile_object_id' => $request->user()->sui_finance_profile_id,
        ]);
    }

    public function markEntryOnChain(Request $request, SavingsEntry $entry): JsonResponse|RedirectResponse
    {
        abort_unless($entry->user_id === $request->user()->id, 403);

        if ($entry->synced_on_chain && $entry->sui_digest) {
            if ($request->expectsJson()) {
                abort(409, 'This entry is already synced on-chain.');
            }

            return back()->with('status', 'This entry is already synced on-chain.');
        }

        try {
            $result = $this->suiService->syncSavingsEntry($entry, $request->user());
        } catch (Throwable $exception) {
            report($exception);

            if (!$request->expectsJson()) {
                return back()->withErrors([
                    'sui_sync' => $exception->getMessage() ?: 'Unable to publish this record to Sui Testnet.',
                ]);
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'Unable to publish this record to Sui Testnet.',
            ], 422);
        }

        if (!$request->user()->sui_finance_profile_id) {
            $request->user()->forceFill([
                'sui_finance_profile_id' => $result['profile_object_id'],
            ])->save();
        }

        $entry->update([
            'synced_on_chain' => true,
            'sui_digest' => $result['digest'],
        ]);

        if (!$request->expectsJson()) {
            return back()->with([
                'status' => 'Published to Sui Testnet.',
                'sui_digest' => $entry->sui_digest,
                'suivision_url' => 'https://testnet.suivision.xyz/txblock/' . $entry->sui_digest,
            ]);
        }

        return response()->json([
            'synced_on_chain' => true,
            'sui_digest' => $entry->sui_digest,
            'profile_object_id' => $request->user()->fresh()->sui_finance_profile_id,
            'profile_digest' => $result['profile_digest'],
            'suivision_url' => 'https://testnet.suivision.xyz/txblock/' . $entry->sui_digest,
        ]);
    }
}
