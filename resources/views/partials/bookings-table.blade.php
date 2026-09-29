{{-- Tabel booking shared; $bookings + $routeBase --}}
@php
    // Hapus transaksi hanya diarea admin & hanya oleh Super Admin
    $canDelete = ($routeBase ?? '') === 'admin' && auth()->user()?->isSuperAdmin();
@endphp
<div class="bg-white rounded-2xl border overflow-x-auto">
    <table class="w-full text-sm min-w-[760px]">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-5 py-3">Kode</th>
                <th class="px-5 py-3">Item</th>
                <th class="px-5 py-3">Customer</th>
                <th class="px-5 py-3">Tanggal</th>
                <th class="px-5 py-3">Total</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($bookings as $b)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs">{{ $b->code }}</td>
                    <td class="px-5 py-3">
                        <p class="font-semibold">{{ $b->item_name }}</p>
                        <p class="text-xs text-gray-400">{{ $b->type->label() }}</p>
                    </td>
                    <td class="px-5 py-3">{{ $b->customer?->name }}</td>
                    <td class="px-5 py-3">{{ $b->start_date?->format('d M Y') }}{{ $b->end_date ? ' – '.$b->end_date->format('d M Y') : '' }}</td>
                    <td class="px-5 py-3 font-semibold">{{ format_idr($b->total_price) }}</td>
                    <td class="px-5 py-3"><span class="text-xs font-medium rounded-full px-3 py-1 {{ booking_badge($b->status) }}">{{ $b->status->label() }}</span></td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route($routeBase.'.bookings.show', $b) }}"
                           class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-700 font-semibold text-xs hover:underline transition">👁 View Detail</a>

                        @if ($canDelete)
                            <form method="POST" action="{{ route('admin.bookings.destroy', $b) }}" class="inline ml-2"
                                  onsubmit="return confirm('Hapus transaksi {{ $b->code }}?\nRiwayat & pembayaran terkait juga akan dihapus.')">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 font-semibold text-xs hover:underline transition">🗑 Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Belum ada data booking.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $bookings->links() }}
