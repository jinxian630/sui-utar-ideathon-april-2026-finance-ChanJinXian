<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private AiChatService $aiChatService) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        if (blank($user->wallet_address) && blank($user->sui_address)) {
            return response()->json([
                'success' => false,
                'message' => 'Connect your Sui wallet before using AI financial planning.',
            ], 403);
        }

        $reply = $this->aiChatService->chat($user, $validated['message']);

        return response()->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }
}
