<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // List Notifications
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()->paginate(10);

        // Format data properly for API
        $data = $notifications->getCollection()->transform(function ($notification) {
            return [
                'id'         => $notification->id,
                'type'       => $notification->data['type'] ?? 'notification',
                'title'      => $notification->data['title'] ?? 'Notification',
                'message'    => $notification->data['message'] ?? '',
                'data'       => $notification->data,
                'read_at'    => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
                'date'       => $notification->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
            'meta'   => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
                'unread_count' => auth()->user()->unreadNotifications()->count()
            ]
        ]);
    }

    // Mark Single as Read
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'status'  => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'status'  => true,
            'message' => 'Notification marked as read'
        ]);
    }

    // Mark All as Read
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status'  => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    // Delete Single Notification
    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'status'  => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Notification deleted'
        ]);
    }

    // Clear All Notifications
    public function clearAll()
    {
        auth()->user()->notifications()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'All notifications cleared'
        ]);
    }
}
