<?php

namespace App\Models;

use App\Enums\PricePeriod;
use Illuminate\Database\Eloquent\Model;

class PropertyPrice extends Model
{
    protected $fillable = ['property_id', 'period', 'price'];

    protected function casts(): array
    {
        return [
            'period' => PricePeriod::class,
            'price' => 'float',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
