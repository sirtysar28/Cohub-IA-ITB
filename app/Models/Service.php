<?php

namespace App\Models;

use App\Enums\PriceUnit;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'name', 'description',
        'starting_price', 'price_unit', 'status',
        'verification', 'rejection_note',
    ];

    protected function casts(): array
    {
        return [
            'starting_price' => 'float',
            'price_unit' => PriceUnit::class,
            'verification' => VerificationStatus::class,
        ];
    }

    /* ---------- Relasi ---------- */

    public function provider()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(ServiceMedia::class)->orderBy('sort_order');
    }

    public function availabilities()
    {
        return $this->hasMany(ServiceAvailability::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /* ---------- Atribut ---------- */

    public function getThumbnailAttribute(): ?string
    {
        $thumb = $this->media->firstWhere('is_thumbnail', true) ?? $this->media->first();
        return $thumb?->path;
    }

    public function scopePublished($query)
    {
        return $query->where('verification', VerificationStatus::Approved->value)
            ->where('status', 'available');
    }
}
