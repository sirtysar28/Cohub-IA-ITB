@extends('layouts.dashboard')

@section('title', 'Dashboard Customer')

@section('content')
<div>
    <h2 class="text-xl font-bold">Selamat datang kembali, {{ auth()->user()->name }} 👋</h2>
    <p class="text-sm text-gray-500">Cari dan sewa properti / jama sesuai kebutuhan Anda.</p>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Total Booking</p>
        <p class="text-2xl font-extrabold mt-1">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Berlangsung</p>
        <p class="text-2xl font-extrabold mt-1 text-cyan-600">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Menunggu</p>
        <p class="text-2xl font-extrabold mt-1 text-yellow-600">{{ $stats['pending'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Selesai</p>
        <p class="text-2xl font-extrabold mt-1 text-green-600">{{ $stats['completed'] }}</p>
    </div>
</div>

{{-- Aksi cepat --}}
<div class="grid sm:grid-cols-2 gap-4">
    <a href="{{ route('properties.index') }}" class="bg-brand-600 hover:bg-brand-700 text-white rounded-2xl p-6 transition">
        <p class="text-3xl">🏢</p>
        <p class="font-bold mt-2">Cari Properti</p>
        <p class="text-sm text-brand-100">Tempat tinggal, ritel, gudang, kantor</p>
    </a>
    <a href="{{ route('services.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white rounded-2xl p-6 transition">
        <p class="text-3xl">🛠</p>
        <p class="font-bold mt-2">Cari Jasa</p>
        <p class="text-sm text-amber-100">Interior, cleaning, catering, rental tools</p>
    </a>
</div>

{{-- Booking terbaru --}}
<div class="bg-white rounded-2xl border">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h3 class="font-semibold">Booking Terbaru</h3>
        <a href="{{ route('customer.bookings.index') }}" class="text-sm text-brand-600 font-semibold">Lihat semua →</a>
    </div>
    <div class="divide-y">
        @forelse ($recentBookings as $booking)
            <a href="{{ route('customer.bookings.show', $booking) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50">
                @if ($thumb = $booking->type->value === 'property' ? $booking->property?->thumbnail : $booking->service?->thumbnail)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" class="w-14 h-14 rounded-xl object-cover" alt="">
                @else
                    <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center text-2xl">{{ $booking->type->value === 'property' ? '🏢' : '🛠' }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate">{{ $booking->item_name }}</p>
                    <p class="text-xs text-gray-400">{{ $booking->code }} · {{ $booking->start_date?->format('d M Y') }}</p>
                </div>
                <span class="text-xs font-medium rounded-full px-3 py-1 {{ booking_badge($booking->status) }}">{{ $booking->status->label() }}</span>
            </a>
        @empty
            <p class="px-6 py-10 text-center text-sm text-gray-400">Belum ada booking. Mulai cari properti atau jasa!</p>
        @endforelse
    </div>
</div>
@endsection
