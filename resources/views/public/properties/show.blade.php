@extends('layouts.app')

@section('title', $property->name)

@section('content')
@include('partials.navbar')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route('properties.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar properti</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-8">
        {{-- Galeri + info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Thumbnail utama --}}
            @if ($thumb = $property->thumbnail)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" alt="{{ $property->name }}" class="w-full aspect-video object-cover rounded-2xl bg-gray-200">
            @else
                <div class="w-full aspect-video rounded-2xl bg-gray-100 flex items-center justify-center text-6xl text-gray-300">🏢</div>
            @endif

            {{-- Galeri --}}
            @if ($property->media->count() > 1)
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                    @foreach ($property->media->skip(1) as $m)
                        <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 border">
                            @if ($m->type === 'video')
                                <video src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover" muted></video>
                            @else
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover hover:scale-105 transition" loading="lazy" alt="Galeri {{ $loop->index + 2 }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white rounded-2xl border p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium bg-brand-50 text-brand-700 rounded-full px-3 py-1">{{ $property->category?->name }}</span>
                    <span class="text-xs font-medium rounded-full px-3 py-1 {{ property_badge($property->status) }}">{{ $property->status->label() }}</span>
                </div>
                <h1 class="text-2xl font-bold mt-3">{{ $property->name }}</h1>
                <p class="text-gray-500 mt-1">📍 {{ $property->address }}, {{ $property->kecamatan ? $property->kecamatan.', ' : '' }}{{ $property->kota_kabupaten }}, {{ $property->provinsi }}</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 text-center">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Luas Tanah</p>
                        <p class="font-bold">{{ number_format($property->land_size ?? 0, 0, ',', '.') }} m²</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Luas Bangunan</p>
                        <p class="font-bold">{{ number_format($property->building_size ?? 0, 0, ',', '.') }} m²</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Kategori</p>
                        <p class="font-bold">{{ $property->category?->name }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Pemilik</p>
                        <p class="font-bold truncate">{{ $property->owner?->name }}</p>
                    </div>
                </div>

                <h2 class="font-semibold mt-6 mb-2">Deskripsi</h2>
                <p class="text-gray-600 whitespace-pre-line leading-relaxed">{{ $property->description }}</p>
            </div>
        </div>

        {{-- Panel harga + booking --}}
        <aside class="space-y-4">
            <div class="bg-white rounded-2xl border p-6 lg:sticky lg:top-20">
                <h2 class="font-semibold">Harga Sewa</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($property->prices as $price)
                        <div class="flex justify-between text-sm bg-gray-50 rounded-xl px-4 py-2.5">
                            <span class="text-gray-500">{{ $price->period->label() }}</span>
                            <span class="font-bold">{{ format_idr($price->price) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Harga belum diatur.</p>
                    @endforelse
                </div>

                @guest
                    <a href="{{ route('login') }}" class="mt-5 block text-center bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition">Login untuk Menyewa</a>
                @elseif (auth()->user()->isCustomer())
                    @if ($property->status->value === 'available')
                        <a href="{{ route('customer.bookings.create-property', $property) }}" class="mt-5 block text-center bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition">Sewa Sekarang</a>
                    @else
                        <p class="mt-5 text-center text-sm bg-yellow-50 text-yellow-700 rounded-xl py-3">Properti sedang tidak tersedia.</p>
                    @endif
                @else
                    <p class="mt-5 text-center text-sm bg-gray-50 text-gray-500 rounded-xl py-3">Hanya customer yang dapat melakukan penyewaan.</p>
                @endif
            </div>
        </aside>
    </div>
</main>
@endsection
