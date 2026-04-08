<?php

namespace App\Http\Controllers;

use App\Models\PurNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the header bell
     */
    public function unread()
    {
        $user = Auth::user();
        if (!$user) return response()->json(['count' => 0, 'notifications' => []]);

        $notifs = PurNotification::where('pnt_acc_id', $user->acc_id)
            ->where('pnt_is_read', false)
            ->latest('created_at')
            ->take(5)
            ->get();

        return response()->json([
            'count' => PurNotification::where('pnt_acc_id', $user->acc_id)->where('pnt_is_read', false)->count(),
            'notifications' => $notifs
        ]);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllRead()
    {
        $user = Auth::user();
        if ($user) {
            PurNotification::where('pnt_acc_id', $user->acc_id)
                ->where('pnt_is_read', false)
                ->update(['pnt_is_read' => true]);
        }

        return response()->json(['status' => 'success']);
    }
}
