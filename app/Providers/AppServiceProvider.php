<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination custom (clean) + tetap pakai query string
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');

        // Terapkan pengaturan SMTP dari database (jika diaktifkan admin)
        if (Schema::hasTable('settings')) {
            $this->applySmtpSettings();
        }
    }

    /**
     * Override konfigurasi mail SMTP secara runtime dari tabel settings.
     */
    protected function applySmtpSettings(): void
    {
        try {
            $smtp = Setting::smtp();

            if (($smtp['smtp_enabled'] ?? '0') !== '1' || empty($smtp['smtp_host'])) {
                return;
            }

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $smtp['smtp_host'],
                'port' => (int) $smtp['smtp_port'],
                'encryption' => in_array($smtp['smtp_encryption'], ['tls', 'ssl'], true) ? $smtp['smtp_encryption'] : null,
                'username' => $smtp['smtp_username'] ?: null,
                'password' => $smtp['smtp_password'] ?: null,
                'timeout' => null,
                'local_domain' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost',
            ]);

            if (! empty($smtp['smtp_from_address'])) {
                Config::set('mail.from', [
                    'address' => $smtp['smtp_from_address'],
                    'name' => $smtp['smtp_from_name'] ?: config('app.name'),
                ]);
            }
        } catch (\Throwable) {
            // Abaikan (misal saat migrate pertama kali) — pakai konfigurasi .env.
        }
    }
}
