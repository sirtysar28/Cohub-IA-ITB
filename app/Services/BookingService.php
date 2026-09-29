<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use InvalidArgumentException;

/**
 * Booking State Engine (bagian 17 workflow):
 * validateTransition() -> changeStatus() -> history + notification.
 */
class BookingService
{
    /**
     * Ubah status booking dengan validasi transisi state machine.
     */
    public function changeStatus(Booking $booking, BookingStatus $next, ?User $actor = null, ?string $note = null): Booking
    {
        $current = $booking->status;

        if (! $current->canTransitionTo($next)) {
            throw new InvalidArgumentException(
                "Transisi status dari {$current->value} ke {$next->value} tidak diizinkan."
            );
        }

        DB::transaction(function () use ($booking, $current, $next, $actor, $note) {
            $now = now();

            $booking->forceFill([
                'status' => $next->value,
                'submitted_at' => $booking->submitted_at ?? ($next === BookingStatus::PendingConfirmation ? $now : $booking->submitted_at),
                'approved_at' => $next === BookingStatus::Approved ? $now : $booking->approved_at,
                'completed_at' => $next === BookingStatus::Completed ? $now : $booking->completed_at,
            ])->save();

            $booking->histories()->create([
                'from_status' => $current->value,
                'to_status' => $next->value,
                'actor_id' => $actor?->id,
                'note' => $note,
            ]);

            // Notifikasi ke pihak terkait
            $this->notifyParties($booking, $current, $next, $actor);

            // History log (audit trail) — aksi dipetakan dari transisi status
            try {
                $action = match ($next) {
                    BookingStatus::PendingConfirmation => 'submit',
                    BookingStatus::Approved => 'approve',
                    BookingStatus::Rejected => 'reject',
                    BookingStatus::Paid => 'pay',
                    BookingStatus::Completed => 'complete',
                    BookingStatus::Cancelled => 'cancel',
                    default => 'update',
                };

                ActivityLog::record(
                    $action,
                    'transaksi',
                    "Booking {$booking->code}: {$current->label()} → {$next->label()}".($actor ? " oleh {$actor->name}" : ''),
                    $booking,
                    $actor,
                );
            } catch (\Throwable $e) {
                report($e); // logging tidak boleh menggagalkan transaksi
            }
        });

        return $booking->fresh();
    }

    /** Hitung total harga sewa properti berdasarkan periode. */
    public static function calculatePropertyTotal(float $pricePerUnit, string $period, int $days): float
    {
        $units = match ($period) {
            'daily' => $days,
            'monthly' => max(1, (int) ceil($days / 30)),
            'yearly' => max(1, (int) ceil($days / 365)),
            default => 1,
        };

        return $pricePerUnit * $units;
    }

    /** Hitung total harga jasa (quantity x harga satuan). */
    public static function calculateServiceTotal(float $startingPrice, int $quantity = 1): float
    {
        return $startingPrice * max(1, $quantity);
    }

    protected function notifyParties(Booking $booking, BookingStatus $from, BookingStatus $to, ?User $actor): void
    {
        try {
            // Beri tahu customer untuk semua perubahan (kecuali aksinya sendiri)
            if (! $actor?->is($booking->customer)) {
                $booking->customer->notify(new BookingStatusChanged($booking, $from, $to, $actor));
            }

            // Beri tahu partner (owner/provider) saat submit booking baru
            $partner = $booking->partner;
            if ($partner && ! $actor?->is($partner)) {
                $partner->notify(new BookingStatusChanged($booking, $from, $to, $actor));
            }

            // Beri tahu semua admin (request/transaksi masuk + monitoring)
            // — kecuali aktornya sendiri adalah admin
            if (! $actor?->isAdmin()) {
                User::query()
                    ->whereIn('role', [UserRole::Admin->value, UserRole::SuperAdmin->value])
                    ->where('is_active', true)
                    ->get()
                    ->each(fn (User $admin) => $admin->notify(new BookingStatusChanged($booking, $from, $to, $actor)));
            }
        } catch (\Throwable $e) {
            report($e); // notifikasi tidak boleh menggagalkan transaksi
        }
    }
}
