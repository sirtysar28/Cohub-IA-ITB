@extends('layouts.app')

@section('title', 'Cari Properti')

@section('content')
@include('partials.navbar')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-bold">🏢 Daftar Properti</h1>
    <p class="text-sm text-gray-500 mt-1">Sewa tempat tinggal, ritel, gudang, dan kantor.</p>

    {{-- Filter (FR-RNT-01: lokasi, tipe, rentang harga) --}}
    <form method="GET" class="mt-6 bg-white rounded-2xl border p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
        <div class="lg:col-span-2">
            <label class="text-xs font-medium text-gray-500">Kata Kunci</label>
            <input name="q" value="{{ request('q') }}" placeholder="Nama properti / deskripsi" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Lokasi (Kota)</label>
            <input name="kota" value="{{ request('kota') }}" placeholder="cth: Surabaya" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Kategori</label>
            <select name="kategori" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" {{ request('kategori') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Harga Min</label>
            <input type="number" name="harga_min" value="{{ request('harga_min') }}" placeholder="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <div class="flex-1">
                <label class="text-xs font-medium text-gray-500">Harga Maks</label>
                <input type="number" name="harga_max" value="{{ request('harga_max') }}" placeholder="∞" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
            </div>
            <button class="bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-4 py-2 text-sm font-semibold h-[38px]">Filter</button>
        </div>
    </form>

    <p class="text-sm text-gray-400 mt-4">{{ $properties->total() }} properti ditemukan</p>

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @forelse ($properties as $property)
            @include('partials.property-card', ['item' => $property])
        @empty
            <div class="col-span-full bg-white border rounded-2xl p-10 text-center text-gray-400">
                Tidak ada properti yang cocok dengan pencarian Anda.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $properties->links() }}</div>
</main>
@endsection
