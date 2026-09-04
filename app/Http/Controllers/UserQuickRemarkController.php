<?php

namespace App\Http\Controllers;

use App\Models\UserQuickRemark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserQuickRemarkController extends Controller
{
    /**
     * Get all quick remarks for authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $remarks = UserQuickRemark::forUser($user->acc_id)->get();
        return response()->json([
            'success' => true,
            'remarks' => $remarks,
            'count' => $remarks->count(),
            'max_allowed' => 7,
        ]);
    }

    /**
     * Store a new custom quick remark (Max 7 per user, max 6 words in label)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Limit Check: Max 7 remarks
        $currentCount = UserQuickRemark::where('uqr_acc_id', $user->acc_id)->count();
        if ($currentCount >= 7) {
            return response()->json([
                'success' => false,
                'message' => 'Limit reached: You can create a maximum of 7 custom shortcuts.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:120',
            'description' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $label = trim($request->input('label'));
        $words = UserQuickRemark::countWords($label);
        if ($words > 6) {
            return response()->json([
                'success' => false,
                'message' => "Label exceeds word limit: It has {$words} words, but maximum allowed is 6 words."
            ], 422);
        }

        $description = trim($request->input('description'));

        $remark = UserQuickRemark::create([
            'uqr_acc_id' => $user->acc_id,
            'uqr_label' => $label,
            'uqr_description' => $description,
            'uqr_order' => $currentCount + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shortcut added successfully.',
            'remark' => $remark,
            'total_count' => $currentCount + 1,
        ]);
    }

    /**
     * Update an existing quick remark
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $remark = UserQuickRemark::where('uqr_id', $id)
            ->where('uqr_acc_id', $user->acc_id)
            ->first();

        if (!$remark) {
            return response()->json([
                'success' => false,
                'message' => 'Shortcut not found or access denied.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:120',
            'description' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $label = trim($request->input('label'));
        $words = UserQuickRemark::countWords($label);
        if ($words > 6) {
            return response()->json([
                'success' => false,
                'message' => "Label exceeds word limit: It has {$words} words, but maximum allowed is 6 words."
            ], 422);
        }

        $remark->update([
            'uqr_label' => $label,
            'uqr_description' => trim($request->input('description')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shortcut updated successfully.',
            'remark' => $remark,
        ]);
    }

    /**
     * Delete a custom quick remark
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $remark = UserQuickRemark::where('uqr_id', $id)
            ->where('uqr_acc_id', $user->acc_id)
            ->first();

        if (!$remark) {
            return response()->json([
                'success' => false,
                'message' => 'Shortcut not found or access denied.'
            ], 404);
        }

        $remark->delete();

        $remainingCount = UserQuickRemark::where('uqr_acc_id', $user->acc_id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Shortcut deleted successfully.',
            'total_count' => $remainingCount,
        ]);
    }
}
