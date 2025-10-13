<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id());
        
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($notifications);
    }

    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
                            ->where('is_read', false)
                            ->count();
        
        return response()->json(['count' => $count]);
    }

    public function markAsRead(Notification $notification)
    {
        // Check if user is authorized to mark this notification as read
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        
        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->update([
                        'is_read' => true,
                        'read_at' => now()
                    ]);
        
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy(Notification $notification)
    {
        // Check if user is authorized to delete this notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();
        
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}