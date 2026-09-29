<?php

namespace App\Enums;

enum BookingType: string
{
    case Property = 'property';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::Property => 'Sewa Properti',
            self::Service => 'Sewa Jasa',
        };
    }
}
