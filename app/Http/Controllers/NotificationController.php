<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $this->authorize('view-dashboard');

        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount(): JsonResponse
    {
        $this->authorize('view-dashboard');

        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead()
    {
        $this->authorize('view-dashboard');

        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function markRead(Request $request, string $id)
    {
        $this->authorize('view-dashboard');

        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = is_array($notification->data) ? $notification->data : json_decode((string) $notification->data, true);
        $url = $data['url'] ?? route('notifications.index');

        return redirect($url);
    }
}