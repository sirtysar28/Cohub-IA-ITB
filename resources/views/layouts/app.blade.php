<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CoHub IA ITB Jatim') — CoHub IA ITB Jatim</title>
    <link rel="icon" type="image/png" href="{{ favicon_url() }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CoHub">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { 50:'#eef2ff',100:'#e0e7ff',500:'#6366f1',600:'#4f46e5',700:'#4338ca',900:'#312e81' } }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/cohub.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .fade-in { animation: fadeIn .3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col text-gray-800 antialiased">

{{-- Loader pindah halaman --}}
<div id="cohub-loader"><div class="loader-spinner"></div><p class="loader-text" id="cohub-loader-text">Memuat…</p></div>
<div id="cohub-progress"></div>

<div class="flex-1 flex flex-col page-enter pb-16 md:pb-0">
    @yield('content')
</div>

<footer class="bg-gray-900 text-gray-400 mt-auto">
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between gap-6">
            <div>
                <p class="text-white font-bold text-lg flex items-center gap-2">
                    @if ($logo = site_logo_url())
                        <img src="{{ $logo }}" alt="Logo CoHub" class="h-7 w-auto max-w-[130px] object-contain bg-white/10 rounded-lg p-1">
                    @else
                        🏢
                    @endif
                    CoHub IA ITB Jatim
                </p>
                <p class="text-sm mt-1">Platform sewa properti &amp; jasa profesional.</p>
                <p class="text-sm">https://cohubiaitbjatim.com</p>
            </div>
            <div class="text-sm space-y-1">
                <p><a href="{{ route('properties.index') }}" class="hover:text-white transition">Properti</a></p>
                <p><a href="{{ route('services.index') }}" class="hover:text-white transition">Jasa</a></p>
                <p><a href="{{ route('register') }}" class="hover:text-white transition">Daftar</a></p>
            </div>
        </div>
        <p class="text-xs mt-6 border-t border-gray-800 pt-4">&copy; {{ date('Y') }} CoHub IA ITB Jatim. All rights reserved.</p>
    </div>
</footer>

{{-- Bottom navigation mobile (4 ikon) --}}
@include('partials.bottom-nav', ['isDashboard' => false])

@auth
    {{-- Notifikasi live juga aktif di halaman publik untuk pengguna login --}}
    @include('partials.notif-live')
@endauth

{{-- Widget chat: AI Groq & Chat Admin --}}
@include('partials.chat-widget', ['hideForAdmin' => false])

<script src="{{ asset('js/cohub.js') }}" defer></script>
</body>
</html>
