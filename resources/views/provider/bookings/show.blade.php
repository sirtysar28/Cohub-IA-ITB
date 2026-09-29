@extends('layouts.dashboard')

@section('title', 'Detail Booking ' . $booking->code)

@section('content')
<div class="flex items-center justify-between flex-wrap gap-3">
    <div>
        <a href="{{ route('provider.bookings.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali</a>
        <h2 class="text-xl font-bold mt-2">Detail Booking {{ $booking->code }}</h2>
    </div>
    <span class="text-xs font-medium rounded-full px-4 py-1.5 {{ booking_badge($booking->status) }}">{{ $booking->status->label() }}</span>
</div>

@include('partials.booking-detail', ['viewRole' => 'provider'])
@endsection
