@extends('layouts.dashboard')

@section('title', 'Order Jasa')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <h2 class="text-xl font-bold">🧾 Order Jasa</h2>
    <form method="GET" class="flex gap-2">
        <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            @foreach (\App\Enums\BookingStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
    </form>
</div>

@include('partials.bookings-table', ['routeBase' => 'provider'])
@endsection
