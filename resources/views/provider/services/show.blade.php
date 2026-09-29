@extends('layouts.dashboard')

@section('title', 'Detail Jasa')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('provider.services.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali</a>
        <h2 class="text-xl font-bold mt-2">{{ $service->name }}</h2>
        <p class="text-sm text-gray-500">{{ $service->category?->name }} · {{ format_idr($service->starting_price) }} / {{ $service->price_unit->label() }}</p>
    </div>
    <div class="flex gap-2 items-center">
        <span class="text-xs font-medium rounded-full px-3 py-1.5 {{ verification_badge($service->verification) }}">{{ $service->verification->label() }}</span>
        <span class="text-xs font-medium rounded-full px-3 py-1.5 {{ $service->status === 'available' ? status_badge('green') : status_badge('gray') }}">{{ $service->status === 'available' ? 'Tersedia' : 'Tidak Tersedia' }}</span>
        <a href="{{ route('provider.services.edit', $service) }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-1.5 rounded-xl">Edit</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">
        @if ($thumb = $service->thumbnail)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" class="w-full aspect-video object-cover rounded-2xl bg-gray-100" alt="">
        @endif

        @if ($service->media->count() > 1)
            <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                @foreach ($service->media->skip(1) as $m)
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
            <h3 class="font-bold mb-2">Deskripsi Layanan</h3>
            <p class="text-gray-600 whitespace-pre-line text-sm">{{ $service->description }}</p>
        </div>

        <div class="bg-white rounded-2xl border p-6">
            <h3 class="font-bold mb-3">Jadwal Ketersediaan</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @forelse ($service->availabilities as $a)
                    <div class="text-sm bg-green-50 text-green-800 rounded-xl px-3 py-2">
                        <b>{{ $a->day_name }}</b> — {{ \Carbon\Carbon::parse($a->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($a->end_time)->format('H:i') }}
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Jadwal belum diatur.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border">
            <div class="px-6 py-4 border-b"><h3 class="font-bold">Riwayat Order Jasa Ini</h3></div>
            <div class="divide-y text-sm">
                @forelse ($service->bookings->sortByDesc('created_at')->take(5) as $b)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="font-semibold">{{ $b->code }} · {{ $b->customer?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $b->start_date?->format('d M Y') }} · {{ format_idr($b->total_price) }}</p>
                        </div>
                        <span class="text-xs rounded-full px-3 py-1 {{ booking_badge($b->status) }}">{{ $b->status->label() }}</span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-gray-400">Belum ada order.</p>
                @endforelse
            </div>
        </div>
    </div>

    <aside class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold">Harga</h3>
        <p class="text-2xl font-extrabold text-brand-700 mt-1">{{ format_idr($service->starting_price) }}</p>
        <p class="text-xs text-gray-400">per {{ $service->price_unit->label() }} (harga mulai dari)</p>
        <a href="{{ route('services.show', $service) }}" target="_blank" class="block text-center mt-4 text-sm border rounded-xl py-2.5 hover:bg-gray-50">Lihat halaman publik ↗</a>
    </aside>
</div>
@endsection
