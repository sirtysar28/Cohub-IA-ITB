@extends('layouts.dashboard')

@section('title', 'Pengaturan')

@section('content')
<div>
    <h2 class="text-xl font-bold">⚙️ Pengaturan Aplikasi</h2>
    <p class="text-sm text-gray-500">Atur landing page publik dan konfigurasi SMTP (email).</p>
</div>

{{-- Tab switcher --}}
<div class="flex flex-wrap gap-2 bg-white border rounded-xl p-1.5 w-fit reveal">
    <a href="{{ route('admin.settings.index', ['tab' => 'landing']) }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'landing' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">🏠 Landing Page</a>
    <a href="{{ route('admin.settings.index', ['tab' => 'smtp']) }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'smtp' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">✉️ SMTP Email</a>
    <a href="{{ route('admin.settings.index', ['tab' => 'branding']) }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'branding' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">🎨 Branding</a>
    <a href="{{ route('admin.settings.index', ['tab' => 'chat']) }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'chat' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">🤖 Chat &amp; AI</a>
</div>

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

@if ($tab === 'landing')
    {{-- ================= LANDING PAGE ================= --}}
    <section class="bg-white rounded-2xl border p-6 max-w-3xl reveal">
        <h3 class="font-semibold">🏠 Pengaturan Landing Page</h3>
        <p class="text-xs text-gray-400 mt-0.5 mb-5">Konten ini tampil di halaman depan publik.</p>

        <form method="POST" action="{{ route('admin.settings.landing') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Judul Hero *</label>
                <input name="landing_hero_title" value="{{ old('landing_hero_title', $landing['landing_hero_title']) }}" required maxlength="150"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                @error('landing_hero_title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Sub-judul Hero *</label>
                <textarea name="landing_hero_subtitle" rows="2" required maxlength="255"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">{{ old('landing_hero_subtitle', $landing['landing_hero_subtitle']) }}</textarea>
                @error('landing_hero_subtitle')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Label Tombol CTA *</label>
                    <input name="landing_cta_label" value="{{ old('landing_cta_label', $landing['landing_cta_label']) }}" required maxlength="50"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    @error('landing_cta_label')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Judul Section Properti *</label>
                    <input name="landing_section_properties" value="{{ old('landing_section_properties', $landing['landing_section_properties']) }}" required maxlength="100"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    @error('landing_section_properties')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Judul Section Jasa *</label>
                <input name="landing_section_services" value="{{ old('landing_section_services', $landing['landing_section_services']) }}" required maxlength="100"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                @error('landing_section_services')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi Singkat Platform</label>
                <textarea name="landing_about" rows="3" maxlength="1000"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">{{ old('landing_about', $landing['landing_about']) }}</textarea>
                @error('landing_about')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <button data-loading-text="Menyimpan…" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl transition disabled:opacity-70">💾 Simpan Pengaturan</button>
        </form>
    </section>

@elseif ($tab === 'chat')
    {{-- ================= CHAT & AI (GROQ) ================= --}}
    <section class="bg-white rounded-2xl border p-6 max-w-3xl reveal">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h3 class="font-semibold">🤖 Chat &amp; AI Assistant (Groq)</h3>
                <p class="text-xs text-gray-400 mt-0.5">Widget chat di pojok kanan bawah: chat langsung ke admin &amp; chatbot AI berbasis Groq yang menjawab otomatis dari bahan workflow aplikasi.</p>
            </div>
            <span class="text-xs rounded-full px-3 py-1 font-semibold {{ ($chat['chat_ai_enabled'] ?? '0') === '1' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ ($chat['chat_ai_enabled'] ?? '0') === '1' ? '● AI Aktif' : '○ AI Nonaktif' }}
            </span>
        </div>

        <form method="POST" action="{{ route('admin.settings.chat') }}" class="mt-5 space-y-4">
            @csrf @method('PUT')

            <div class="grid sm:grid-cols-2 gap-3">
                <label class="flex items-center gap-3 bg-brand-50 border border-brand-100 rounded-xl px-4 py-3 text-sm cursor-pointer">
                    <input type="checkbox" name="chat_ai_enabled" value="1" {{ old('chat_ai_enabled', ($chat['chat_ai_enabled'] ?? '0') === '1') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-brand-600">
                    <span><b>🤖 AI Assistant (Groq)</b><br><span class="text-xs text-gray-500">Chatbot otomatis 24/7</span></span>
                </label>
                <label class="flex items-center gap-3 bg-brand-50 border border-brand-100 rounded-xl px-4 py-3 text-sm cursor-pointer">
                    <input type="checkbox" name="chat_admin_enabled" value="1" {{ old('chat_admin_enabled', ($chat['chat_admin_enabled'] ?? '1') === '1') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-brand-600">
                    <span><b>👨‍💻 Chat ke Admin</b><br><span class="text-xs text-gray-500">Percakapan langsung dengan admin</span></span>
                </label>
            </div>

            <div class="border rounded-2xl p-5 bg-gradient-to-br from-purple-50 to-indigo-50">
                <p class="text-sm font-bold mb-1">🔑 Groq API Key</p>
                <p class="text-xs text-gray-500 mb-3">Dapatkan gratis di <a href="https://console.groq.com/keys" target="_blank" rel="noopener" class="text-brand-600 underline">console.groq.com/keys</a>. Key tersimpan di database &amp; bisa diganti kapan saja.</p>
                <div class="relative">
                    <input type="password" id="groq-key-input" name="groq_api_key" autocomplete="new-password"
                           placeholder="{{ !empty($chat['groq_api_key']) ? '•••••••••••• (tersimpan — isi untuk mengganti)' : 'gsk_...' }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-12 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    <button type="button" class="pw-toggle" data-pw-toggle="groq-key-input" aria-label="Lihat API key" title="Intip API key">👁️</button>
                </div>
                @error('groq_api_key')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Model AI</label>
                    <select name="groq_model" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                        @foreach (\App\Services\GroqChatService::MODELS as $model => $label)
                            <option value="{{ $model }}" {{ old('groq_model', $chat['groq_model'] ?? 'llama-3.3-70b-versatile') === $model ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">💡 Balasan AI bersumber dari knowledge base workflow CoHub (resources/knowledge/cohub-workflow.md).</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Pesan Selamat Datang Widget Chat</label>
                <textarea name="chat_welcome_message" rows="2" maxlength="500"
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">{{ old('chat_welcome_message', $chat['chat_welcome_message'] ?? '') }}</textarea>
            </div>

            <button data-loading-text="Menyimpan…" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl transition disabled:opacity-70">💾 Simpan Pengaturan Chat</button>
        </form>

        {{-- Tes koneksi Groq --}}
        <div class="mt-6 pt-5 border-t">
            <h4 class="text-sm font-semibold">🔌 Tes Koneksi Groq</h4>
            <p class="text-xs text-gray-400 mt-0.5 mb-3">Pastikan API key sudah tersimpan sebelum menekan tombol ini.</p>
            <form method="POST" action="{{ route('admin.settings.chat.test') }}">
                @csrf
                <button data-loading-text="Menghubungi Groq…" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition disabled:opacity-70">🚀 Tes Sekarang</button>
            </form>
        </div>
    </section>

@elseif ($tab === 'branding')
    {{-- ================= BRANDING: LOGO & FAVICON ================= --}}
    <section class="bg-white rounded-2xl border p-6 max-w-3xl reveal">
        <h3 class="font-semibold">🎨 Branding Aplikasi</h3>
        <p class="text-xs text-gray-400 mt-0.5 mb-5">Ganti logo &amp; favicon aplikasi. Logo tampil di navbar, sidebar dashboard, dan footer; favicon tampil di tab browser.</p>

        <form method="POST" action="{{ route('admin.settings.branding') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Upload Logo --}}
            <div class="border rounded-2xl p-5">
                <div class="flex flex-wrap items-center gap-5">
                    <div class="w-28 h-28 rounded-2xl bg-brand-50 border-2 border-dashed border-brand-200 flex items-center justify-center overflow-hidden shrink-0" id="logo-preview-box">
                        @if ($logoUrl = site_logo_url())
                            <img src="{{ $logoUrl }}" alt="Logo aplikasi" class="w-full h-full object-contain p-2" id="logo-preview">
                        @else
                            <span class="text-4xl" id="logo-preview">🏢</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-[220px]">
                        <p class="text-sm font-semibold">Logo Aplikasi</p>
                        <p class="text-xs text-gray-400 mt-0.5 mb-3">Format JPG/PNG/WebP/SVG, maks 2 MB, disarankan rasio 1:1 (maks 1200×1200). Rasio lebar juga didukung.</p>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                                📤 Pilih Logo…
                                <input type="file" name="site_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" onchange="previewFile(this, 'logo-preview')">
                            </label>
                            @if (site_logo_url())
                                <label class="cursor-pointer inline-flex items-center gap-2 border border-red-200 text-red-600 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-50 transition">
                                    <input type="checkbox" name="reset_logo" value="1" class="rounded border-gray-300 text-red-600">↩️ Reset ke Default
                                </label>
                            @endif
                        </div>
                        @error('site_logo')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
                        <p class="text-[11px] text-gray-400 mt-2">✅ Aktif di: navbar publik, sidebar dashboard &amp; footer.</p>
                    </div>
                </div>
            </div>

            {{-- Upload Favicon --}}
            <div class="border rounded-2xl p-5">
                <div class="flex flex-wrap items-center gap-5">
                    <div class="w-28 h-28 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ favicon_url() }}" alt="Favicon" class="w-12 h-12 object-contain" id="favicon-preview" onerror="this.outerHTML='<span class=\'text-2xl\' id=\'favicon-preview\'>🌐</span>'">
                    </div>
                    <div class="flex-1 min-w-[220px]">
                        <p class="text-sm font-semibold">Favicon</p>
                        <p class="text-xs text-gray-400 mt-0.5 mb-3">Format ICO/PNG/JPG/WebP/SVG, maks 1 MB. Disarankan persegi 32×32 atau 512×512.</p>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                                📤 Pilih Favicon…
                                <input type="file" name="site_favicon" accept="image/x-icon,image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" onchange="previewFile(this, 'favicon-preview')">
                            </label>
                            @if (setting('site_favicon'))
                                <label class="cursor-pointer inline-flex items-center gap-2 border border-red-200 text-red-600 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-50 transition">
                                    <input type="checkbox" name="reset_favicon" value="1" class="rounded border-gray-300 text-red-600">↩️ Reset ke Default
                                </label>
                            @endif
                        </div>
                        @error('site_favicon')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
                        <p class="text-[11px] text-gray-400 mt-2">💡 Setelah simpan, refresh halaman (Ctrl/Cmd+Shift+R) bila favicon belum berubah.</p>
                    </div>
                </div>
            </div>

            <button data-loading-text="Mengunggah…" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl transition disabled:opacity-70">💾 Simpan Branding</button>
        </form>
    </section>

@else
    {{-- ================= SMTP ================= --}}
    <section class="bg-white rounded-2xl border p-6 max-w-3xl reveal">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h3 class="font-semibold">✉️ Konfigurasi SMTP</h3>
                <p class="text-xs text-gray-400 mt-0.5">Dipakai untuk mengirim email (notififikasi, dsb). Konfigurasi langsung aktif setelah disimpan.</p>
            </div>
            <span class="text-xs rounded-full px-3 py-1 font-semibold {{ ($smtp['smtp_enabled'] ?? '0') === '1' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ ($smtp['smtp_enabled'] ?? '0') === '1' ? '● Aktif' : '○ Nonaktif (pakai .env)' }}
            </span>
        </div>

        <form method="POST" action="{{ route('admin.settings.smtp') }}" class="mt-5 space-y-4">
            @csrf @method('PUT')

            <label class="flex items-center gap-3 bg-brand-50 border border-brand-100 rounded-xl px-4 py-3 text-sm cursor-pointer">
                <input type="checkbox" name="smtp_enabled" value="1" {{ old('smtp_enabled', ($smtp['smtp_enabled'] ?? '0') === '1') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-brand-600">
                <span><b>Aktifkan SMTP kustom</b> — timpa pengaturan email dari file .env</span>
            </label>

            <div class="grid sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1">SMTP Host *</label>
                    <input name="smtp_host" value="{{ old('smtp_host', $smtp['smtp_host']) }}" placeholder="smtp.gmail.com" required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    @error('smtp_host')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Port *</label>
                    <input type="number" name="smtp_port" value="{{ old('smtp_port', $smtp['smtp_port']) }}" placeholder="587" required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    @error('smtp_port')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Enkripsi</label>
                    <select name="smtp_encryption" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                        @foreach (['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'Tanpa Enkripsi'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('smtp_encryption', $smtp['smtp_encryption']) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Username (SMTP)</label>
                    <input name="smtp_username" value="{{ old('smtp_username', $smtp['smtp_username']) }}" autocomplete="off"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                </div>
                {{-- Password SMTP dengan toggle intip --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Password (SMTP)</label>
                    <div class="relative">
                        <input type="password" id="smtp-password-input" name="smtp_password" autocomplete="new-password" placeholder="{{ !empty($smtp['smtp_password']) ? '•••••• (tersimpan)' : 'opsional' }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-12 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                        <button type="button" class="pw-toggle" data-pw-toggle="smtp-password-input" aria-label="Lihat password SMTP" title="Intip password">👁️</button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Kosongkan untuk mempertahankan password lama.</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email Pengirim (From)</label>
                    <input type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $smtp['smtp_from_address']) }}" placeholder="noreply@cohubiaitbjatim.com"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    @error('smtp_from_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Pengirim</label>
                    <input name="smtp_from_name" value="{{ old('smtp_from_name', $smtp['smtp_from_name']) }}" placeholder="CoHub IA ITB Jatim"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                </div>
            </div>

            <button data-loading-text="Menyimpan…" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl transition disabled:opacity-70">💾 Simpan SMTP</button>
        </form>

        {{-- Tes kirim email --}}
        <div class="mt-6 pt-5 border-t">
            <h4 class="text-sm font-semibold">📮 Tes Kirim Email</h4>
            <p class="text-xs text-gray-400 mt-0.5 mb-3">Pastikan SMTP aktif &amp; tersimpan sebelum mengirim email percobaan.</p>
            <form method="POST" action="{{ route('admin.settings.smtp.test') }}" class="flex gap-2 max-w-md">
                @csrf
                <input type="email" name="test_to" value="{{ auth()->user()->email }}" required placeholder="tujuan@email.com"
                       class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                <button data-loading-text="Mengirim…" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition whitespace-nowrap disabled:opacity-70">🚀 Kirim Tes</button>
            </form>
        </div>
    </section>
@endif
@endsection

@push('scripts')
<script>
/* Preview logo/favicon langsung sebelum diupload */
function previewFile(input, targetId) {
    var file = input.files && input.files[0];
    var target = document.getElementById(targetId);
    if (!file || !target) return;

    if (file.type === 'image/svg+xml') {
        var reader = new FileReader();
        reader.onload = function (e) {
            target.outerHTML = '<img src="' + e.target.result + '" alt="Preview" id="' + targetId + '" class="w-full h-full object-contain p-2">';
        };
        reader.readAsDataURL(file);
        return;
    }

    target.outerHTML = '<img src="' + URL.createObjectURL(file) + '" alt="Preview" id="' + targetId + '" class="w-full h-full object-contain p-2">';
}
</script>
@endpush
