<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\NewChatMessage;
use App\Services\GroqChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Endpoint widget chat (pojok kanan bawah):
 * - mode "ai"    → dibalas otomatis oleh chatbot Groq (bahan: knowledge base workflow)
 * - mode "admin" → masuk ke konsol Admin → Chat Masuk, dibalas manusia
 */
class ChatController extends Controller
{
    /** Buka / lanjutkan percakapan. */
    public function open(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:ai,admin'],
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $type = $data['type'];

        if ($type === 'ai' && ! GroqChatService::aiEnabled()) {
            return response()->json(['ok' => false, 'message' => 'Asisten AI sedang tidak tersedia.'], 423);
        }
        if ($type === 'admin' && ! GroqChatService::adminChatEnabled()) {
            return response()->json(['ok' => false, 'message' => 'Chat admin sedang ditutup.'], 423);
        }

        $user = $request->user();

        // Lanjutkan percakapan terakhir yang masih terbuka (milik sendiri)
        $sessionKey = 'chat_convo_'.$type;
        $convo = null;

        if ($user) {
            $convo = ChatConversation::query()
                ->where('type', $type)->where('status', 'open')
                ->where('user_id', $user->id)
                ->latest('id')->first();
        } elseif ($request->session()->has($sessionKey)) {
            $convo = ChatConversation::query()
                ->where('type', $type)->where('status', 'open')
                ->whereNull('user_id')
                ->whereKey($request->session()->get($sessionKey))
                ->first();
        }

        $convo ??= ChatConversation::create([
            'user_id' => $user?->id,
            'guest_name' => $user ? null : ($data['name'] ?? 'Tamu'),
            'guest_email' => $user ? null : ($data['email'] ?? null),
            'type' => $type,
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $request->session()->put($sessionKey, $convo->id);

        return response()->json([
            'ok' => true,
            'conversation' => $this->conversationPayload($convo),
            'messages' => $this->messagePayload($convo->messages()->orderBy('id')->get()),
            'token' => $user ? null : $convo->token,
        ]);
    }

    /** Ambil pesan (polling widget). */
    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        abort_unless($this->authorizeConversation($request, $conversation), 403);

        $after = (int) $request->query('after', 0);

        $messages = $conversation->messages()
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->get();

        return response()->json([
            'ok' => true,
            'messages' => $this->messagePayload($messages),
            'status' => $conversation->status,
        ]);
    }

    /** Kirim pesan dari pengguna (dibalas bot bila mode AI). */
    public function send(Request $request, ChatConversation $conversation): JsonResponse
    {
        abort_unless($this->authorizeConversation($request, $conversation), 403);
        abort_unless($conversation->status === 'open', 422, 'Percakapan sudah ditutup.');

        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ], ['message.required' => 'Pesan tidak boleh kosong.']);

        $userMessage = $conversation->messages()->create([
            'sender' => 'user',
            'message' => trim($data['message']),
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
            'admin_unread' => $conversation->admin_unread + 1,
        ])->save();

        $botReply = null;

        if ($conversation->type === 'ai') {
            $botReply = $conversation->messages()->create([
                'sender' => 'bot',
                'message' => app(GroqChatService::class)->reply($conversation, $userMessage->message),
            ]);

            $conversation->forceFill([
                'admin_unread' => 0, // percakapan AI tidak memerlukan admin
                'last_message_at' => now(),
            ])->save();
        } else {
            // 🔔 Lonceng + email ke semua admin
            try {
                $admins = User::query()
                    ->whereIn('role', ['admin', 'super_admin'])
                    ->where('is_active', true)
                    ->get();

                NotificationFacade::send($admins, new NewChatMessage(
                    conversation: $conversation,
                    senderName: $conversation->display_name,
                    preview: mb_strimwidth($userMessage->message, 0, 80, '…'),
                    forAdmin: true,
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'ok' => true,
            'messages' => $this->messagePayload(collect([$userMessage, $botReply])->filter()),
        ]);
    }

    /* ---------- Helpers ---------- */

    protected function authorizeConversation(Request $request, ChatConversation $conversation): bool
    {
        if ($conversation->user_id) {
            return $request->user()?->id === $conversation->user_id;
        }

        // Guest: cocokkan token dari request dengan token percakapan
        $token = (string) $request->input('token', $request->query('token', ''));

        return $token !== '' && hash_equals($conversation->token, $token);
    }

    protected function messagePayload($messages): array
    {
        return $messages->map(fn (ChatMessage $m) => [
            'id' => $m->id,
            'sender' => $m->sender, // user | admin | bot
            'message' => $m->message,
            'time' => $m->created_at->timezone('Asia/Jakarta')->format('H:i'),
        ])->all();
    }

    protected function conversationPayload(ChatConversation $convo): array
    {
        return [
            'id' => $convo->id,
            'type' => $convo->type,
            'status' => $convo->status,
        ];
    }
}
