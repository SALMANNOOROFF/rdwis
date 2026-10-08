<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCaseDraftRemark;

class UserCaseDraftRemarkController extends Controller
{
    /**
     * Save/update draft remarks privately for authenticated user.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'case_type' => 'required|string|in:purchase,contract',
            'case_id'   => 'required|integer',
            'remarks'   => 'nullable|string',
        ]);

        $accId = Auth::user()?->acc_id ?? Auth::id();
        if (!$accId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $draft = UserCaseDraftRemark::saveDraft(
            (int) $accId,
            $validated['case_type'],
            (int) $validated['case_id'],
            $validated['remarks'] ?? ''
        );

        return response()->json([
            'status'     => 'success',
            'message'    => 'Draft remarks saved privately.',
            'saved_at'   => $draft->updated_at ? $draft->updated_at->format('h:i A') : now()->format('h:i A'),
            'draft_text' => $draft->draft_remarks,
        ]);
    }

    /**
     * Clear draft remarks for authenticated user.
     */
    public function clear(Request $request)
    {
        $validated = $request->validate([
            'case_type' => 'required|string|in:purchase,contract',
            'case_id'   => 'required|integer',
        ]);

        $accId = Auth::user()?->acc_id ?? Auth::id();
        if (!$accId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        UserCaseDraftRemark::clearDraft(
            (int) $accId,
            $validated['case_type'],
            (int) $validated['case_id']
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Draft remarks cleared.',
        ]);
    }

    /**
     * Get current draft remarks for authenticated user.
     */
    public function get(Request $request)
    {
        $validated = $request->validate([
            'case_type' => 'required|string|in:purchase,contract',
            'case_id'   => 'required|integer',
        ]);

        $accId = Auth::user()?->acc_id ?? Auth::id();
        if (!$accId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $draft = UserCaseDraftRemark::where('acc_id', (int) $accId)
            ->where('case_type', $validated['case_type'])
            ->where('case_id', (int) $validated['case_id'])
            ->first();

        return response()->json([
            'status'     => 'success',
            'has_draft'  => !empty(trim($draft?->draft_remarks ?? '')),
            'remarks'    => $draft?->draft_remarks ?? '',
            'saved_at'   => $draft && $draft->updated_at ? $draft->updated_at->format('h:i A') : null,
        ]);
    }
}
