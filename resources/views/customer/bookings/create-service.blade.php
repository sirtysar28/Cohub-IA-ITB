{{-- Form booking jasa (FR-TRX-02): jadwal, custom note, quantity/pax/unit, kalkulasi harga --}}
@extends('layouts.dashboard')

@section('title', 'Pesan Jasa')

@section('content')
<div>
    <a href="{{ route('services.show', $service) }}" class="text-sm text-brand-600 hover:underline">← Kembali ke detail jasa</a>
    <h2 class="text-xl font-bold mt-2">Pesan Jasa</h2>
    <p class="text-sm text-gray-500">{{ $service->name }} — {{ format_idr($service->starting_price) }} / {{ $service->price_unit->label() }}</p>
</div>

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('customer.bookings.store-service', $service) }}#review"
      class="grid lg:grid-cols-3 gap-6 items-start" x-data="{ qty: {{ old('quantity', 1) }} }">
    @csrf

    <div class="lg:col-span-2 bg-white rounded-2xl border p-6 space-y-5">
        <div>
            <label class="block font-semibold text-sm mb-2">1. Tanggal Pelaksanaan * <span class="font-normal text-gray-400 text-xs">(harus sesuai hari tersedia)</span></label>
            <input type="date" name="start_date" min="{{ now()->toDateString() }}" value="{{ old('start_date') }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 sm:w-1/2">
            <p class="text-xs text-gray-400 mt-2 space-x-1">
                Tersedia:
                @forelse ($service->availabilities as $a)
                    <span class="inline-block bg-green-50 text-green-700 rounded px-2 py-0.5">{{ $a->day_name }}</span>
                @empty
                    <span class="text-red-500">belum diatur</span>
                @endforelse
            </p>
        </div>

        @if (in_array($service->price_unit->value, ['per_hour', 'per_day']))
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-sm mb-2">2a. Jam Pelaksanaan</label>
                    <input type="time" name="schedule_time" value="{{ old('schedule_time') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block font-semibold text-sm mb-2">2b. Selesai Periode (opsional)</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
            </div>
        @else
            <div>
                <label class="block font-semibold text-sm mb-2">2. Periode / Selesai (opsional)</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 sm:w-1/2">
            </div>
        @endif

        <div>
            <label class="block font-semibold text-sm mb-2">
                3. Quantity
                <span class="font-normal text-gray-400 text-xs">({{ $service->price_unit->value === 'per_pax' ? 'jumlah orang / pax' : ($service->price_unit->value === 'per_unit' ? 'jumlah unit alat' : 'jumlah order') }})</span>
            </label>
            <input type="number" name="quantity" x-model="qty" min="1" max="1000" required class="w-32 border border-gray-300 rounded-xl px-4 py-2.5">
        </div>

        <div>
            <label class="block font-semibold text-sm mb-2">4. Custom Requirement / Brief</label>
            <textarea name="note" rows="4" placeholder="Jelaskan kebutuhan Anda: desain interior gaya minimalis, 100 pax menu prasmanan, alat bor 1 minggu, dst."
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5">{{ old('note') }}</textarea>
        </div>
    </div>

    {{-- Review + kalkulasi harga --}}
    <aside id="review" class="bg-white rounded-2xl border p-6 lg:sticky lg:top-20">
        <h3 class="font-bold">Price Calculation</h3>
        <div class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-400">Jasa</span><span class="font-semibold text-right">{{ $service->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Penyedia</span><span class="font-semibold">{{ $service->provider?->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Harga satuan</span><span class="font-semibold">{{ format_idr($service->starting_price) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Quantity</span><span class="font-semibold" x-text="qty"></span></div>
            <div class="flex justify-between border-t pt-3 text-base">
                <span class="font-semibold">Estimasi Total</span>
                <span class="font-extrabold text-brand-700" x-text="'Rp ' + ({{ (int) $service->starting_price }} * (qty || 1)).toLocaleString('id-ID')"></span>
            </div>
        </div>

        <div class="mt-5 space-y-2">
            <button name="action" value="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition">Kirim Pemesanan</button>
            <button name="action" value="draft" class="w-full bg-white border border-gray-300 hover:bg-gray-50 font-semibold py-3 rounded-xl transition">Simpan sebagai Draft</button>
        </div>
    </aside>
</form>
@endsection
