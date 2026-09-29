<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin'; // Akses penuh seluruh fitur admin (termasuk hapus transaksi)
    case Admin = 'admin';
    case Customer = 'customer';
    case Owner = 'owner';          // Pemilik Properti
    case Provider = 'provider';    // Penyedia Jasa

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::Admin => 'Administrator',
            self::Customer => 'Customer / Penyewa',
            self::Owner => 'Pemilik Properti',
            self::Provider => 'Penyedia Jasa',
        };
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::SuperAdmin, self::Admin => 'admin.dashboard',
            self::Owner => 'owner.dashboard',
            self::Provider => 'provider.dashboard',
            self::Customer => 'customer.dashboard',
        };
    }
}
