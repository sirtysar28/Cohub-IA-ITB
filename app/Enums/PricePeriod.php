<?php

namespace App\Enums;

enum PricePeriod: string
{
    case Daily = 'daily';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Per Hari',
            self::Monthly => 'Per Bulan',
            self::Yearly => 'Per Tahun',
        };
    }

    public function days(): int
    {
        return match ($this) {
            self::Daily => 1,
            self::Monthly => 30,
            self::Yearly => 365,
        };
    }
}
