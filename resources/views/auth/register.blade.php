@extends('layouts.app')

@section('content')
@include('partials.navbar')

<main class="flex-1 py-10 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8 fade-in">
        <h1 class="text-2xl font-extrabold">Daftar Akun Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Pilih peran Anda dan lengkapi data pendaftaran.</p>

        {{-- Pemilihan role --}}
        <div class="mt-6 grid grid-cols-3 gap-3">
            @foreach (['customer' => ['Customer', '🧑'], 'owner' => ['Pemilik Properti', '🏢'], 'provider' => ['Penyedia Jasa', '🛠']] as $value => [$label, $icon])
                <a href="{{ route('register', ['as' => $value]) }}"
                   class="rounded-xl border-2 p-4 text-center transition {{ $role->value === $value ? 'border-brand-600 bg-brand-50 text-brand-700 font-semibold' : 'border-gray-200 hover:border-brand-300' }}">
                    <div class="text-2xl">{{ $icon }}</div>
                    <p class="text-sm mt-1">{{ $label }}</p>
                </a>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 grid sm:grid-cols-2 gap-4">
            @csrf
            <input type="hidden" name="role" value="{{ $role->value }}">

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
                <input name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none" placeholder="Nama sesuai identitas">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">WhatsApp *</label>
                <input name="whatsapp" value="{{ old('whatsapp') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none" placeholder="08xxxxxxxxxx">
            </div>
            {{-- Password dengan toggle intip --}}
            @include('partials.password-field', ['name' => 'password', 'label' => 'Password *', 'placeholder' => 'Minimal 8 karakter', 'autocomplete' => 'new-password', 'value' => old('password')])
            @include('partials.password-field', ['name' => 'password_confirmation', 'label' => 'Konfirmasi Password *', 'autocomplete' => 'new-password'])

            {{-- Alamat --}}
            <div class="sm:col-span-2 border-t pt-4 mt-2">
                <p class="text-sm font-semibold text-gray-600 mb-3">Alamat {{ $role->value === 'customer' ? '(disarankan lengkap)' : '(opsional)' }}</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <input name="provinsi" value="{{ old('provinsi') }}" placeholder="Provinsi" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kota_kabupaten" value="{{ old('kota_kabupaten') }}" placeholder="Kota/Kabupaten" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kecamatan" value="{{ old('kecamatan') }}" placeholder="Kecamatan" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kelurahan" value="{{ old('kelurahan') }}" placeholder="Kelurahan" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="kode_pos" value="{{ old('kode_pos') }}" placeholder="Kode Pos" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <input name="alamat" value="{{ old('alamat') }}" placeholder="Alamat lengkap" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
            </div>

            <button class="sm:col-span-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition">
                Daftar sebagai {{ $role->label() }}
            </button>
        </form>

        <p class="text-sm text-center mt-4 text-gray-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-brand-600 font-semibold">Login</a>
        </p>
    </div>
</main>
@endsection
