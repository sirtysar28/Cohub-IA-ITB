@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
@include('partials.navbar')

{{-- Hero + Search dengan animasi parallax --}}
<section data-parallax class="parallax bg-gradient-to-br from-brand-900 via-brand-700 to-brand-500 text-white">
    {{-- Layer parallax (mouse + scroll) --}}
    <div class="parallax-layer" data-parallax-speed="18">
        <div class="parallax-blob float-slow" style="width:480px;height:480px;background:#818cf8;top:-12%;left:-8%;"></div>
        <div class="parallax-blob float-slower" style="width:380px;height:380px;background:#38bdf8;bottom:-18%;right:-6%;opacity:.35;"></div>
    </div>
    <div class="parallax-layer" data-parallax-speed="34" style="inset:-8% -4%;">
        <div class="parallax-blob" style="width:260px;height:260px;background:#e879f9;top:22%;left:58%;opacity:.28;"></div>
    </div>
    <div class="parallax-grid"></div>
    <span data-parallax-orb="26" class="parallax-orb float-slow" style="width:64px;height:64px;top:18%;left:12%;"></span>
    <span data-parallax-orb="42" class="parallax-orb float-slower" style="width:34px;height:34px;top:64%;left:20%;"></span>
    <span data-parallax-orb="34" class="parallax-orb float-slow" style="width:48px;height:48px;top:28%;right:14%;"></span>
    <span data-parallax-orb="52" class="parallax-orb float-slower" style="width:22px;height:22px;top:70%;right:24%;"></span>

    <div data-parallax-content class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
        <span class="inline-block text-xs font-semibold tracking-[.2em] uppercase bg-white/10 border border-white/20 rounded-full px-4 py-1.5 backdrop-blur fade-in">✨ CoHub IA ITB Jatim</span>
        <h1 class="mt-5 text-3xl md:text-6xl font-extrabold shimmer leading-tight">{{ $landing['landing_hero_title'] }}</h1>
        <p class="mt-4 text-brand-100 max-w-2xl mx-auto md:text-lg">{{ $landing['landing_hero_subtitle'] }}</p>

        <form action="{{ request()->fullUrlWithQuery([]) }}" method="GET" class="mt-9 max-w-3xl mx-auto bg-white/95 backdrop-blur rounded-2xl p-3 shadow-2xl shadow-indigo-950/30 flex flex-col md:flex-row gap-3 reveal">
            <select name="tipe" onchange="this.form.action = this.value === 'service' ? '{{ route('services.index') }}' : '{{ route('properties.index') }}'" class="md:w-40 rounded-xl border-gray-300 border px-3 py-2.5 text-gray-700 bg-white">
                <option value="property">🏠 Properti</option>
                <option value="service">🛠 Jasa</option>
            </select>
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama, lokasi, atau kata kunci..." class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-gray-700 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
            <button class="bg-brand-600 hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-600/40 hover:-translate-y-0.5 text-white font-semibold px-6 py-2.5 rounded-xl transition-all">{{ $landing['landing_cta_label'] }}</button>
        </form>
    </div>

    {{-- Gelombang pemisah halus --}}
    <svg class="absolute bottom-0 left-0 right-0 z-10" viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0,32 C360,64 1080,0 1440,32 L1440,60 L0,60 Z" fill="#f9fafb"/>
    </svg>
</section>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

    {{-- Tentang singkat --}}
    @if (!empty($landing['landing_about']))
        <section class="reveal">
            <div class="bg-white rounded-2xl border p-6 sm:p-8 text-center max-w-3xl mx-auto hover-lift">
                <p class="text-xs font-semibold tracking-[.18em] uppercase text-brand-500">Tentang Platform</p>
                <p class="mt-3 text-gray-600 leading-relaxed">{{ $landing['landing_about'] }}</p>
            </div>
        </section>
    @endif

    {{-- Properti Populer --}}
    <section class="reveal">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-2xl font-bold">{{ $landing['landing_section_properties'] }}</h2>
            <a href="{{ route('properties.index') }}" class="text-brand-600 text-sm font-semibold hover:underline">Lihat semua →</a>
        </div>

        @if ($properties->isEmpty())
            <p class="text-gray-400 bg-white rounded-xl p-8 text-center border">Belum ada properti yang dipublikasikan.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($properties->take(4) as $property)
                    <div class="reveal reveal-delay-{{ min($loop->iteration, 3) }}">
                        @include('partials.property-card', ['item' => $property])
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Jasa Populer --}}
    <section class="reveal">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-2xl font-bold">{{ $landing['landing_section_services'] }}</h2>
            <a href="{{ route('services.index') }}" class="text-brand-600 text-sm font-semibold hover:underline">Lihat semua →</a>
        </div>

        @if ($services->isEmpty())
            <p class="text-gray-400 bg-white rounded-xl p-8 text-center border">Belum ada jasa yang dipublikasikan.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($services->take(4) as $service)
                    <div class="reveal reveal-delay-{{ min($loop->iteration, 3) }}">
                        @include('partials.service-card', ['item' => $service])
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Cara Kerja --}}
    <section class="reveal">
        <div class="bg-white rounded-2xl border p-8 hover:shadow-xl hover:shadow-indigo-100 transition-shadow duration-500">
            <h2 class="text-2xl font-bold text-center">Cara Kerja CoHub</h2>
            <div class="grid md:grid-cols-4 gap-6 mt-8 text-center text-sm">
                <div class="reveal reveal-delay-1"><p class="text-3xl float-slow">🔎</p><p class="font-semibold mt-2">1. Cari</p><p class="text-gray-500 mt-1">Cari properti atau jasa sesuai kebutuhan.</p></div>
                <div class="reveal reveal-delay-2"><p class="text-3xl float-slower">📝</p><p class="font-semibold mt-2">2. Ajukan Sewa</p><p class="text-gray-500 mt-1">Isi tanggal, tujuan, dan catatan khusus.</p></div>
                <div class="reveal reveal-delay-2"><p class="text-3xl float-slow">✅</p><p class="font-semibold mt-2">3. Konfirmasi</p><p class="text-gray-500 mt-1">Pemilik/Penyedia menyetujui pengajuan Anda.</p></div>
                <div class="reveal reveal-delay-3"><p class="text-3xl float-slower">🎉</p><p class="font-semibold mt-2">4. Selesai</p><p class="text-gray-500 mt-1">Bayar dan mulai gunakan properti/jasa.</p></div>
            </div>
        </div>
    </section>
</main>
@endsection
