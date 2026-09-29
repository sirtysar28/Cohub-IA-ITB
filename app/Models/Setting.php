<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan aplikasi (landing page & SMTP) — key/value store sederhana.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /* ---------------- Landing Page (default values) ---------------- */

    public const LANDING_DEFAULTS = [
        'landing_hero_title' => 'Find Your Perfect Space & Services',
        'landing_hero_subtitle' => 'Sewa properti dan jasa profesional dalam satu platform — CoHub IA ITB Jatim',
        'landing_cta_label' => '🔍 Cari Sekarang',
        'landing_section_properties' => '🏢 Properti Populer',
        'landing_section_services' => '🛠 Jasa Profesional',
        'landing_about' => 'CoHub IA ITB Jatim menghubungkan pemilik properti, penyedia jasa, dan penyewa dalam satu ekosistem terpercaya.',
    ];

    /* ---------------- SMTP (default values) ---------------- */

    public const SMTP_DEFAULTS = [
        'smtp_enabled' => '0',
        'smtp_host' => '',
        'smtp_port' => '587',
        'smtp_encryption' => 'tls',
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_from_address' => '',
        'smtp_from_name' => 'CoHub IA ITB Jatim',
    ];

    /* ---------------- Branding (default values) ---------------- */

    public const BRANDING_DEFAULTS = [
        'site_logo' => '',    // path file di disk public (kosong = pakai emoji default)
        'site_favicon' => '', // path file di disk public (kosong = pakai favicon.ico bawaan)
    ];

    /* ---------------- Chat & AI Groq (default values) ---------------- */

    public const CHAT_DEFAULTS = [
        'chat_ai_enabled' => '0',          // 1 = widget AI aktif
        'chat_admin_enabled' => '1',       // 1 = chat ke admin aktif
        'chat_welcome_message' => 'Halo! 👋 Saya asisten AI CoHub. Tanya apa saja seputar sewa properti, jasa, dan alur booking ya!',
        'groq_api_key' => '',              // API key Groq (console.groq.com)
        'groq_model' => 'llama-3.3-70b-versatile',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** Ambil semua setting satu grup (prefix), gabung dengan default. */
    public static function group(string $prefix, array $defaults = []): array
    {
        $rows = static::query()->where('key', 'like', $prefix.'%')->pluck('value', 'key');

        return collect($defaults)->merge($rows)->all();
    }

    /** Setting landing page (dengan nilai default). */
    public static function landing(): array
    {
        return static::group('landing_', self::LANDING_DEFAULTS);
    }

    /** Setting SMTP (dengan nilai default). */
    public static function smtp(): array
    {
        return static::group('smtp_', self::SMTP_DEFAULTS);
    }

    /** Setting branding: logo & favicon (dengan nilai default). */
    public static function branding(): array
    {
        return static::group('site_', self::BRANDING_DEFAULTS);
    }

    /** Setting chat & AI Groq (dengan nilai default).
     *
     * CATATAN: tidak memakai group('chat_') + group('groq_) karena operator union (+)
     * mempertahankan nilai default (string kosong) untuk key groq_* sehingga
     * API key yang sudah tersimpan di DB selalu terbaca kosong.
     * Semua key diambil sekaligus dengan whereIn agar nilai DB selalu menang.
     */
    public static function chat(): array
    {
        $rows = static::query()->whereIn('key', array_keys(self::CHAT_DEFAULTS))->pluck('value', 'key');

        return collect(self::CHAT_DEFAULTS)->merge($rows)->all();
    }
}
