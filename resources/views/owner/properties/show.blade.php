@extends('layouts.dashboard')

@section('title', 'Detail Properti')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('owner.properties.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali</a>
        <h2 class="text-xl font-bold mt-2">{{ $property->name }}</h2>
        <p class="text-sm text-gray-500">📍 {{ $property->address }}, {{ $property->kota_kabupaten }}</p>
    </div>
    <div class="flex gap-2">
        <span class="text-xs font-medium rounded-full px-3 py-1.5 {{ verification_badge($property->verification) }}">{{ $property->verification->label() }}</span>
        <span class="text-xs font-medium rounded-full px-3 py-1.5 {{ property_badge($property->status) }}">{{ $property->status->label() }}</span>
        <a href="{{ route('owner.properties.edit', $property) }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-1.5 rounded-xl">Edit</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">
        @if ($thumb = $property->thumbnail)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" class="w-full aspect-video object-cover rounded-2xl bg-gray-100" alt="">
        @endif

        @if ($property->media->count() > 1)
            <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                @foreach ($property->media->skip(1) as $m)
                    <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 border">
                        @if ($m->type === 'video')
                            <video src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover" muted></video>
                        @else
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover" alt="">
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl border p-6">
            <h3 class="font-bold mb-3">Informasi</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-sm">
                <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-400">Kategori</p><p class="font-bold">{{ $property->category?->name }}</p></div>
                <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-400">Luas Tanah</p><p class="font-bold">{{ number_format($property->land_size ?? 0) }} m²</p></div>
                <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-400">Luas Bangunan</p><p class="font-bold">{{ number_format($property->building_size ?? 0) }} m²</p></div>
                <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-400">Total Booking</p><p class="font-bold">{{ $property->bookings->count() }}</p></div>
            </div>
            <h4 class="font-semibold mt-5 mb-1">Deskripsi</h4>
            <p class="text-gray-600 whitespace-pre-line text-sm">{{ $property->description }}</p>
        </div>

        <div class="bg-white rounded-2xl border">
            <div class="px-6 py-4 border-b"><h3 class="font-bold">Riwayat Booking Properti Ini</h3></div>
            <div class="divide-y text-sm">
                @forelse ($property->bookings->sortByDesc('created_at')->take(5) as $b)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="font-semibold">{{ $b->code }} · {{ $b->customer?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $b->start_date?->format('d M Y') }} · {{ format_idr($b->total_price) }}</p>
                        </div>
                        <span class="text-xs rounded-full px-3 py-1 {{ booking_badge($b->status) }}">{{ $b->status->label() }}</span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-gray-400">Belum ada booking.</p>
                @endforelse
            </div>
        </div>
    </div>

    <aside class="bg-white rounded-2xl border p-6 space-y-3">
        <h3 class="font-bold">Harga Sewa</h3>
        @forelse ($property->prices as $price)
            <div class="flex justify-between text-sm bg-gray-50 rounded-xl px-4 py-2.5">
                <span class="text-gray-500">{{ $price->period->label() }}</span>
                <span class="font-bold">{{ format_idr($price->price) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Belum ada harga.</p>
        @endforelse
        <a href="{{ route('properties.show', $property) }}" target="_blank" class="block text-center mt-4 text-sm border rounded-xl py-2.5 hover:bg-gray-50">Lihat halaman publik ↗</a>
    </aside>
</div>
@endsection
