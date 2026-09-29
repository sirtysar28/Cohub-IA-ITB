<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['booking_id', 'invoice_no', 'amount', 'method', 'status', 'paid_at'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'float',
            'paid_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
