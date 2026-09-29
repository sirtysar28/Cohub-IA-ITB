{{-- Navigasi publik (desktop & mobile-first) --}}
<header class="bg-white/85 backdrop-blur-md shadow-sm sticky top-0 z-40 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-xl text-brand-700 hover:scale-[1.03] transition-transform origin-left">
                @if ($logo = site_logo_url())
                    <img src="{{ $logo }}" alt="Logo CoHub" class="h-8 w-auto max-w-[150px] object-contain">
                @else
                    🏢
                @endif
                <span>CoHub<span class="text-gray-400 font-medium"> IA ITB Jatim</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('properties.index') }}" class="relative hover:text-brand-600 transition after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-0 after:bg-brand-500 after:transition-all hover:after:w-full">Properti</a>
                <a href="{{ route('services.index') }}" class="relative hover:text-brand-600 transition after:absolute after:left-0 after:-bottom-1 after:h-0.5 after:w-0 after:bg-brand-500 after:transition-all hover:after:w-full">Jasa</a>
                @guest
                    <a href="{{ route('login') }}" class="hover:text-brand-600 transition">Login</a>
                    <a href="{{ route('register') }}" class="bg-brand-600 text-white px-4 py-2 rounded-lg hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-600/30 hover:-translate-y-0.5 transition-all">Daftar</a>
                @else
                    <a href="{{ route(auth()->user()->role->homeRoute()) }}" class="hover:text-brand-600 transition">Dashboard</a>
                    <a href="{{ route('profile.edit') }}" class="hover:text-brand-600 transition" title="Profil saya">👤</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-gray-500 hover:text-red-600 transition">Logout</button>
                    </form>
                @endguest
            </nav>

            {{-- Menu mobile --}}
            <div class="md:hidden" x-data="{ open: false }">
                <button @click="open = !open" class="p-2 text-gray-600" aria-label="Menu">☰</button>
                <div x-cloak x-show="open" @click.outside="open=false" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute right-2 top-16 bg-white shadow-xl rounded-xl w-52 py-2 text-sm border">
                    <a href="{{ route('properties.index') }}" class="block px-4 py-2 hover:bg-gray-50">🏠 Properti</a>
                    <a href="{{ route('services.index') }}" class="block px-4 py-2 hover:bg-gray-50">🛠 Jasa</a>
                    @guest
                        <a href="{{ route('login') }}" class="block px-4 py-2 hover:bg-gray-50">🔐 Login</a>
                        <a href="{{ route('register') }}" class="block px-4 py-2 hover:bg-gray-50">✍️ Daftar</a>
                    @else
                        <a href="{{ route(auth()->user()->role->homeRoute()) }}" class="block px-4 py-2 hover:bg-gray-50">📊 Dashboard</a>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50">👤 Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-50">🚪 Logout</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</header>

@if ($flash = session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 fade-in">
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">✅ {{ $flash }}</div>
    </div>
@elseif ($flash = session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 fade-in">
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">⚠️ {{ $flash }}</div>
    </div>
@endif
