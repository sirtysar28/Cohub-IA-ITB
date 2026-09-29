@extends('layouts.dashboard')

@section('title', 'Dashboard Provider')

@section('content')
<div>
    <h2 class="text-xl font-bold">Dashboard Penyedia Jasa</h2>
    <p class="text-sm text-gray-500">Kelola layanan profesional dan order jasa Anda.</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Total Jasa</p>
        <p class="text-2xl font-extrabold mt-1">{{ $stats['total_services'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Aktif</p>
        <p class="text-2xl font-extrabold mt-1 text-green-600">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Menunggu Verifikasi Admin</p>
        <p class="text-2xl font-extrabold mt-1 text-yellow-600">{{ $stats['pending_verification'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Total Order</p>
        <p class="text-2xl font-extrabold mt-1">{{ $stats['total_bookings'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Menunggu Konfirmasi</p>
        <p class="text-2xl font-extrabold mt-1 text-orange-600">{{ $stats['pending_bookings'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border p-5">
        <p class="text-xs text-gray-400">Pendapatan (Selesai)</p>
        <p class="text-2xl font-extrabold mt-1 text-brand-700">{{ format_idr($stats['revenue']) }}</p>
    </div>
</div>

<div class="flex gap-3 flex-wrap">
    <a href="{{ route('provider.services.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">➕ Tambah Jasa</a>
    <a href="{{ route('provider.bookings.index', ['status' => 'pending_confirmation']) }}" class="bg-white border hover:bg-gray-50 font-semibold px-5 py-2.5 rounded-xl text-sm">🔔 Konfirmasi Order</a>
</div>

<div class="bg-white rounded-2xl border">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h3 class="font-semibold">Order Terbaru</h3>
        <a href="{{ route('provider.bookings.index') }}" class="text-sm text-brand-600 font-semibold">Lihat semua →</a>
    </div>
    <div class="divide-y">
        @forelse ($recentBookings as $booking)
            <a href="{{ route('provider.bookings.show', $booking) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate">{{ $booking->item_name }}</p>
                    <p class="text-xs text-gray-400">{{ $booking->code }} · {{ $booking->customer?->name }} · {{ $booking->start_date?->format('d M Y') }}</p>
                </div>
                <span class="text-xs font-medium rounded-full px-3 py-1 {{ booking_badge($booking->status) }}">{{ $booking->status->label() }}</span>
            </a>
        @empty
            <p class="px-6 py-10 text-center text-sm text-gray-400">Belum ada order jasa.</p>
        @endforelse
    </div>
</div>
@endsection
