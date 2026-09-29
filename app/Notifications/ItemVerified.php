<?php

namespace App\Notifications;

use App\Enums\VerificationStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi hasil verifikasi properti/jasa oleh admin —
 * ke Pemilik Properti / Penyedia Jasa (database 🔔 + email SMTP).
 */
class ItemVerified extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,        // property | service
        public string $itemName,
        public VerificationStatus $status,
        public ?string $note = null,
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'item_name' => $this->itemName,
            'status' => $this->status->value,
            'message' => $this->message(),
            'url' => $this->url ?? route('home'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $approved = $this->status === VerificationStatus::Approved;

        return (new MailMessage)
            ->subject(($approved ? '✅ Disetujui' : '❌ Ditolak').' — Verifikasi '.$this->itemName)
            ->view('emails.generic', [
                'icon' => $this->type === 'property' ? '🏢' : '🛠',
                'accent' => $approved ? '#10b981' : '#ef4444',
                'pretitle' => 'Hasil Verifikasi',
                'title' => $approved
                    ? 'Selamat! '.$this->itemName.' telah disetujui & dipublikasikan 🎉'
                    : 'Maaf, '.$this->itemName.' belum lolos verifikasi',
                'intro' => array_values(array_filter([
                    $approved
                        ? 'Listing Anda telah diverifikasi oleh tim CoHub dan kini tampil publik di halaman '.($this->type === 'property' ? 'Properti' : 'Jasa').'. Pelanggan sudah dapat menemukan dan melakukan booking.'
                        : 'Listing Anda belum memenuhi kriteria kami. Silakan perbaiki data, lalu ajukan kembali verifikasi.',
                    $this->note ? 'Catatan dari admin: "'.$this->note.'"' : null,
                ])),
                'details' => [
                    'Jenis Listing' => $this->type === 'property' ? '🏢 Properti' : '🛠 Jasa',
                    'Nama' => $this->itemName,
                    'Status' => $this->status->label(),
                ],
                'ctaLabel' => $approved ? 'Lihat Listing Saya' : 'Perbaiki Listing',
                'ctaUrl' => $this->url ?? route('home'),
                'note' => $approved
                    ? 'Pantau permintaan booking masuk dari menu Order di dashboard Anda.'
                    : 'Pastikan deskripsi, foto (maks. 5 media), dan harga sudah sesuai ketentuan.',
            ]);
    }

    protected function message(): string
    {
        $approved = $this->status === VerificationStatus::Approved;

        return $approved
            ? "{$this->itemName} disetujui & dipublikasikan."
            : "{$this->itemName} ditolak".($this->note ? ' — '.$this->note : '');
    }
}
