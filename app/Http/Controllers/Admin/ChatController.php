<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Notifications\NewChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Konsol chat admin — membalas percakapan masuk dari widget chat. */
class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = ChatConversation::query()
            ->with('user')
            ->withCount('messages')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w
                    ->where('guest_name', 'like', $term)
                    ->orWhere('guest_email', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)));
            })
            ->latest('last_message_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.chats.index', compact('conversations'));
    }

    public function show(ChatConversation $conversation): View
    {
        $conversation->load(['messages.senderUser', 'user']);
        $conversation->forceFill(['admin_unread' => 0])->save();

        return view('admin.chats.show', ['conversation' => $conversation]);
    }

    public function reply(Request $request, ChatConversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ], ['message.required' => 'Balasan tidak boleh kosong.']);

        $conversation->messages()->create([
            'sender' => 'admin',
            'sender_id' => auth()->id(),
            'message' => trim($data['message']),
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
            'admin_unread' => 0,
        ])->save();

        // 🔔 Lonceng notifikasi ke pengguna (jika login)
        try {
            if ($conversation->user) {
                $conversation->user->notify(new NewChatMessage(
                    conversation: $conversation,
                    senderName: auth()->user()->name,
                    preview: mb_strimwidth($data['message'], 0, 80, '…'),
                    forAdmin: false,
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Balasan terkirim.');
    }

    public function toggle(Request $request, ChatConversation $conversation): RedirectResponse
    {
        $conversation->update([
            'status' => $conversation->status === 'open' ? 'closed' : 'open',
        ]);

        return back()->with('success', 'Percakapan ditandai '.$conversation->status.'.');
    }

    /** Pesan baru sejak ID tertentu (polling halaman detail). */
    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $after = (int) $request->query('after', 0);

        $messages = $conversation->messages()
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'sender' => $m->sender,
                'message' => $m->message,
                'time' => $m->created_at->timezone('Asia/Jakarta')->format('H:i'),
            ]);

        return response()->json(['ok' => true, 'messages' => $messages]);
    }
}
