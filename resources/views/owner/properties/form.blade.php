{{-- Form tambah/edit properti: kategori → info → media (max 5, pertama thumbnail) → harga → status --}}
@extends('layouts.dashboard')

@php $isEdit = $property->exists; @endphp

@section('title', $isEdit ? 'Edit Properti' : 'Tambah Properti')

@section('content')
<div>
    <a href="{{ route('owner.properties.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali</a>
    <h2 class="text-xl font-bold mt-2">{{ $isEdit ? 'Edit: '.$property->name : 'Tambah Properti Baru' }}</h2>
    @unless ($isEdit)
        <p class="text-sm text-gray-500">Properti baru akan diverifikasi admin sebelum dipublikasikan.</p>
    @endunless
</div>

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" enctype="multipart/form-data"
      action="{{ $isEdit ? route('owner.properties.update', $property) : route('owner.properties.store') }}"
      class="grid lg:grid-cols-3 gap-6 items-start">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="lg:col-span-2 space-y-6">
        {{-- Informasi dasar --}}
        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <h3 class="font-bold">1. Informasi Properti</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1">Nama Properti *</label>
                    <input name="name" value="{{ old('name', $property->name) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                        <option value="">— Pilih Kategori —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id', $property->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status Ketersediaan *</label>
                    <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                        @foreach (\App\Enums\PropertyStatus::cases() as $s)
                            <option value="{{ $s->value }}" {{ old('status', $property->status->value ?? 'available') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Luas Tanah (m²)</label>
                    <input type="number" step="0.01" name="land_size" value="{{ old('land_size', $property->land_size) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Luas Bangunan (m²)</label>
                    <input type="number" step="0.01" name="building_size" value="{{ old('building_size', $property->building_size) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi *</label>
                <textarea name="description" rows="5" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5">{{ old('description', $property->description) }}</textarea>
            </div>
        </div>

        {{-- Lokasi --}}
        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <h3 class="font-bold">2. Lokasi</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <input name="provinsi" value="{{ old('provinsi', $property->provinsi) }}" required placeholder="Provinsi *" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                <input name="kota_kabupaten" value="{{ old('kota_kabupaten', $property->kota_kabupaten) }}" required placeholder="Kota/Kabupaten *" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                <input name="kecamatan" value="{{ old('kecamatan', $property->kecamatan) }}" placeholder="Kecamatan" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                <input name="address" value="{{ old('address', $property->address) }}" required placeholder="Alamat lengkap *" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
        </div>

        {{-- Harga multi periode --}}
        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <h3 class="font-bold">3. Harga Sewa * <span class="text-xs font-normal text-gray-400">(boleh lebih dari satu periode)</span></h3>
            <div class="grid sm:grid-cols-3 gap-4">
                @foreach (['daily' => 'Per Hari', 'monthly' => 'Per Bulan', 'yearly' => 'Per Tahun'] as $value => $label)
                    @php $existing = $isEdit ? $property->prices->firstWhere('period.value', $value) : null; @endphp
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}</label>
                        <input type="number" min="0" step="0.01" name="prices[{{ $loop->index }}][price]"
                               value="{{ old("prices.$loop->index.price", $existing?->price) }}"
                               placeholder="Rp" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                        <input type="hidden" name="prices[{{ $loop->index }}][period]" value="{{ $value }}">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Media --}}
        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <h3 class="font-bold">4. Upload Media</h3>
            <p class="text-xs text-gray-400">Maksimal {{ $maxMedia }} gambar/video. <b>File pertama otomatis menjadi thumbnail.</b> Gambar dikompresi otomatis. (JPG/PNG/WebP/GIF/MP4, maks 10MB)</p>

            @if ($isEdit && $property->media->count())
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                    @foreach ($property->media as $m)
                        <div class="relative group">
                            <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 border">
                                @if ($m->type === 'video')
                                    <video src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover" muted></video>
                                @else
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($m->path) }}" class="w-full h-full object-cover" alt="">
                                @endif
                            </div>
                            @if ($m->is_thumbnail)
                                <span class="absolute top-1 left-1 bg-brand-600 text-white text-[10px] px-2 py-0.5 rounded-full">Thumbnail</span>
                            @endif
                            <a href="{{ route('owner.properties.media.destroy', [$property, $m]) }}"
                               onclick="event.preventDefault(); if(confirm('Hapus media ini?')) { var f=document.createElement('form'); f.method='POST'; f.action=this.href; f.innerHTML='@csrf<input type=hidden name=_method value=DELETE>'; document.body.appendChild(f); f.submit(); }"
                               class="absolute top-1 right-1 bg-red-600 text-white text-[10px] w-5 h-5 rounded-full hidden group-hover:flex items-center justify-center">✕</a>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400">Slot tersisa: {{ $maxMedia - $property->media->count() }}</p>
            @endif

            <input type="file" name="media_new[]" multiple accept="image/*,video/mp4,video/webm"
                   class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-6 text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700">
        </div>
    </div>

    {{-- Sidebar submit --}}
    <aside class="bg-white rounded-2xl border p-6 lg:sticky lg:top-20 space-y-4">
        <h3 class="font-bold">Simpan</h3>
        @if ($isEdit)
            <p class="text-sm {{ $property->verification->color() === 'green' ? 'text-green-600' : 'text-yellow-600' }}">
                Status verifikasi: <b>{{ $property->verification->label() }}</b>
            </p>
        @endif
        <button class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl">
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Properti' }}
        </button>
        <a href="{{ route('owner.properties.index') }}" class="block text-center text-sm text-gray-500 py-2">Batal</a>
    </aside>
</form>
@endsection
