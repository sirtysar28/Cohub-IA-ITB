<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PriceUnit;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'code', 'type', 'customer_id', 'property_id', 'service_id',
        'start_date', 'end_date', 'schedule_time', 'quantity',
        'price_period', 'unit_price', 'total_price',
        'purpose', 'note', 'status',
        'submitted_at', 'approved_at', 'paid_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => BookingType::class,
            'status' => BookingStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'schedule_time' => 'datetime:H:i',
            'unit_price' => 'float',
            'total_price' => 'float',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /* ---------- Relasi ---------- */

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function histories()
    {
        return $this->hasMany(BookingStatusHistory::class)->latest();
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /* ---------- Atribut ---------- */

    /** Partner = pemilik properti / penyedia jasa dari booking ini */
    public function getPartnerAttribute()
    {
        return $this->type === BookingType::Property ? $this->property?->owner : $this->service?->provider;
    }

    public function getItemNameAttribute(): string
    {
        return $this->type === BookingType::Property
            ? ($this->property?->name ?? 'Properti dihapus')
            : ($this->service?->name ?? 'Jasa dihapus');
    }

    public function getDurationDaysAttribute(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }
        return max(1, (int) $this->start_date->diffInDays($this->end_date) + 1);
    }

    public function getUnitLabelAttribute(): string
    {
        if ($this->type === BookingType::Property) {
            return $this->price_period ?? '';
        }
        return $this->service?->price_unit?->label() ?? '';
    }
}
