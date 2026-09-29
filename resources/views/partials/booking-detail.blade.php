{{-- Detail booking shared + tombol aksi sesuai role --}}
@php
    $user = auth()->user();
    $isCustomerView = $viewRole === 'customer';
    $isPartnerView = in_array($viewRole, ['owner', 'provider']);
@endphp

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">
        {{-- Ringkasan item --}}
        <div class="bg-white rounded-2xl border p-6">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-medium bg-brand-50 text-brand-700 rounded-full px-3 py-1">{{ $booking->type->label() }}</span>
                <span class="text-xs font-medium rounded-full px-3 py-1 {{ booking_badge($booking->status) }}">{{ $booking->status->label() }}</span>
            </div>

            <div class="mt-4 flex gap-4">
                @if ($thumb = $booking->type->value === 'property' ? $booking->property?->thumbnail : $booking->service?->thumbnail)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" class="w-28 h-28 rounded-xl object-cover" alt="">
                @else
                    <div class="w-28 h-28 rounded-xl bg-gray-100 flex items-center justify-center text-4xl">{{ $booking->type->value === 'property' ? '🏢' : '🛠' }}</div>
                @endif
                <div class="min-w-0">
                    <h2 class="text-lg font-bold">{{ $booking->item_name }}</h2>
                    <p class="text-sm text-gray-500">Kategori: {{ ($booking->property ?? $booking->service)?->category?->name ?? '-' }}</p>
                    <p class="text-sm text-gray-500">{{ $booking->type->value === 'property' ? 'Pemilik' : 'Penyedia' }}: {{ $booking->partner?->name }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-3 mt-6 text-sm">
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    <p class="text-xs text-gray-400">Kode Booking</p>
                    <p class="font-mono font-semibold">{{ $booking->code }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    <p class="text-xs text-gray-400">Customer</p>
                    <p class="font-semibold">{{ $booking->customer?->name }} ({{ $booking->customer?->whatsapp }})</p>
                </div>
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    <p class="text-xs text-gray-400">Tanggal</p>
                    <p class="font-semibold">
                        {{ $booking->start_date?->format('d M Y') }}{{ $booking->end_date ? ' – '.$booking->end_date->format('d M Y') : '' }}
                        @if ($booking->type->value === 'property' && $booking->duration_days)
                            <span class="text-gray-400">({{ $booking->duration_days }} hari)</span>
                        @endif
                    </p>
                </div>
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    @if ($booking->schedule_time)
                        <p class="text-xs text-gray-400">Jam Pelaksanaan</p>
                        <p class="font-semibold">{{ $booking->schedule_time->format('H:i') }}</p>
                    @else
                        <p class="text-xs text-gray-400">Satuan Harga</p>
                        <p class="font-semibold">{{ $booking->type->value === 'property' ? \App\Enums\PricePeriod::from($booking->price_period)->label() : $booking->service?->price_unit?->label() }}</p>
                    @endif
                </div>
                @if ($booking->type->value === 'service')
                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <p class="text-xs text-gray-400">Quantity</p>
                        <p class="font-semibold">{{ $booking->quantity }} {{ $booking->service?->price_unit->value === 'per_pax' ? 'pax' : 'unit' }}</p>
                    </div>
                @endif
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    <p class="text-xs text-gray-400">Harga Satuan</p>
                    <p class="font-semibold">{{ format_idr($booking->unit_price) }}</p>
                </div>
            </div>

            @if ($booking->purpose)
                <div class="mt-4">
                    <p class="text-xs text-gray-400">Tujuan Penggunaan</p>
                    <p class="text-sm bg-yellow-50 border border-yellow-100 rounded-xl px-4 py-3 mt-1 whitespace-pre-line">{{ $booking->purpose }}</p>
                </div>
            @endif

            @if ($booking->note)
                <div class="mt-4">
                    <p class="text-xs text-gray-400">Custom Note / Brief</p>
                    <p class="text-sm bg-brand-50 border border-brand-100 rounded-xl px-4 py-3 mt-1 whitespace-pre-line">{{ $booking->note }}</p>
                </div>
            @endif
        </div>

        {{-- Riwayat status --}}
        <div class="bg-white rounded-2xl border p-6">
            <h3 class="font-bold mb-4">Riwayat Status</h3>
            <ol class="relative border-l-2 border-gray-100 ml-3 space-y-6">
                @foreach ($booking->histories->reverse()->values() as $h)
                    <li class="ml-5">
                        <span class="absolute -left-[9px] w-4 h-4 rounded-full {{ $loop->last ? 'bg-brand-600' : 'bg-gray-300' }} border-2 border-white"></span>
                        <p class="text-sm font-semibold">
                            {{ \App\Enums\BookingStatus::from($h->to_status->value)->label() }}
                            <span class="font-normal text-gray-400 text-xs">· {{ $h->created_at->format('d M Y H:i') }}</span>
                        </p>
                        @if ($h->note)<p class="text-xs text-gray-500 mt-0.5">{{ $h->note }}</p>@endif
                        <p class="text-xs text-gray-400">oleh {{ $h->actor?->name ?? 'Sistem' }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    {{-- Panel kanan: total + pembayaran + aksi --}}
    <aside class="space-y-4">
        <div class="bg-white rounded-2xl border p-6">
            <p class="text-xs text-gray-400">Total Harga</p>
            <p class="text-3xl font-extrabold text-brand-700 mt-1">{{ format_idr($booking->total_price) }}</p>

            @if ($booking->payment)
                <div class="mt-4 text-sm bg-green-50 rounded-xl px-4 py-3">
                    <p class="font-semibold">{{ $booking->payment->status->label() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Invoice {{ $booking->payment->invoice_no }} · {{ ucfirst($booking->payment->method) }}</p>
                    @if ($booking->payment->paid_at)
                        <p class="text-xs text-gray-500">Dibayar {{ $booking->payment->paid_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- ====== AKSI CUSTOMER ====== --}}
        @if ($isCustomerView)
            @if ($booking->status === \App\Enums\BookingStatus::Draft)
                <form method="POST" action="{{ route('customer.bookings.submit', $booking) }}">
                    @csrf
                    <button class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl">Ajukan Sewa (Kirim ke Pemilik/Penyedia)</button>
                </form>
            @endif

            @if ($booking->status === \App\Enums\BookingStatus::Approved)
                <form method="POST" action="{{ route('customer.bookings.pay', $booking) }}" class="bg-white rounded-2xl border p-5 space-y-3"
                      x-data="{ method: 'transfer' }">
                    @csrf
                    <p class="font-semibold text-sm">Pembayaran</p>
                    <select name="method" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <option value="transfer">Transfer Bank (simulasi)</option>
                        <option value="cash">Tunai / Bayar di Tempat</option>
                    </select>
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl">💰 Bayar Sekarang</button>
                </form>
            @endif

            @if (in_array($booking->status, [\App\Enums\BookingStatus::Draft, \App\Enums\BookingStatus::PendingConfirmation, \App\Enums\BookingStatus::Approved, \App\Enums\BookingStatus::Paid]))
                <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}"
                      onsubmit="return confirm('Yakin batalkan booking ini?')">
                    @csrf
                    <button class="w-full bg-white border border-red-200 text-red-600 hover:bg-red-50 font-semibold py-3 rounded-xl">Batalkan Booking</button>
                </form>
            @endif
        @endif

        {{-- ====== AKSI OWNER / PROVIDER ====== --}}
        @if ($isPartnerView)
            <div class="bg-white rounded-2xl border p-5 space-y-3">
                <p class="font-semibold text-sm">Aksi {{ $viewRole === 'owner' ? 'Pemilik Properti' : 'Penyedia Jasa' }}</p>

                @if ($booking->status === \App\Enums\BookingStatus::PendingConfirmation)
                    <form method="POST" action="{{ route($viewRole.'.bookings.approve', $booking) }}">
                        @csrf
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl">✅ Setujui Pengajuan</button>
                    </form>
                    <form method="POST" action="{{ route($viewRole.'.bookings.reject', $booking) }}" class="space-y-2">
                        @csrf
                        <input name="note" required placeholder="Alasan penolakan..." class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl">❌ Tolak Pengajuan</button>
                    </form>
                @elseif ($booking->status === \App\Enums\BookingStatus::Paid)
                    <form method="POST" action="{{ route($viewRole.'.bookings.start', $booking) }}">
                        @csrf
                        <button class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-3 rounded-xl">▶️ Mulai (Aktifkan Sewa)</button>
                    </form>
                @elseif ($booking->status === \App\Enums\BookingStatus::InProgress)
                    <form method="POST" action="{{ route($viewRole.'.bookings.complete', $booking) }}">
                        @csrf
                        <button class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl">🏁 Tandai Selesai</button>
                    </form>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">Tidak ada aksi tersedia untuk status {{ $booking->status->label() }}.</p>
                @endif
            </div>
        @endif
    </aside>
</div>
