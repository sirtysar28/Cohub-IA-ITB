@extends('layouts.app')

@section('title', $service->name)

@section('content')
@include('partials.navbar')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route('services.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar jasa</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            @if ($thumb = $service->thumbnail)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" alt="{{ $service->name }}" class="w-full aspect-video object-cover rounded-2xl bg-gray-200">
            @else
                <div class="w-full aspect-video rounded-2xl bg-gray-100 flex items-center justify-center text-6xl text-gray-300">🛠</div>
            @endif

            @if ($service->media->count() > 1)
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                    @foreach ($service->media->skip(1) as $m)
                        <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 border">
                            @if ($m->type === 'video')
                                <video src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover" muted></video>
                            @else
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover hover:scale-105 transition" loading="lazy" alt="Portfolio {{ $loop->index + 2 }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white rounded-2xl border p-6">
                <span class="text-xs font-medium bg-amber-50 text-amber-700 rounded-full px-3 py-1">{{ $service->category?->name }}</span>
                <h1 class="text-2xl font-bold mt-3">{{ $service->name }}</h1>
                <p class="text-gray-500 mt-1">👤 {{ $service->provider?->name }}</p>

                <h2 class="font-semibold mt-6 mb-2">Deskripsi Layanan</h2>
                <p class="text-gray-600 whitespace-pre-line leading-relaxed">{{ $service->description }}</p>

                <h2 class="font-semibold mt-6 mb-2">Jadwal Tersedia (FR-SVC-04)</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @forelse ($service->availabilities as $a)
                        <div class="text-sm bg-green-50 text-green-800 rounded-xl px-3 py-2">
                            <span class="font-semibold">{{ $a->day_name }}</span> — {{ \Carbon\Carbon::parse($a->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($a->end_time)->format('H:i') }}
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Jadwal belum diatur.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="bg-white rounded-2xl border p-6 lg:sticky lg:top-20">
                <h2 class="font-semibold">Harga</h2>
                <p class="mt-2">
                    <span class="text-2xl font-extrabold text-brand-700">{{ format_idr($service->starting_price) }}</span>
                    <span class="text-sm text-gray-400">/ {{ $service->price_unit->label() }}</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">* Harga mulai dari, bisa berubah sesuai kebutuhan.</p>

                @guest
                    <a href="{{ route('login') }}" class="mt-5 block text-center bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition">Login untuk Memesan</a>
                @elseif (auth()->user()->isCustomer())
                    @if ($service->status === 'available')
                        <a href="{{ route('customer.bookings.create-service', $service) }}" class="mt-5 block text-center bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition">Pesan Jasa Ini</a>
                    @else
                        <p class="mt-5 text-center text-sm bg-yellow-50 text-yellow-700 rounded-xl py-3">Jasa sedang tidak tersedia.</p>
                    @endif
                @else
                    <p class="mt-5 text-center text-sm bg-gray-50 text-gray-500 rounded-xl py-3">Hanya customer yang dapat memesan jasa.</p>
                @endif
            </div>
        </aside>
    </div>
</main>
@endsection
