@extends('layouts.dashboard')

@section('title', 'Verifikasi Jasa')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <h2 class="text-xl font-bold">🛠 Manajemen &amp; Verifikasi Jasa</h2>
    <form method="GET" class="flex gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama jasa..." class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <select name="verification" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Verifikasi</option>
            @foreach (\App\Enums\VerificationStatus::cases() as $v)
                <option value="{{ $v->value }}" {{ request('verification') === $v->value ? 'selected' : '' }}>{{ $v->label() }}</option>
            @endforeach
        </select>
        <button class="bg-brand-600 text-white rounded-xl px-4 py-2 text-sm font-semibold">Filter</button>
    </form>
</div>

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">{{ $errors->first() }}</div>
@endif

<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse ($services as $service)
        <div class="bg-white rounded-2xl border overflow-hidden flex flex-col">
            <div class="aspect-video bg-gray-100">
                @if ($thumb = $service->thumbnail)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" class="w-full h-full object-cover" alt="">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-4xl">🛠</div>
                @endif
            </div>
            <div class="p-4 space-y-2 flex-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs bg-amber-50 text-amber-700 rounded-full px-2.5 py-0.5">{{ $service->category?->name }}</span>
                    <span class="text-xs rounded-full px-2.5 py-0.5 {{ verification_badge($service->verification) }}">{{ $service->verification->label() }}</span>
                </div>
                <p class="font-semibold truncate">{{ $service->name }}</p>
                <p class="text-xs text-gray-400">👤 {{ $service->provider?->name }}</p>
                <p class="text-sm font-bold">{{ format_idr($service->starting_price) }} <span class="text-xs font-normal text-gray-400">/ {{ $service->price_unit->label() }}</span></p>
            </div>

            @if ($service->verification !== \App\Enums\VerificationStatus::Approved)
                <div class="p-4 pt-0 space-y-2">
                    <form method="POST" action="{{ route('admin.services.verify', $service) }}">
                        @csrf
                        <input type="hidden" name="decision" value="approved">
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 rounded-xl">✅ Setujui &amp; Publikasikan</button>
                    </form>
                    <form method="POST" action="{{ route('admin.services.verify', $service) }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <input name="note" placeholder="Alasan penolakan" class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-xs">
                        <button class="bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 rounded-xl">Tolak</button>
                    </form>
                </div>
            @else
                <div class="p-4 pt-0">
                    <form method="POST" action="{{ route('admin.services.verify', $service) }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <input name="note" placeholder="Alasan penarikan publikasi" class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-xs">
                        <button class="border border-red-200 text-red-600 text-xs font-semibold px-3 rounded-xl hover:bg-red-50">Tarik</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="col-span-full bg-white border rounded-2xl p-10 text-center text-gray-400">Tidak ada jasa.</div>
    @endforelse
</div>

{{ $services->links() }}
@endsection
