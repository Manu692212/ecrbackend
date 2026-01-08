<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\Admin')
            ->where('notifiable_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = DB::table('notifications')
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\Admin')
            ->where('notifiable_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function unreadCount(Request $request)
    {
        $count = DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\Admin')
            ->where('notifiable_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|in:info,warning,error,success',
            'recipient_id' => 'nullable|exists:admins,id'
        ]);

        $notification = DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\SystemNotification',
            'notifiable_type' => 'App\\Models\\Admin',
            'notifiable_id' => $request->recipient_id ?? $request->user()->id,
            'data' => json_encode([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type ?? 'info'
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Notification created successfully']);
    }
}
