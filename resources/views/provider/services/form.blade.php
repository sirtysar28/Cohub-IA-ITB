{{-- Form tambah/edit jasa: kategori → detail → portfolio → availability Senin-Sabtu --}}
@extends('layouts.dashboard')

@php $isEdit = $service->exists; @endphp

@section('title', $isEdit ? 'Edit Jasa' : 'Tambah Jasa')

@section('content')
<div>
    <a href="{{ route('provider.services.index') }}" class="text-sm text-brand-600 hover:underline">← Kembali</a>
    <h2 class="text-xl font-bold mt-2">{{ $isEdit ? 'Edit: '.$service->name : 'Daftarkan Jasa Baru' }}</h2>
    @unless ($isEdit)
        <p class="text-sm text-gray-500">Jasa baru akan diverifikasi admin sebelum dipublikasikan.</p>
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
      action="{{ $isEdit ? route('provider.services.update', $service) : route('provider.services.store') }}"
      class="grid lg:grid-cols-3 gap-6 items-start">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="lg:col-span-2 space-y-6">
        {{-- Detail jasa --}}
        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <h3 class="font-bold">1. Detail Jasa</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1">Nama Jasa *</label>
                    <input name="name" value="{{ old('name', $service->name) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                        <option value="">— Pilih Kategori —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id', $service->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status Layanan *</label>
                    <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                        <option value="available" {{ old('status', $service->status ?? 'available') === 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="unavailable" {{ old('status', $service->status ?? '') === 'unavailable' ? 'selected' : '' }}>Tidak Tersedia</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Harga Mulai Dari (Rp) *</label>
                    <input type="number" min="0" step="0.01" name="starting_price" value="{{ old('starting_price', $service->starting_price) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Satuan Harga *</label>
                    <select name="price_unit" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                        @foreach (\App\Enums\PriceUnit::cases() as $u)
                            <option value="{{ $u->value }}" {{ old('price_unit', $service->price_unit?->value ?? 'per_project') === $u->value ? 'selected' : '' }}>{{ $u->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi Layanan *</label>
                <textarea name="description" rows="5" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5">{{ old('description', $service->description) }}</textarea>
            </div>
        </div>

        {{-- Availability Senin-Sabtu --}}
        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <h3 class="font-bold">2. Atur Ketersediaan Jadwal <span class="text-xs font-normal text-gray-400">(Senin–Sabtu, 00:00–24:00)</span></h3>
            <div class="space-y-2">
                @foreach ($days as $day => $dayName)
                    @php $avail = $isEdit ? $service->availabilities->firstWhere('day_of_week', $day) : null; @endphp
                    <div class="flex flex-wrap items-center gap-3 bg-gray-50 rounded-xl px-4 py-2.5">
                        <label class="flex items-center gap-2 w-28 text-sm font-medium">
                            <input type="checkbox" name="availability[{{ $loop->index }}][enabled]" value="1"
                                   data-day-row="{{ $day }}"
                                   {{ old("availability.$loop->index.enabled", $avail ? '1' : null) ? 'checked' : '' }}
                                   class="rounded border-gray-300">
                            {{ $dayName }}
                        </label>
                        <input type="hidden" name="availability[{{ $loop->index }}][day]" value="{{ $day }}">
                        <input type="time" name="availability[{{ $loop->index }}][start_time]" value="{{ old("availability.$loop->index.start_time", $avail?->start_time ?? '08:00') }}"
                               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <span class="text-gray-400 text-sm">s.d.</span>
                        <input type="time" name="availability[{{ $loop->index }}][end_time]" value="{{ old("availability.$loop->index.end_time", $avail?->end_time ?? '17:00') }}"
                               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400">Hari yang tidak dicentang tidak akan tersedia untuk dipesan customer.</p>
        </div>

        {{-- Portfolio --}}
        <div class="bg-white rounded-2xl border p-6 space-y-4">
            <h3 class="font-bold">3. Portfolio / Contoh Pekerjaan</h3>
            <p class="text-xs text-gray-400">Maksimal {{ $maxMedia }} gambar/video. <b>File pertama otomatis menjadi thumbnail.</b></p>

            @if ($isEdit && $service->media->count())
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                    @foreach ($service->media as $m)
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
                            <a href="{{ route('provider.services.media.destroy', [$service, $m]) }}"
                               onclick="event.preventDefault(); if(confirm('Hapus media ini?')) { var f=document.createElement('form'); f.method='POST'; f.action=this.href; f.innerHTML='@csrf<input type=hidden name=_method value=DELETE>'; document.body.appendChild(f); f.submit(); }"
                               class="absolute top-1 right-1 bg-red-600 text-white text-[10px] w-5 h-5 rounded-full hidden group-hover:flex items-center justify-center">✕</a>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400">Slot tersisa: {{ $maxMedia - $service->media->count() }}</p>
            @endif

            <input type="file" name="media_new[]" multiple accept="image/*,video/mp4,video/webm"
                   class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-6 text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700">
        </div>
    </div>

    <aside class="bg-white rounded-2xl border p-6 lg:sticky lg:top-20 space-y-4">
        <h3 class="font-bold">Simpan</h3>
        @if ($isEdit)
            <p class="text-sm {{ $service->verification->color() === 'green' ? 'text-green-600' : 'text-yellow-600' }}">
                Status verifikasi: <b>{{ $service->verification->label() }}</b>
            </p>
        @endif
        <button class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl">
            {{ $isEdit ? 'Simpan Perubahan' : 'Daftarkan Jasa' }}
        </button>
        <a href="{{ route('provider.services.index') }}" class="block text-center text-sm text-gray-500 py-2">Batal</a>
    </aside>
</form>

<script>
    // Nonaktifkan input jam untuk hari yang tidak dicentang
    document.querySelectorAll('[data-day-row]').forEach(cb => {
        const row = cb.closest('div');
        const toggle = () => {
            row.querySelectorAll('input[type=time]').forEach(t => t.disabled = !cb.checked);
        };
        cb.addEventListener('change', toggle);
        toggle();
    });
</script>
@endsection
