@extends('layouts.app')

@section('title', 'Login')

@section('content')
@include('partials.navbar')

@php
    $captchaCode = session('captcha_code', '');
    $captchaStyles = captcha_styles($captchaCode);
@endphp

<main class="flex-1 flex items-center justify-center py-12 px-4 relative">
    {{-- Dekorasi parallax belakang kartu login --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="parallax-blob float-slow" style="width:420px;height:420px;background:#c7d2fe;top:-140px;left:-120px;"></div>
        <div class="parallax-blob float-slower" style="width:360px;height:360px;background:#a5b4fc;bottom:-120px;right:-100px;"></div>
    </div>

    <div class="max-w-md w-full bg-white/90 backdrop-blur rounded-2xl shadow-xl shadow-indigo-200/50 border border-indigo-100 p-8 fade-in relative z-10">
        <h1 class="text-2xl font-extrabold text-center">Masuk ke CoHub</h1>
        <p class="text-sm text-gray-500 text-center mt-1">Platform sewa properti &amp; jasa IA ITB Jatim</p>

        @if ($errors->any())
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition" placeholder="nama@email.com">
            </div>

            {{-- Password dengan toggle intip --}}
            @include('partials.password-field', ['name' => 'password', 'label' => 'Password', 'autocomplete' => 'current-password'])

            {{-- Captcha 5 karakter --}}
            <div>
                <label for="captcha-input" class="block text-sm font-medium mb-1">Kode Captcha <span class="text-gray-400 font-normal">(5 karakter)</span></label>
                <div class="flex items-center gap-3">
                    <div id="captcha-box" class="captcha-box" aria-label="Kode captcha">
                        @foreach (str_split($captchaCode) as $i => $char)
                            <span class="captcha-char" style="{{ $captchaStyles[$i] ?? '' }}">{{ $char }}</span>
                        @endforeach
                    </div>
                    <button type="button" data-captcha-refresh="{{ route('captcha.refresh') }}"
                            class="p-2.5 rounded-xl border border-gray-200 hover:border-brand-400 hover:bg-brand-50 transition text-lg"
                            title="Ganti kode captcha" aria-label="Ganti kode captcha">🔄</button>
                </div>
                <input type="text" id="captcha-input" name="captcha" required maxlength="5" autocomplete="off"
                       value="{{ old('captcha') }}"
                       placeholder="Ketik kode di atas"
                       class="mt-2 w-full rounded-xl border-gray-300 border px-4 py-2.5 font-mono tracking-[.25em] uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                @error('captcha')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300"> Ingat saya
            </label>

            <button data-loading-text="Sedang masuk…" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-2.5 rounded-xl transition disabled:opacity-70">Login</button>
        </form>

        <p class="text-sm text-center mt-6 text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-brand-600 font-semibold hover:underline">Daftar sekarang</a>
        </p>

        <div class="mt-6 bg-gray-50 rounded-xl p-3 text-xs text-gray-500 leading-relaxed">
            <p class="font-semibold text-gray-600 mb-1">Akun demo:</p>
            <p>superadmin@cohub.id · admin@cohub.id · owner1@cohub.id · provider1@cohub.id · customer@cohub.id — password: <b>password</b></p>
        </div>
    </div>
</main>
@endsection
