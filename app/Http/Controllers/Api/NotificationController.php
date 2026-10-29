<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->take(100)->get();
        $unread = $user->unreadNotifications()->latest()->take(8)->get();

        return response()->json([
            'data' => [
                'unread_count' => $user->unreadNotifications()->count(),
                'unread' => $unread->map(fn ($notification) => ApiData::notification($notification))->values(),
                'notifications' => $notifications->map(fn ($notification) => ApiData::notification($notification))->values(),
            ],
        ]);
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification introuvable.',
            ], 404);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marquee comme lue.',
            'data' => ApiData::notification($notification->fresh()),
        ]);
    }
}