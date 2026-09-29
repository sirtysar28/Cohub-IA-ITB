{{-- Pagination clean & smooth (Tailwind) — dipakai via Paginator::defaultView --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-6 select-none">
        <p class="text-xs text-gray-400">
            Menampilkan
            <span class="font-semibold text-gray-600">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-gray-600">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold text-gray-600">{{ number_format($paginator->total()) }}</span> data
        </p>

        <div class="flex items-center gap-1">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 rounded-lg text-xs text-gray-300 cursor-not-allowed">‹ Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition">‹ Sebelumnya</a>
            @endif

            {{-- Window nomor halaman --}}
            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $start + 4);
                $start = max(1, $end - 4);
            @endphp

            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition">1</a>
                @if ($start > 2)<span class="px-1 text-gray-300 text-xs">…</span>@endif
            @endif

            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $current)
                    <span aria-current="page"
                          class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold text-white bg-brand-600 shadow-sm shadow-brand-600/40">{{ $i }}</span>
                @else
                    <a href="{{ $paginator->url($i) }}"
                       class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition">{{ $i }}</a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)<span class="px-1 text-gray-300 text-xs">…</span>@endif
                <a href="{{ $paginator->url($last) }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition">{{ $last }}</a>
            @endif

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition">Berikutnya ›</a>
            @else
                <span class="px-3 py-2 rounded-lg text-xs text-gray-300 cursor-not-allowed">Berikutnya ›</span>
            @endif
        </div>
    </nav>
@endif
