@extends('layouts.dashboard')

@section('title', 'Booking Saya')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <h2 class="text-xl font-bold">🧾 Booking Saya</h2>
    <form method="GET" class="flex gap-2">
        <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            @foreach (\App\Enums\BookingStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-2xl border overflow-x-auto">
    <table class="w-full text-sm min-w-[720px]">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-5 py-3">Kode</th>
                <th class="px-5 py-3">Item</th>
                <th class="px-5 py-3">Tanggal</th>
                <th class="px-5 py-3">Total</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($bookings as $b)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs">{{ $b->code }}</td>
                    <td class="px-5 py-3">
                        <p class="font-semibold">{{ $b->item_name }}</p>
                        <p class="text-xs text-gray-400">{{ $b->type->label() }}</p>
                    </td>
                    <td class="px-5 py-3">{{ $b->start_date?->format('d M Y') }}{{ $b->end_date ? ' – '.$b->end_date->format('d M Y') : '' }}</td>
                    <td class="px-5 py-3 font-semibold">{{ format_idr($b->total_price) }}</td>
                    <td class="px-5 py-3"><span class="text-xs font-medium rounded-full px-3 py-1 {{ booking_badge($b->status) }}">{{ $b->status->label() }}</span></td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('customer.bookings.show', $b) }}" class="text-brand-600 font-semibold text-xs hover:underline">Detail →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada booking.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $bookings->links() }}
@endsection
