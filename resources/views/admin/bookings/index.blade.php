@extends('layouts.dashboard')

@section('title', 'Manajemen Transaksi')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <h2 class="text-xl font-bold">🧾 Manajemen Transaksi</h2>
    <form method="GET" class="flex flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Kode / nama customer" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <select name="type" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Tipe</option>
            <option value="property" {{ request('type') === 'property' ? 'selected' : '' }}>Properti</option>
            <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>Jasa</option>
        </select>
        <select name="status" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            @foreach (\App\Enums\BookingStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
        <button class="bg-brand-600 text-white rounded-xl px-4 py-2 text-sm font-semibold">Filter</button>
    </form>
</div>

@include('partials.bookings-table', ['routeBase' => 'admin'])
@endsection
