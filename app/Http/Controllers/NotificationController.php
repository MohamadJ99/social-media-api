<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notification = $request->user()->notifications()->latest()->paginate(20);

        return NotificationResource::collection($notification);
    }

    public function markAsRead(
        Request $request,
        $notification
    ) {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $notification->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()
            ->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
