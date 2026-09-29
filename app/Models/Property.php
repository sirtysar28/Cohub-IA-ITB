<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'name', 'description',
        'land_size', 'building_size',
        'provinsi', 'kota_kabupaten', 'kecamatan', 'address',
        'status', 'verification', 'rejection_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => PropertyStatus::class,
            'verification' => VerificationStatus::class,
            'land_size' => 'float',
            'building_size' => 'float',
        ];
    }

    /* ---------- Relasi ---------- */

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('sort_order');
    }

    public function prices()
    {
        return $this->hasMany(PropertyPrice::class);
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

    public function getPriceAttribute(): ?float
    {
        $min = $this->prices->min('price');
        return $min !== null ? (float) $min : null;
    }

    public function scopePublished($query)
    {
        return $query->where('verification', VerificationStatus::Approved->value)
            ->whereIn('status', [PropertyStatus::Available->value, PropertyStatus::Rented->value]);
    }
}
