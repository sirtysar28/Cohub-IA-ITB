<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\GroqChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Pengaturan aplikasi (admin): Landing Page, SMTP, dan Branding (logo & favicon).
 */
class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'landing' => Setting::landing(),
            'smtp' => Setting::smtp(),
            'branding' => Setting::branding(),
            'chat' => Setting::chat(),
            'tab' => request('tab', 'landing'),
        ]);
    }

    public function updateLanding(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'landing_hero_title' => ['required', 'string', 'max:150'],
            'landing_hero_subtitle' => ['required', 'string', 'max:255'],
            'landing_cta_label' => ['required', 'string', 'max:50'],
            'landing_section_properties' => ['required', 'string', 'max:100'],
            'landing_section_services' => ['required', 'string', 'max:100'],
            'landing_about' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        ActivityLog::record('settings', 'pengaturan', 'Pengaturan landing page disimpan (judul: "'.$data['landing_hero_title'].'").');

        return redirect()->route('admin.settings.index', ['tab' => 'landing'])
            ->with('success', 'Pengaturan landing page disimpan.');
    }

    public function updateSmtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'smtp_enabled' => ['nullable', 'boolean'],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_encryption' => ['nullable', 'in:none,tls,ssl'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'], // kosong = pertahankan
            'smtp_from_address' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:100'],
        ], [
            'smtp_host.required' => 'SMTP host wajib diisi.',
            'smtp_port.required' => 'SMTP port wajib diisi.',
            'smtp_port.between' => 'Port harus antara 1–65535.',
        ]);

        $fields = [
            'smtp_enabled' => $request->boolean('smtp_enabled') ? '1' : '0',
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => (string) $data['smtp_port'],
            'smtp_encryption' => $data['smtp_encryption'] ?? 'tls',
            'smtp_username' => $data['smtp_username'] ?? '',
            'smtp_from_address' => $data['smtp_from_address'] ?? '',
            'smtp_from_name' => $data['smtp_from_name'] ?? 'CoHub IA ITB Jatim',
        ];

        foreach ($fields as $key => $value) {
            Setting::set($key, $value);
        }

        // Password: hanya timpa jika diisi (agar tidak hilang saat edit)
        if (! empty($data['smtp_password'])) {
            Setting::set('smtp_password', $data['smtp_password']);
        }

        ActivityLog::record('settings', 'pengaturan', 'Pengaturan SMTP disimpan (host: '.$data['smtp_host'].').');

        return redirect()->route('admin.settings.index', ['tab' => 'smtp'])
            ->with('success', 'Pengaturan SMTP disimpan. Konfigurasi langsung aktif.');
    }

    /** Simpan pengaturan Chat & AI (Groq). */
    public function updateChat(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'chat_ai_enabled' => ['nullable', 'boolean'],
            'chat_admin_enabled' => ['nullable', 'boolean'],
            'groq_api_key' => ['nullable', 'string', 'max:255'], // kosong = pertahankan
            'groq_model' => ['nullable', 'string', 'max:100'],
            'chat_welcome_message' => ['nullable', 'string', 'max:500'],
        ]);

        $fields = [
            'chat_ai_enabled' => $request->boolean('chat_ai_enabled') ? '1' : '0',
            'chat_admin_enabled' => $request->boolean('chat_admin_enabled') ? '1' : '0',
            'groq_model' => $data['groq_model'] ?? 'llama-3.3-70b-versatile',
            'chat_welcome_message' => $data['chat_welcome_message'] ?? '',
        ];

        // Validasi: AI hanya bisa aktif jika API key tersedia
        if ($fields['chat_ai_enabled'] === '1'
            && empty($data['groq_api_key'])
            && empty(Setting::get('groq_api_key'))) {
            return back()->withErrors(['groq_api_key' => 'Isi Groq API Key terlebih dahulu sebelum mengaktifkan AI Assistant.'])
                ->withInput();
        }

        foreach ($fields as $key => $value) {
            Setting::set($key, $value);
        }

        if (! empty($data['groq_api_key'])) {
            Setting::set('groq_api_key', trim($data['groq_api_key']));
        }

        ActivityLog::record('settings', 'pengaturan', 'Pengaturan Chat & AI disimpan (AI: '.($fields['chat_ai_enabled'] === '1' ? 'aktif' : 'nonaktif').', model: '.$fields['groq_model'].').');

        return redirect()->route('admin.settings.index', ['tab' => 'chat'])
            ->with('success', 'Pengaturan Chat & AI disimpan. Widget chat langsung diperbarui.');
    }

    /** Tes koneksi API Groq. */
    public function testGroq(): RedirectResponse
    {
        $result = app(GroqChatService::class)->testConnection();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /** Upload logo & favicon aplikasi (tersimpan di disk public). */
    public function updateBranding(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048', 'dimensions:max_width=1200,max_height=1200'],
            'site_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp,svg', 'max:1024', 'dimensions:max_width=512,max_height=512'],
        ], [
            'site_logo.mimes' => 'Logo harus berformat JPG, PNG, WebP, atau SVG.',
            'site_logo.max' => 'Ukuran logo maksimal 2 MB.',
            'site_logo.dimensions' => 'Dimensi logo maksimal 1200x1200 px.',
            'site_favicon.mimes' => 'Favicon harus berformat ICO, PNG, JPG, WebP, atau SVG.',
            'site_favicon.max' => 'Ukuran favicon maksimal 1 MB.',
            'site_favicon.dimensions' => 'Dimensi favicon maksimal 512x512 px (disarankan 32x32 / 512x512).',
        ]);

        $changed = [];

        foreach (['site_logo', 'site_favicon'] as $field) {
            if ($request->hasFile($field)) {
                // Hapus file lama
                $old = Setting::get($field);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }

                $file = $request->file($field);
                $path = $file->storeAs(
                    'branding',
                    $field.'-'.time().'.'.$file->getClientOriginalExtension(),
                    'public',
                );

                Setting::set($field, $path);
                $changed[] = $field === 'site_logo' ? 'logo' : 'favicon';
            }
        }

        // Opsi reset ke default (hapus file & setting)
        if ($request->boolean('reset_logo') && Setting::get('site_logo')) {
            Storage::disk('public')->delete(Setting::get('site_logo'));
            Setting::set('site_logo', null);
            $changed[] = 'logo (direset ke default)';
        }
        if ($request->boolean('reset_favicon') && Setting::get('site_favicon')) {
            Storage::disk('public')->delete(Setting::get('site_favicon'));
            Setting::set('site_favicon', null);
            $changed[] = 'favicon (direset ke default)';
        }

        if (empty($changed)) {
            return back()->with('error', 'Tidak ada perubahan — pilih file logo/favicon terlebih dahulu.');
        }

        ActivityLog::record('settings', 'pengaturan', 'Branding aplikasi diperbarui: '.implode(', ', $changed).'.');

        return redirect()->route('admin.settings.index', ['tab' => 'branding'])
            ->with('success', 'Branding diperbarui: '.implode(', ', $changed).'.');
    }

    /** Kirim email percobaan ke alamat admin yang sedang login. */
    public function testSmtp(Request $request): RedirectResponse
    {
        $request->validate(['test_to' => ['required', 'email']]);

        $smtp = Setting::smtp();

        if (($smtp['smtp_enabled'] ?? '0') !== '1') {
            return back()->with('error', 'SMTP belum diaktifkan. Aktifkan dulu lalu simpan.');
        }

        try {
            Mail::send('emails.generic', [
                'icon' => '🚀',
                'accent' => '#10b981',
                'pretitle' => 'Tes Koneksi Email',
                'title' => 'Hore! Koneksi SMTP Berhasil ✅',
                'intro' => [
                    'Email percobaan ini dikirim dari pengaturan SMTP CoHub IA ITB Jatim. Jika email ini sampai ke kotak masuk Anda, konfigurasi SMTP sudah benar dan siap dipakai untuk mengirim notifikasi transaksi ke customer, admin, pemilik properti, dan penyedia jasa.',
                ],
                'details' => [
                    'Waktu Kirim' => now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB',
                    'Dikirim Oleh' => auth()->user()->name,
                    'Tujuan' => $request->test_to,
                ],
                'ctaLabel' => 'Buka CoHub',
                'ctaUrl' => route('home'),
                'note' => 'Email notifikasi otomatis (booking, verifikasi, chat) akan menggunakan tampilan seperti ini.',
            ], function ($message) use ($request) {
                $message->to($request->test_to)->subject('[CoHub] ✅ Tes Koneksi SMTP Berhasil');
            });

            return back()->with('success', 'Email percobaan terkirim ke '.$request->test_to.'.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email: '.$e->getMessage());
        }
    }
}
