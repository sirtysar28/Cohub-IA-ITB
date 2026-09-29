{{-- Kartu jasa --}}
<a href="{{ route('services.show', $item) }}" class="bg-white rounded-2xl border overflow-hidden hover:shadow-lg transition group">
    <div class="aspect-video bg-gray-200 overflow-hidden">
        @if ($thumb = $item->thumbnail)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" alt="{{ $item->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400 text-4xl">🛠</div>
        @endif
    </div>
    <div class="p-4">
        <span class="text-xs font-medium bg-amber-50 text-amber-700 rounded-full px-2.5 py-0.5">{{ $item->category?->name }}</span>
        <h3 class="font-semibold mt-2 truncate">{{ $item->name }}</h3>
        <p class="text-sm text-gray-500 mt-0.5 truncate">👤 {{ $item->provider?->name }}</p>
        <p class="mt-2 font-bold text-brand-700">
            {{ format_idr($item->starting_price) }}
            <span class="text-xs font-normal text-gray-400">/ {{ $item->price_unit->label() }}</span>
        </p>
    </div>
</a>
