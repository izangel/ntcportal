<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification; // Important: Import this class

class NotificationController extends Controller
{
    /**
     * Mark a specific notification as read.
     *
     * @param  \Illuminate\Notifications\DatabaseNotification  $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        // Ensure the logged-in user is the owner of this notification for security
        if (Auth::id() !== $notification->notifiable_id) {
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsRead(); // Mark the notification as read

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications for the authenticated user as read.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
      
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}