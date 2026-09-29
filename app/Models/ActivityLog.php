<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * History log / audit trail aktivitas pengguna di seluruh aplikasi.
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'module', 'description',
        'subject_type', 'subject_id', 'ip_address', 'user_agent',
    ];

    /* ---------- Aksi yang dikenal (untuk filter & badge) ---------- */

    public const ACTIONS = [
        'login', 'logout', 'register',
        'create', 'update', 'delete',
        'verify', 'approve', 'reject',
        'submit', 'pay', 'cancel', 'complete',
        'role', 'status', 'settings',
    ];

    public const MODULES = [
        'auth', 'properti', 'jasa', 'transaksi', 'pengguna', 'pengaturan',
    ];

    /* ---------- Rekam aktivitas (helper utama) ---------- */

    public static function record(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        ?User $user = null,
    ): self {
        return static::create([
            'user_id' => ($user ?? auth()->user())?->id,
            'action' => $action,
            'module' => $module,
            'description' => mb_substr($description, 0, 500),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()?->ip(),
            'user_agent' => mb_substr((string) request()?->userAgent(), 0, 500) ?: null,
        ]);
    }

    /* ---------- Relasi ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /* ---------- Presentasi ---------- */

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'login' => '🔐',
            'logout' => '🚪',
            'register' => '✍️',
            'create' => '➕',
            'update' => '✏️',
            'delete' => '🗑️',
            'verify' => '✅',
            'approve' => '👍',
            'reject' => '❌',
            'submit' => '📤',
            'pay' => '💰',
            'cancel' => '🚫',
            'complete' => '🏁',
            'role' => '🎭',
            'status' => '🔁',
            'settings' => '⚙️',
            default => '📌',
        };
    }
}
