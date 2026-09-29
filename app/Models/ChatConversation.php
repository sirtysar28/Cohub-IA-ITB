<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatConversation extends Model
{
    protected $fillable = [
        'user_id', 'guest_name', 'guest_email', 'token',
        'type', 'status', 'admin_unread', 'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'admin_unread' => 'integer',
            'last_message_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $convo) {
            $convo->token ??= (string) Str::uuid();
        });
    }

    /* ---------- Relasi ---------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /* ---------- Atribut ---------- */

    /** Nama tampilan pengirim (user login atau guest). */
    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name
            ?? $this->guest_name
            ?? 'Tamu';
    }

    public function getLastMessageAttribute(): ?ChatMessage
    {
        return $this->messages()->latest('id')->first();
    }
}
