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
        if (\App\Models\SystemSetting::get('ai_assistant_enabled', '1') !== '1') {
            return response()->json([
                'success' => false,
                'message' => 'The AI Assistant (RIVA) is currently disabled by System Administrator.',
                'status' => 'disabled',
            ], 403);
        }

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

    /**
     * Toggle master AI Assistant status (Enable/Disable).
     * Strictly restricted to SO IT and Super Admin.
     */
    public function toggle(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $canManage = (method_exists($user, 'canManageAi') && $user->canManageAi())
            || ($user->acc_username === 'superadminrdw')
            || session('impersonated_by_god')
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'isSoit') && $user->isSoit())
            || strtolower(trim((string) ($user->acc_untarea ?? ''))) === 'it';

        if (!$canManage) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only SO IT and Super Admin can configure AI Assistant availability.',
            ], 403);
        }

        if ($request->has('enabled')) {
            $enabled = filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN);
        } else {
            $current = \App\Models\SystemSetting::get('ai_assistant_enabled', '1');
            $enabled = ($current !== '1');
        }

        \App\Models\SystemSetting::set(
            'ai_assistant_enabled',
            $enabled ? '1' : '0',
            'Master AI Assistant (RIVA) Enable/Disable Switch'
        );

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
            'message' => $enabled
                ? 'AI Assistant (RIVA) has been successfully ENABLED across RDWIS.'
                : 'AI Assistant (RIVA) has been successfully DISABLED across RDWIS.',
        ]);
    }

    /**
     * Check master AI Assistant status.
     */
    public function status(): JsonResponse
    {
        $enabled = \App\Models\SystemSetting::get('ai_assistant_enabled', '1') === '1';

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
        ]);
    }
}
