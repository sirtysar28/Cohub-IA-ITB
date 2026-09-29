<?php

use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Enums\VerificationStatus;
use App\Models\Setting;

if (! function_exists('setting')) {
    /** Ambil nilai pengaturan aplikasi (landing/SMTP) dengan default opsional. */
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('format_idr')) {
    /** Format angka ke Rupiah: 1250000 -> Rp 1.250.000 */
    function format_idr(float|int|string|null $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('status_badge')) {
    /** Render kelas warna Tailwind untuk badge status. */
    function status_badge(string $color): string
    {
        return match ($color) {
            'green' => 'bg-green-100 text-green-800 ring-green-600/20',
            'blue' => 'bg-blue-100 text-blue-800 ring-blue-600/20',
            'yellow' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
            'red' => 'bg-red-100 text-red-800 ring-red-600/20',
            'gray' => 'bg-gray-100 text-gray-800 ring-gray-600/20',
            'indigo' => 'bg-indigo-100 text-indigo-800 ring-indigo-600/20',
            'cyan' => 'bg-cyan-100 text-cyan-800 ring-cyan-600/20',
            'purple' => 'bg-purple-100 text-purple-800 ring-purple-600/20',
            default => 'bg-gray-100 text-gray-800 ring-gray-600/20',
        };
    }
}

if (! function_exists('captcha_styles')) {
    /** Gaya acak per karakter captcha (rotasi & posisi vertikal) — dipakai view login. */
    function captcha_styles(string $code): array
    {
        $styles = [];
        for ($i = 0, $len = strlen($code); $i < $len; $i++) {
            $styles[] = sprintf(
                'display:inline-block;transform:rotate(%ddeg) translateY(%dpx)',
                random_int(-18, 18),
                random_int(-4, 5)
            );
        }

        return $styles;
    }
}

if (! function_exists('booking_badge')) {
    function booking_badge(BookingStatus $status): string
    {
        return status_badge($status->color());
    }
}

if (! function_exists('property_badge')) {
    function property_badge(PropertyStatus $status): string
    {
        return status_badge($status->color());
    }
}

if (! function_exists('verification_badge')) {
    function verification_badge(VerificationStatus $status): string
    {
        return status_badge($status->color());
    }
}

if (! function_exists('activity_badge')) {
    /** Kelas warna Tailwind untuk badge aksi pada history log. */
    function activity_badge(string $action): string
    {
        return match ($action) {
            'login', 'logout', 'register' => 'bg-indigo-100 text-indigo-800 ring-indigo-600/20',
            'create' => 'bg-green-100 text-green-800 ring-green-600/20',
            'update', 'settings', 'status', 'role' => 'bg-blue-100 text-blue-800 ring-blue-600/20',
            'delete', 'reject', 'cancel' => 'bg-red-100 text-red-800 ring-red-600/20',
            'verify', 'approve', 'complete', 'pay' => 'bg-green-100 text-green-800 ring-green-600/20',
            'submit' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
            default => 'bg-gray-100 text-gray-800 ring-gray-600/20',
        };
    }
}

if (! function_exists('site_logo_url')) {
    /** URL logo aplikasi dari pengaturan (null jika belum diupload -> pakai emoji default). */
    function site_logo_url(): ?string
    {
        $path = setting('site_logo');

        return $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)
            ? \Illuminate\Support\Facades\Storage::url($path)
            : null;
    }
}

if (! function_exists('favicon_url')) {
    /** URL favicon dari pengaturan (fallback ke favicon.ico bawaan). */
    function favicon_url(): string
    {
        $path = setting('site_favicon');

        if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::url($path);
        }

        return asset('favicon.ico');
    }
}
