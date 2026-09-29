@extends('layouts.dashboard')

@section('title', 'Properti Saya')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <h2 class="text-xl font-bold">🏢 Properti Saya</h2>
    <div class="flex gap-2">
        <form method="GET" class="flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama..." class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Enums\PropertyStatus::cases() as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('owner.properties.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-4 py-2 rounded-xl text-sm whitespace-nowrap">➕ Tambah</a>
    </div>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse ($properties as $property)
        <div class="bg-white rounded-2xl border overflow-hidden">
            <div class="aspect-video bg-gray-100">
                @if ($thumb = $property->thumbnail)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" class="w-full h-full object-cover" alt="">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-4xl">🏢</div>
                @endif
            </div>
            <div class="p-4 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs bg-brand-50 text-brand-700 rounded-full px-2.5 py-0.5">{{ $property->category?->name }}</span>
                    <span class="text-xs rounded-full px-2.5 py-0.5 {{ verification_badge($property->verification) }}">{{ $property->verification->label() }}</span>
                </div>
                <p class="font-semibold truncate">{{ $property->name }}</p>
                <p class="text-xs text-gray-400">📍 {{ $property->kota_kabupaten }} · {{ number_format($property->building_size ?? 0) }} m²</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs rounded-full px-2.5 py-0.5 {{ property_badge($property->status) }}">{{ $property->status->label() }}</span>
                    <p class="text-sm font-bold">{{ $property->price ? format_idr($property->price) : '—' }}</p>
                </div>
                @if ($property->verification === \App\Enums\VerificationStatus::Rejected && $property->rejection_note)
                    <p class="text-xs bg-red-50 text-red-600 rounded-lg px-3 py-2">Ditolak: {{ $property->rejection_note }}</p>
                @endif
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('owner.properties.show', $property) }}" class="flex-1 text-center text-sm border rounded-xl py-2 hover:bg-gray-50">Detail</a>
                    <a href="{{ route('owner.properties.edit', $property) }}" class="flex-1 text-center text-sm bg-brand-50 text-brand-700 rounded-xl py-2 hover:bg-brand-100">Edit</a>
                    <form method="POST" action="{{ route('owner.properties.destroy', $property) }}" onsubmit="return confirm('Hapus properti ini?')">
                        @csrf @method('DELETE')
                        <button class="text-sm text-red-600 border border-red-200 rounded-xl px-3 py-2 hover:bg-red-50">🗑</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white border rounded-2xl p-10 text-center text-gray-400">
            Belum ada properti. <a href="{{ route('owner.properties.create') }}" class="text-brand-600 font-semibold">Tambah properti pertama →</a>
        </div>
    @endforelse
</div>

{{ $properties->links() }}
@endsection
