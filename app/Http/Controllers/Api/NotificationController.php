<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = $user->notifications();

        // Filter by read status if specified
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->unread();
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $notifications = $query->latest()->paginate($perPage);

        // Transform notifications for frontend
        $transformedNotifications = $notifications->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'subtitle' => $notification->data['subtitle'] ?? '',
                'icon' => $notification->data['icon'] ?? 'tabler-bell',
                'color' => $notification->data['color'] ?? 'primary',
                'time' => $notification->created_at->diffForHumans(),
                'isSeen' => ! is_null($notification->read_at),
                'url' => $notification->data['url'] ?? null,
                'created_at' => $notification->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $transformedNotifications,
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * Display the specified notification.
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        // Mark as read when viewed
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->data['title'] ?? 'Notification',
                'subtitle' => $notification->data['subtitle'] ?? '',
                'icon' => $notification->data['icon'] ?? 'tabler-bell',
                'color' => $notification->data['color'] ?? 'primary',
                'time' => $notification->created_at->diffForHumans(),
                'isSeen' => ! is_null($notification->read_at),
                'url' => $notification->data['url'] ?? null,
                'created_at' => $notification->created_at->toISOString(),
                'data' => $notification->data,
            ],
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->read_at,
            ],
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->read_at,
            ],
        ]);
    }

    /**
     * Mark multiple notifications as read.
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'string|exists:notifications,id',
        ]);

        $user = Auth::user();
        $notifications = $user->notifications()
            ->whereIn('id', $request->notification_ids)
            ->get();

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as read',
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Mark multiple notifications as unread.
     */
    public function markMultipleAsUnread(Request $request): JsonResponse
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'string|exists:notifications,id',
        ]);

        $user = Auth::user();
        $notifications = $user->notifications()
            ->whereIn('id', $request->notification_ids)
            ->get();

        foreach ($notifications as $notification) {
            $notification->markAsUnread();
        }

        return response()->json([
            'message' => 'Notifications marked as unread',
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'count' => $count,
        ]);
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }

    /**
     * Get notification statistics.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $user->notifications()->count(),
                'unread' => $user->unreadNotifications()->count(),
                'read' => $user->readNotifications()->count(),
            ],
        ]);
    }
}
