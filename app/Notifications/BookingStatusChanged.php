<?php

namespace App\Notifications;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi perubahan status booking — dikirim ke customer, owner/provider,
 * dan admin. Kanal: database (ikon 🔔 di header) + email (SMTP).
 */
class BookingStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public BookingStatus $from,
        public BookingStatus $to,
        public ?User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->code,
            'item_name' => $this->booking->item_name,
            'from' => $this->from->value,
            'to' => $this->to->value,
            'message' => $this->message(),
            'url' => route('bookings.redirect', $this->booking),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->booking->code}] " . $this->message())
            ->view('emails.booking-status', [
                'recipient' => $notifiable,
                'booking' => $this->booking,
                'from' => $this->from,
                'to' => $this->to,
                'actor' => $this->actor,
                'headline' => $this->messageFor($notifiable),
                'tip' => $this->tipFor($notifiable),
                'url' => route('bookings.redirect', $this->booking),
            ]);
    }

    protected function message(): string
    {
        $item = $this->booking->item_name;

        return match ($this->to) {
            BookingStatus::PendingConfirmation => "Booking {$item} menunggu konfirmasi.",
            BookingStatus::Approved => "Booking {$item} telah disetujui. Silakan lakukan pembayaran.",
            BookingStatus::Rejected => "Booking {$item} ditolak.",
            BookingStatus::Paid => "Pembayaran booking {$item} berhasil dikonfirmasi.",
            BookingStatus::InProgress => "Booking {$item} sedang berlangsung.",
            BookingStatus::Completed => "Booking {$item} telah selesai.",
            BookingStatus::Cancelled => "Booking {$item} dibatalkan.",
            default => "Status booking {$item} diperbarui.",
        };
    }

    /** Pesan email yang menyesuaikan peran penerima. */
    protected function messageFor(object $notifiable): string
    {
        $item = $this->booking->item_name;

        if ($notifiable instanceof User && $notifiable->isAdmin()) {
            return match ($this->to) {
                BookingStatus::PendingConfirmation => "Booking {$item} ({$this->booking->code}) baru saja diajukan dan menunggu konfirmasi mitra.",
                BookingStatus::Paid => "Pembayaran booking {$item} ({$this->booking->code}) telah dikonfirmasi.",
                default => "Transaksi {$this->booking->code} ({$item}) kini berstatus {$this->to->label()}.",
            };
        }

        if ($notifiable instanceof User && $notifiable->is($this->booking->partner)) {
            return match ($this->to) {
                BookingStatus::PendingConfirmation => "Ada request booking baru untuk {$item}. Segera lakukan review — setujui atau tolak dari dashboard Anda.",
                BookingStatus::Paid => "Customer telah membayaran booking {$item}. Booking siap dijalankan.",
                BookingStatus::Cancelled => "Booking {$item} dibatalkan.",
                default => $this->message(),
            };
        }

        return $this->message();
    }

    /** Tips kontekstual di email. */
    protected function tipFor(object $notifiable): string
    {
        if ($notifiable instanceof User && $notifiable->is($this->booking->partner)) {
            return match ($this->to) {
                BookingStatus::PendingConfirmation => 'Buka dashboard Anda → menu Order → pilih Approve atau Reject beserta catatan.',
                default => '',
            };
        }

        return match ($this->to) {
            BookingStatus::Approved => 'Booking Anda disetujui! Lakukan pembayaran segera agar jadwal terkunci.',
            BookingStatus::Paid => 'Pembayaran diterima. Mitra akan memulai layanan sesuai jadwal.',
            BookingStatus::Completed => 'Terima kasih telah menggunakan CoHub! Berikan ulasan Anda.',
            default => '',
        };
    }
}
