{{-- Menu sidebar dinamis sesuai role --}}
@php
    $menu = match (auth()->user()->role->value) {
        'admin', 'super_admin' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard'), 'icon' => '📊'],
            ['label' => 'Kelola Pengguna', 'url' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*'), 'icon' => '👥'],
            ['label' => 'Verifikasi Properti', 'url' => route('admin.properties.index'), 'active' => request()->routeIs('admin.properties.*'), 'icon' => '🏢'],
            ['label' => 'Verifikasi Jasa', 'url' => route('admin.services.index'), 'active' => request()->routeIs('admin.services.*'), 'icon' => '🛠'],
            ['label' => 'Transaksi', 'url' => route('admin.bookings.index'), 'active' => request()->routeIs('admin.bookings.*'), 'icon' => '🧾'],
            ['label' => 'Chat Masuk', 'url' => route('admin.chats.index'), 'active' => request()->routeIs('admin.chats.*'), 'icon' => '💬', 'badgeId' => 'chat-menu-badge'],
            ['label' => 'Laporan', 'url' => route('admin.reports.index'), 'active' => request()->routeIs('admin.reports.*'), 'icon' => '📈'],
            ['label' => 'History Log', 'url' => route('admin.activity-logs.index'), 'active' => request()->routeIs('admin.activity-logs.*'), 'icon' => '🕘'],
            ['label' => 'Pengaturan', 'url' => route('admin.settings.index'), 'active' => request()->routeIs('admin.settings.*'), 'icon' => '⚙️'],
        ],
        'owner' => [
            ['label' => 'Dashboard', 'url' => route('owner.dashboard'), 'active' => request()->routeIs('owner.dashboard'), 'icon' => '📊'],
            ['label' => 'Properti Saya', 'url' => route('owner.properties.index'), 'active' => request()->routeIs('owner.properties.*'), 'icon' => '🏢'],
            ['label' => 'Tambah Properti', 'url' => route('owner.properties.create'), 'active' => request()->routeIs('owner.properties.create'), 'icon' => '➕'],
            ['label' => 'Order Sewa', 'url' => route('owner.bookings.index'), 'active' => request()->routeIs('owner.bookings.*'), 'icon' => '🧾'],
        ],
        'provider' => [
            ['label' => 'Dashboard', 'url' => route('provider.dashboard'), 'active' => request()->routeIs('provider.dashboard'), 'icon' => '📊'],
            ['label' => 'Jasa Saya', 'url' => route('provider.services.index'), 'active' => request()->routeIs('provider.services.*'), 'icon' => '🛠'],
            ['label' => 'Tambah Jasa', 'url' => route('provider.services.create'), 'active' => request()->routeIs('provider.services.create'), 'icon' => '➕'],
            ['label' => 'Order Jasa', 'url' => route('provider.bookings.index'), 'active' => request()->routeIs('provider.bookings.*'), 'icon' => '🧾'],
        ],
        default => [
            ['label' => 'Dashboard', 'url' => route('customer.dashboard'), 'active' => request()->routeIs('customer.dashboard'), 'icon' => '📊'],
            ['label' => 'Cari Properti', 'url' => route('properties.index'), 'active' => request()->routeIs('properties.index'), 'icon' => '🏢'],
            ['label' => 'Cari Jasa', 'url' => route('services.index'), 'active' => request()->routeIs('services.index'), 'icon' => '🛠'],
            ['label' => 'Booking Saya', 'url' => route('customer.bookings.index'), 'active' => request()->routeIs('customer.bookings.*'), 'icon' => '🧾'],
        ],
    };

    $menu[] = ['label' => 'Profil Saya', 'url' => route('profile.edit'), 'active' => request()->routeIs('profile.*'), 'icon' => '👤'];
@endphp

@foreach ($menu as $item)
    <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
       class="flex items-center gap-3 rounded-lg px-3 py-2 transition whitespace-nowrap {{ $item['active'] ? 'bg-white text-brand-900 font-semibold' : 'hover:bg-white/10 text-brand-100' }}">
        <span class="relative">
            {{ $item['icon'] }}
            @if (isset($item['badgeId']))
                <span id="{{ $item['badgeId'] }}" class="absolute -top-1.5 -right-2 bg-red-500 text-white text-[9px] font-bold rounded-full h-4 min-w-4 px-1 flex items-center justify-center" style="display:none"></span>
            @endif
        </span>
        <span class="sidebar-label flex-1">{{ $item['label'] }}</span>
    </a>
@endforeach
