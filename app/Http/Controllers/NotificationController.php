<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /** Tandai satu notifikasi sebagai dibaca (fetch dari dropdown). */
    public function markRead(DatabaseNotification $notification): JsonResponse|RedirectResponse
    {
        abort_unless($notification->notifiable_id === auth()->id() && $notification->notifiable_type === get_class(auth()->user()), 403);

        $notification->markAsRead();

        if (request()->expectsJson() || request()->isMethod('post')) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    /** Tandai semua dibaca. */
    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai dibaca.');
    }

    /**
     * Data polling notifikasi (dipanggil JS di header dashboard tiap 20 detik):
     * badge lonceng 🔔, daftar terbaru, toast untuk notifikasi baru,
     * plus jumlah chat masuk belum dibaca untuk admin.
     */
    public function poll(): JsonResponse
    {
        $user = auth()->user();

        $notifications = $user->notifications()->take(10)->get();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'latest_id' => $notifications->first()?->id,
            'items' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? 'Notifikasi',
                'url' => $n->data['url'] ?? '#',
                'read' => (bool) $n->read_at,
                'time' => $n->created_at->timezone('Asia/Jakarta')->diffForHumans(),
            ]),
            'chat_unread' => $user->isAdmin()
                ? (int) \App\Models\ChatConversation::where('status', 'open')->where('type', 'admin')->sum('admin_unread')
                : 0,
        ]);
    }
}
