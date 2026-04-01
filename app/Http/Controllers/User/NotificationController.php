<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // List all notifications
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(15);

        return view('website.user.notifications', compact('notifications'));
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        // Redirect to the notification's target link if it exists and is not a placeholder
        $link = $notification->data['link'] ?? null;
        if ($link && $link !== '#' && !str_contains($link, 'messages')) {
            return redirect($link);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    // Clear all notifications
    public function clearAll()
    {
        auth()->user()->notifications()->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications cleared.');
    }

    // Delete notification
    public function destroy($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification deleted.');
    }
}
