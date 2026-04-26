<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForecastController extends Controller
{
    protected $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    public function getForecast(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $savingsData = $user->savingsEntries()
            ->latest()
            ->take(50)
            ->get()
            ->toArray();
        
        $forecast = $this->aiChatService->generateForecast($savingsData);

        return response()->json([
            'success' => true,
            'forecast' => $forecast
        ]);
    }
}
