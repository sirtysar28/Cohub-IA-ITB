@extends('layouts.dashboard')

@section('title', 'Jasa Saya')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <h2 class="text-xl font-bold">🛠 Jasa Saya</h2>
    <div class="flex gap-2">
        <form method="GET">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama jasa..." class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        </form>
        <a href="{{ route('provider.services.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-4 py-2 rounded-xl text-sm whitespace-nowrap">➕ Tambah</a>
    </div>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse ($services as $service)
        <div class="bg-white rounded-2xl border overflow-hidden">
            <div class="aspect-video bg-gray-100">
                @if ($thumb = $service->thumbnail)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" class="w-full h-full object-cover" alt="">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-4xl">🛠</div>
                @endif
            </div>
            <div class="p-4 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs bg-amber-50 text-amber-700 rounded-full px-2.5 py-0.5">{{ $service->category?->name }}</span>
                    <span class="text-xs rounded-full px-2.5 py-0.5 {{ verification_badge($service->verification) }}">{{ $service->verification->label() }}</span>
                </div>
                <p class="font-semibold truncate">{{ $service->name }}</p>
                <p class="text-sm font-bold text-brand-700">{{ format_idr($service->starting_price) }} <span class="text-xs font-normal text-gray-400">/ {{ $service->price_unit->label() }}</span></p>
                <p class="text-xs text-gray-400">Hari: {{ $service->availabilities->pluck('day_name')->implode(', ') ?: 'belum diatur' }}</p>
                @if ($service->verification === \App\Enums\VerificationStatus::Rejected && $service->rejection_note)
                    <p class="text-xs bg-red-50 text-red-600 rounded-lg px-3 py-2">Ditolak: {{ $service->rejection_note }}</p>
                @endif
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('provider.services.show', $service) }}" class="flex-1 text-center text-sm border rounded-xl py-2 hover:bg-gray-50">Detail</a>
                    <a href="{{ route('provider.services.edit', $service) }}" class="flex-1 text-center text-sm bg-brand-50 text-brand-700 rounded-xl py-2 hover:bg-brand-100">Edit</a>
                    <form method="POST" action="{{ route('provider.services.destroy', $service) }}" onsubmit="return confirm('Hapus jasa ini?')">
                        @csrf @method('DELETE')
                        <button class="text-sm text-red-600 border border-red-200 rounded-xl px-3 py-2 hover:bg-red-50">🗑</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white border rounded-2xl p-10 text-center text-gray-400">
            Belum ada jasa. <a href="{{ route('provider.services.create') }}" class="text-brand-600 font-semibold">Daftarkan jasa pertama →</a>
        </div>
    @endforelse
</div>

{{ $services->links() }}
@endsection
