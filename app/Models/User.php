<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'whatsapp',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /* ---------- Helpers Role ---------- */

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool { return $this->role === UserRole::SuperAdmin; }
    public function isAdmin(): bool  { return $this->role === UserRole::Admin || $this->role === UserRole::SuperAdmin; }
    public function isOwner(): bool  { return $this->role === UserRole::Owner; }
    public function isProvider(): bool { return $this->role === UserRole::Provider; }
    public function isCustomer(): bool { return $this->role === UserRole::Customer; }

    /* ---------- Relasi ---------- */

    public function customerProfile()
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }
}
