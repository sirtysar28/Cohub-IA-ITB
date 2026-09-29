{{-- Kartu properti --}}
<a href="{{ route('properties.show', $item) }}" class="bg-white rounded-2xl border overflow-hidden hover:shadow-lg transition group">
    <div class="aspect-video bg-gray-200 overflow-hidden">
        @if ($thumb = $item->thumbnail)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" alt="{{ $item->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400 text-4xl">🏢</div>
        @endif
    </div>
    <div class="p-4">
        <span class="text-xs font-medium bg-brand-50 text-brand-700 rounded-full px-2.5 py-0.5">{{ $item->category?->name }}</span>
        <h3 class="font-semibold mt-2 truncate">{{ $item->name }}</h3>
        <p class="text-sm text-gray-500 mt-0.5 truncate">📍 {{ $item->kota_kabupaten ?? '-' }}</p>
        <p class="text-sm text-gray-500">{{ number_format($item->building_size ?? 0, 0, ',', '.') }} m² bangunan</p>
        <p class="mt-2 font-bold text-brand-700">
            @if ($item->price)
                {{ format_idr($item->price) }} <span class="text-xs font-normal text-gray-400">mulai</span>
            @else
                Hubungi pemilik
            @endif
        </p>
        @if ($item->status->value === 'rented')
            <span class="inline-block mt-2 text-xs bg-blue-100 text-blue-700 rounded-full px-2.5 py-0.5">Sedang Disewa</span>
        @endif
    </div>
</a>
