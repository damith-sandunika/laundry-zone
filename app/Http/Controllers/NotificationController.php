<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function check()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['count' => 0, 'notifications' => []]);
        }

        $notifications = $user->unreadNotifications()->get()->map(function ($notif) {
            return [
                'id' => $notif->id,
                'message' => $notif->data['message'] ?? 'New Notification',
                'url' => $notif->data['url'] ?? '#',
                'type' => $notif->data['type'] ?? 'general',
                'time' => $notif->created_at->diffForHumans()
            ];
        });

        // Group notifications by type for Alpine
        $grouped = $notifications->groupBy('type');

        return response()->json([
            'count' => $notifications->count(),
            'grouped' => $grouped
        ]);
    }

    public function markAsRead(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            if ($request->has('type')) {
                // Mark only a specific type as read
                $user->unreadNotifications()
                     ->get()
                     ->filter(function($n) use ($request) {
                         return isset($n->data['type']) && $n->data['type'] === $request->type;
                     })
                     ->markAsRead();
            } else {
                $user->unreadNotifications->markAsRead();
            }
        }
        return response()->json(['success' => true]);
    }
}
