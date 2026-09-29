@extends('layouts.dashboard')

@section('title', 'Detail Booking ' . $booking->code)

@section('content')
<div class="flex items-center justify-between flex-wrap gap-3">
    <div>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali</a>
        <h2 class="text-xl font-bold mt-2">Detail Booking {{ $booking->code }}</h2>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs font-medium rounded-full px-4 py-1.5 {{ booking_badge($booking->status) }}">{{ $booking->status->label() }}</span>
        @if (auth()->user()->isSuperAdmin())
            <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}"
                  onsubmit="return confirm('Hapus transaksi {{ $booking->code }}?\nRiwayat & pembayaran terkait juga akan dihapus.')">
                @csrf @method('DELETE')
                <button class="text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 rounded-lg px-3 py-2 transition">🗑 Hapus Transaksi</button>
            </form>
        @endif
    </div>
</div>

@include('partials.booking-detail', ['viewRole' => 'admin'])
@endsection
