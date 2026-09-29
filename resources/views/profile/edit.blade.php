@extends('layouts.dashboard')

@section('title', 'Profil Saya')

@section('content')
<div class="flex items-center gap-4">
    <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-2xl font-extrabold shadow-inner">
        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
    </div>
    <div>
        <h2 class="text-xl font-bold">👤 Profil Saya</h2>
        <p class="text-sm text-gray-500">{{ $user->role->label() }} · {{ $user->email }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 items-start">

    {{-- ============ DATA AKUN & ALAMAT ============ --}}
    <section class="bg-white rounded-2xl border p-6 reveal">
        <h3 class="font-semibold">Data Akun &amp; Alamat</h3>
        <p class="text-xs text-gray-400 mt-0.5 mb-4">Perbarui informasi kontak dan alamat Anda.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="grid sm:grid-cols-2 gap-4">
            @csrf @method('PUT')

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
                <input name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-400">
                <p class="text-xs text-gray-400 mt-1">Email tidak dapat diubah.</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">WhatsApp *</label>
                <input name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" required placeholder="08xxxxxxxxxx"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                @error('whatsapp')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2 border-t pt-4">
                <p class="text-sm font-semibold text-gray-600 mb-3">Alamat</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <input name="provinsi" value="{{ old('provinsi', $profile->provinsi) }}" placeholder="Provinsi" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kota_kabupaten" value="{{ old('kota_kabupaten', $profile->kota_kabupaten) }}" placeholder="Kota/Kabupaten" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kecamatan" value="{{ old('kecamatan', $profile->kecamatan) }}" placeholder="Kecamatan" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan) }}" placeholder="Kelurahan" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kode_pos" value="{{ old('kode_pos', $profile->kode_pos) }}" placeholder="Kode Pos" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="alamat" value="{{ old('alamat', $profile->alamat) }}" placeholder="Alamat lengkap" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
            </div>

            <button data-loading-text="Menyimpan…" class="sm:col-span-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold py-2.5 rounded-xl transition disabled:opacity-70">
                💾 Simpan Perubahan
            </button>
        </form>
    </section>

    {{-- ============ GANTI PASSWORD ============ --}}
    <section class="bg-white rounded-2xl border p-6 reveal reveal-delay-1">
        <h3 class="font-semibold">🔒 Ganti Password</h3>
        <p class="text-xs text-gray-400 mt-0.5 mb-4">Password baru minimal 8 karakter.</p>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PUT')

            @include('partials.password-field', ['name' => 'current_password', 'label' => 'Password Saat Ini *', 'autocomplete' => 'current-password'])
            @include('partials.password-field', ['name' => 'password', 'label' => 'Password Baru *', 'placeholder' => 'Minimal 8 karakter', 'autocomplete' => 'new-password', 'hint' => 'Gunakan kombinasi huruf, angka, dan simbol untuk keamanan.'])
            @include('partials.password-field', ['name' => 'password_confirmation', 'label' => 'Konfirmasi Password Baru *', 'autocomplete' => 'new-password'])

            <button data-loading-text="Mengubah…" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-semibold py-2.5 rounded-xl transition disabled:opacity-70">
                🔑 Ubah Password
            </button>
        </form>
    </section>
</div>
@endsection
