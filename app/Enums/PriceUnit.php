<?php

namespace App\Enums;

enum PriceUnit: string
{
    case PerHour = 'per_hour';
    case PerDay = 'per_day';
    case PerProject = 'per_project';
    case PerPax = 'per_pax';
    case PerUnit = 'per_unit';

    public function label(): string
    {
        return match ($this) {
            self::PerHour => 'Per Jam',
            self::PerDay => 'Per Hari',
            self::PerProject => 'Per Proyek',
            self::PerPax => 'Per Pax',
            self::PerUnit => 'Per Unit',
        };
    }
}
