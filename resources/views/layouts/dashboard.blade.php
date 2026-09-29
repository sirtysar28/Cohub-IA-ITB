{{-- Layout dashboard dengan sidebar dinamis per role --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — CoHub IA ITB Jatim</title>
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
            theme: { extend: { colors: { brand: { 50:'#eef2ff',100:'#e0e7ff',500:'#6366f1',600:'#4f46e5',700:'#4338ca',900:'#312e81' } } } }
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
<body class="bg-gray-50 min-h-screen text-gray-800 antialiased" x-data="{ sidebar: false }">

{{-- Loader pindah halaman & login --}}
<div id="cohub-loader"><div class="loader-spinner"></div><p class="loader-text" id="cohub-loader-text">Memuat…</p></div>
<div id="cohub-progress"></div>

<div class="flex">
    {{-- ============ SIDEBAR ============ --}}
    <aside id="sidebar" class="w-64 bg-brand-900 text-white flex-shrink-0 fixed md:sticky top-0 h-screen z-50 flex flex-col transition-transform -translate-x-full md:translate-x-0 group/sidebar"
           :class="sidebar && 'translate-x-0'">
        <div class="px-5 py-5 border-b border-white/10 flex items-center gap-2">
            <a href="{{ route('home') }}" class="font-extrabold text-lg whitespace-nowrap flex items-center justify-center">
                @if ($logo = site_logo_url())
                    <img src="{{ $logo }}" alt="Logo CoHub" class="h-8 w-auto max-w-[150px] object-contain">
                @else
                    🏢
                @endif
            </a>
            <a href="{{ route('home') }}" class="sidebar-label font-extrabold text-lg">CoHub <span class="text-xs font-medium text-brand-300 block leading-tight">{{ auth()->user()->role->label() }}</span></a>
        </div>

        <nav class="flex-1 overflow-x-hidden overflow-y-auto py-4 px-3 space-y-1 text-sm">
            @include('partials.sidebar-menu')
        </nav>

        <div class="p-4 border-t border-white/10 text-sm">
            <p class="font-semibold truncate">{{ auth()->user()->name }}</p>
            <p class="sidebar-label text-xs text-brand-200 truncate">{{ auth()->user()->email }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="w-full text-left bg-white/10 hover:bg-red-600 rounded-lg px-3 py-2 transition whitespace-nowrap">🚪 <span class="sidebar-label">Logout</span></button>
            </form>
        </div>
    </aside>

    <div x-cloak x-show="sidebar" @click="sidebar=false" class="fixed inset-0 bg-black/40 z-40 md:hidden backdrop-blur-sm"></div>

    {{-- ============ KONTEN ============ --}}
    <div class="flex-1 min-w-0">
        <header class="bg-white/90 backdrop-blur-md shadow-sm h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <button @click="sidebar=true" class="md:hidden p-2 text-gray-600" aria-label="Buka menu">☰</button>

                {{-- Hamburger minimize sidebar (khusus desktop; di mobile pakai tombol drawer di atas) --}}
                <button id="minimize-btn" class="hidden md:inline-flex items-center justify-center p-2.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" aria-label="Minimalkan menu" title="Minimalkan menu">
                    <span class="menu-hamburger" aria-hidden="true"><span></span><span></span><span></span></span>
                </button>

                <h1 class="font-bold text-lg truncate">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-2">
                {{-- Notifikasi (live: badge & daftar di-update otomatis saat ada request/transaksi/chat) --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open=!open" class="relative p-2 rounded-lg hover:bg-gray-100" aria-label="Notifikasi">
                        🔔
                        @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                        <span id="notif-badge" class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] rounded-full h-4 min-w-4 px-1" style="{{ $unread ? '' : 'display:none' }}">{{ $unread > 99 ? '99+' : $unread }}</span>
                    </button>
                    <div x-cloak x-show="open" @click.outside="open=false"
                         class="absolute right-0 mt-2 w-80 bg-white shadow-xl rounded-xl border max-h-96 overflow-y-auto z-50">
                        <div class="flex items-center justify-between px-4 py-2 border-b">
                            <p class="font-semibold text-sm">Notifikasi</p>
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button class="text-[11px] text-brand-600 hover:underline">Tandai semua dibaca</button>
                            </form>
                        </div>
                        <div id="notif-list">
                            @forelse (auth()->user()->notifications()->take(10)->get() as $notif)
                                <a href="{{ $notif->data['url'] ?? '#' }}" onclick="fetch('{{ route('notifications.read', $notif->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})"
                                   class="block px-4 py-3 border-b hover:bg-gray-50 text-sm {{ $notif->read_at ? 'opacity-60' : '' }}">
                                    <p>{{ $notif->data['message'] ?? 'Notifikasi' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <p class="px-4 py-6 text-sm text-gray-400 text-center">Belum ada notifikasi.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Profil --}}
                <a href="{{ route('profile.edit') }}" class="p-2 rounded-lg hover:bg-gray-100 transition" title="Profil saya" aria-label="Profil saya">👤</a>

                <a href="{{ route('home') }}" class="hidden sm:inline text-sm text-gray-500 hover:text-brand-600 transition">Lihat Situs →</a>
            </div>
        </header>

        <main class="p-4 sm:p-6 space-y-6 pb-24 md:pb-6 page-enter">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm fade-in">✅ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm fade-in">⚠️ {{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- Bottom navigation mobile (4 ikon) --}}
@include('partials.bottom-nav', ['isDashboard' => true])

{{-- Notifikasi live (badge 🔔 + toast) --}}
@include('partials.notif-live')

{{-- Widget chat AI & admin (disembunyikan untuk admin di dashboard — admin punya konsol sendiri) --}}
@include('partials.chat-widget', ['hideForAdmin' => true])

<script src="{{ asset('js/cohub.js') }}" defer></script>
@stack('scripts')
</body>
</html>
