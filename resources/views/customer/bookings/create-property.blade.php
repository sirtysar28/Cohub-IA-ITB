{{-- Form booking properti (FR-TRX-01): tanggal, tujuan penggunaan, custom note --}}
@extends('layouts.dashboard')

@section('title', 'Sewa Properti')

@section('content')
<div>
    <a href="{{ route('properties.show', $property) }}" class="text-sm text-brand-600 hover:underline">← Kembali ke detail properti</a>
    <h2 class="text-xl font-bold mt-2">Ajukan Sewa Properti</h2>
    <p class="text-sm text-gray-500">{{ $property->name }} — {{ $property->kota_kabupaten }}</p>
</div>

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('customer.bookings.store-property', $property) }}#review"
      class="grid lg:grid-cols-3 gap-6 items-start" x-data="{ period: '{{ old('price_period', $property->prices->first()?->period->value) }}', start: '{{ old('start_date') }}', end: '{{ old('end_date') }}' }">
    @csrf

    <div class="lg:col-span-2 bg-white rounded-2xl border p-6 space-y-5">
        {{-- Alur: Pilih tanggal → tujuan → catatan → review → submit --}}
        <div>
            <label class="block font-semibold text-sm mb-2">1. Pilih Periode Harga *</label>
            <div class="grid grid-cols-3 gap-3">
                @foreach ($property->prices as $price)
                    <label class="border-2 rounded-xl p-3 text-center cursor-pointer transition text-sm"
                           :class="period === '{{ $price->period->value }}' ? 'border-brand-600 bg-brand-50 font-semibold' : 'border-gray-200'">
                        <input type="radio" name="price_period" value="{{ $price->period->value }}" x-model="period" class="sr-only" {{ $loop->first ? 'checked' : '' }} required>
                        <p>{{ $price->period->label() }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ format_idr($price->price) }}</p>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-sm mb-2">2a. Tanggal Mulai *</label>
                <input type="date" name="start_date" x-model="start" min="{{ now()->toDateString() }}" value="{{ old('start_date') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <div>
                <label class="block font-semibold text-sm mb-2">2b. Tanggal Selesai *</label>
                <input type="date" name="end_date" x-model="end" min="{{ now()->toDateString() }}" value="{{ old('end_date') }}" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-sm mb-2">3. Tujuan Penggunaan Properti *</label>
            <textarea name="purpose" rows="3" required placeholder="Contoh: Digunakan untuk kantor cabang, acara komunitas, gudang logistik..."
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5">{{ old('purpose') }}</textarea>
        </div>

        <div>
            <label class="block font-semibold text-sm mb-2">4. Catatan Khusus (opsional)</label>
            <textarea name="note" rows="3" placeholder="Kebutuhan tambahan, permintaan fasilitas, dll."
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5">{{ old('note') }}</textarea>
        </div>
    </div>

    {{-- Review Booking --}}
    <aside id="review" class="bg-white rounded-2xl border p-6 lg:sticky lg:top-20">
        <h3 class="font-bold">Review Booking</h3>
        <div class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-400">Properti</span><span class="font-semibold text-right">{{ $property->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Pemilik</span><span class="font-semibold">{{ $property->owner?->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Periode</span><span class="font-semibold"><span x-text="period === 'daily' ? 'Per Hari' : period === 'monthly' ? 'Per Bulan' : 'Per Tahun'"></span></span></div>
            <div class="flex justify-between"><span class="text-gray-400">Mulai</span><span class="font-semibold" x-text="start || '-'"></span></div>
            <div class="flex justify-between"><span class="text-gray-400">Selesai</span><span class="font-semibold" x-text="end || '-'"></span></div>
            <p class="text-xs text-gray-400 border-t pt-3">Total harga final akan dikonfirmasi pemilik. Estimasi dihitung dari periode &amp; durasi.</p>
        </div>

        <div class="mt-5 space-y-2">
            <button name="action" value="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition">Ajukan Sewa Sekarang</button>
            <button name="action" value="draft" class="w-full bg-white border border-gray-300 hover:bg-gray-50 font-semibold py-3 rounded-xl transition">Simpan sebagai Draft</button>
        </div>
    </aside>
</form>
@endsection
