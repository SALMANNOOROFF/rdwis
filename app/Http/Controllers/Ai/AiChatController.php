<?php

namespace App\Http\Controllers\Ai;

use App\Exceptions\AiServiceUnavailableException;
use App\Http\Controllers\Controller;
use App\Services\Ai\AiToolCallingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AiChatController
 *
 * Exposes the synchronous AI chat endpoint for authorized users.
 * Connects directly to the AiToolCallingService which enforces strict user-scoping.
 */
class AiChatController extends Controller
{
    /**
     * Handle incoming chat requests from authenticated users.
     */
    public function chat(Request $request, AiToolCallingService $toolCallingService): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:2000',
        ]);

        $actingUser = $request->user();
        if (!$actingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        try {
            $result = $toolCallingService->processMessage(
                userMessage: $validated['message'],
                actingUser: $actingUser,
                history: $validated['history'] ?? []
            );

            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
                'tool_called' => $result['tool_called'],
                'status' => $result['status'],
            ], 200);
        } catch (AiServiceUnavailableException $e) {
            Log::warning('AI chat endpoint service unavailable', [
                'user_id' => $actingUser->getAuthIdentifier(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The AI assistant service is temporarily unavailable. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 503);
        }
    }
}
