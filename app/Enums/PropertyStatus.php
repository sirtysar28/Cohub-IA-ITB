<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Available = 'available';
    case Rented = 'rented';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive'; // FR-PROP-04: Non-aktif

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Rented => 'Disewa',
            self::Maintenance => 'Perawatan',
            self::Inactive => 'Non-Aktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Available => 'green',
            self::Rented => 'blue',
            self::Maintenance => 'yellow',
            self::Inactive => 'gray',
        };
    }
}
