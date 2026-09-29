<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case PendingConfirmation = 'pending_confirmation';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingConfirmation => 'Menunggu Konfirmasi',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Paid => 'Dibayar',
            self::InProgress => 'Berlangsung',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::PendingConfirmation => 'yellow',
            self::Approved => 'blue',
            self::Rejected => 'red',
            self::Paid => 'indigo',
            self::InProgress => 'cyan',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }

    /** Warna hex untuk grafik statistik. */
    public function hexColor(): string
    {
        return match ($this) {
            self::Draft => '#9ca3af',
            self::PendingConfirmation => '#f59e0b',
            self::Approved => '#3b82f6',
            self::Rejected => '#ef4444',
            self::Paid => '#6366f1',
            self::InProgress => '#06b6d4',
            self::Completed => '#10b981',
            self::Cancelled => '#f43f5e',
        };
    }

    /**
     * Transisi status yang diizinkan (State Engine - FR-TRX-04).
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::PendingConfirmation, self::Cancelled],
            self::PendingConfirmation => [self::Approved, self::Rejected, self::Cancelled],
            self::Approved => [self::Paid, self::Cancelled],
            self::Paid => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed],
            self::Rejected, self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
