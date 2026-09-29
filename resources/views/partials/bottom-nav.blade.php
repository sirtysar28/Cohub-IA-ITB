{{-- Bottom navigation mobile — 4 ikon di footer (role-aware) --}}
@php
    $isDashboard = $isDashboard ?? false;

    if ($isDashboard && auth()->check()) {
        $items = match (auth()->user()->role->value) {
            'admin', 'super_admin' => [
                ['url' => route('admin.dashboard'), 'icon' => '📊', 'label' => 'Dashboard', 'match' => 'admin.dashboard'],
                ['url' => route('admin.properties.index'), 'icon' => '🏢', 'label' => 'Properti', 'match' => 'admin.properties.*'],
                ['url' => route('admin.bookings.index'), 'icon' => '🧾', 'label' => 'Transaksi', 'match' => 'admin.bookings.*'],
                ['url' => route('profile.edit'), 'icon' => '👤', 'label' => 'Profil', 'match' => 'profile.*'],
            ],
            'owner' => [
                ['url' => route('owner.dashboard'), 'icon' => '📊', 'label' => 'Dashboard', 'match' => 'owner.dashboard'],
                ['url' => route('owner.properties.index'), 'icon' => '🏢', 'label' => 'Properti', 'match' => 'owner.properties.*'],
                ['url' => route('owner.bookings.index'), 'icon' => '🧾', 'label' => 'Order', 'match' => 'owner.bookings.*'],
                ['url' => route('profile.edit'), 'icon' => '👤', 'label' => 'Profil', 'match' => 'profile.*'],
            ],
            'provider' => [
                ['url' => route('provider.dashboard'), 'icon' => '📊', 'label' => 'Dashboard', 'match' => 'provider.dashboard'],
                ['url' => route('provider.services.index'), 'icon' => '🛠', 'label' => 'Jasa', 'match' => 'provider.services.*'],
                ['url' => route('provider.bookings.index'), 'icon' => '🧾', 'label' => 'Order', 'match' => 'provider.bookings.*'],
                ['url' => route('profile.edit'), 'icon' => '👤', 'label' => 'Profil', 'match' => 'profile.*'],
            ],
            default => [
                ['url' => route('customer.dashboard'), 'icon' => '📊', 'label' => 'Dashboard', 'match' => 'customer.dashboard'],
                ['url' => route('properties.index'), 'icon' => '🏢', 'label' => 'Properti', 'match' => 'properties.index'],
                ['url' => route('customer.bookings.index'), 'icon' => '🧾', 'label' => 'Booking', 'match' => 'customer.bookings.*'],
                ['url' => route('profile.edit'), 'icon' => '👤', 'label' => 'Profil', 'match' => 'profile.*'],
            ],
        };
    } else {
        $items = [
            ['url' => route('home'), 'icon' => '🏠', 'label' => 'Home', 'match' => 'home'],
            ['url' => route('properties.index'), 'icon' => '🏢', 'label' => 'Properti', 'match' => 'properties.*'],
            ['url' => route('services.index'), 'icon' => '🛠', 'label' => 'Jasa', 'match' => 'services.*'],
            auth()->check()
                ? ['url' => route(auth()->user()->role->homeRoute()), 'icon' => '📊', 'label' => 'Dashboard', 'match' => 'login']
                : ['url' => route('login'), 'icon' => '🔐', 'label' => 'Login', 'match' => 'login|register'],
        ];
    }
@endphp

<nav id="bottom-nav" class="bottom-nav md:hidden" aria-label="Navigasi bawah">
    @foreach ($items as $item)
        <a href="{{ $item['url'] }}"
           class="{{ request()->routeIs($item['match']) ? 'active' : '' }}">
            <span class="bn-icon">{{ $item['icon'] }}</span>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
