<?php

namespace App\Notifications;

use App\Models\ChatConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi pesan chat baru:
 * - ke ADMIN  : ada pesan masuk dari pengguna (database 🔔 + email)
 * - ke PENGGUNA: admin membalas percakapan (database 🔔)
 */
class NewChatMessage extends Notification
{
    use Queueable;

    public function __construct(
        public ChatConversation $conversation,
        public string $senderName,
        public string $preview,
        public bool $forAdmin,
    ) {}

    public function via(object $notifiable): array
    {
        // Email hanya untuk admin (agar tidak spam ke pengguna); pengguna melihat
        // balasan langsung di widget chat / lonceng notifikasi.
        return $this->forAdmin ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'message' => $this->forAdmin
                ? "💬 Pesan chat baru dari {$this->senderName}: \"{$this->preview}\""
                : "💬 Admin telah membalas chat Anda: \"{$this->preview}\"",
            'url' => $this->forAdmin
                ? route('admin.chats.show', $this->conversation)
                : route('home').'#chat',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('💬 Pesan chat baru dari '.$this->senderName)
            ->view('emails.generic', [
                'icon' => '💬',
                'accent' => '#4f46e5',
                'pretitle' => 'Chat Masuk',
                'title' => 'Pesan baru dari '.$this->senderName,
                'intro' => [
                    '"'.str($this->preview)->limit(180).'"',
                    'Segera balas percakapan ini agar pengguna tidak menunggu terlalu lama.',
                ],
                'details' => [
                    'Pengirim' => $this->senderName,
                    'Email' => $this->conversation->guest_email ?: $this->conversation->user?->email ?? '-',
                    'Status' => ucfirst($this->conversation->status),
                ],
                'ctaLabel' => 'Balas Chat Sekarang',
                'ctaUrl' => route('admin.chats.show', $this->conversation),
            ]);
    }
}
