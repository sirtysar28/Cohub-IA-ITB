@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')

@section('content')
<div class="flex flex-wrap justify-between items-end gap-3">
    <div>
        <h2 class="text-xl font-bold">Dashboard Administrator</h2>
        <p class="text-sm text-gray-500">Verifikasi data, moderasi, dan monitoring sistem.</p>
    </div>
    <p class="text-xs text-gray-400">🕘 {{ now()->translatedFormat('l, d F Y') }}</p>
</div>

{{-- ==== Pencarian global interaktif ==== --}}
<div class="relative z-30" x-data="adminSearch()" @click.outside="open = false" @keydown.window.escape="open = false">
    <div class="relative">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 select-none">🔍</span>
        <input type="text" x-model="q" @input.debounce.350ms="search()" @focus="groups.length && (open = true)"
               placeholder="Cari properti, jasa, pengguna, atau kode transaksi… lalu klik hasilnya"
               class="w-full bg-white border border-gray-200 rounded-2xl pl-11 pr-12 py-3.5 text-sm shadow-sm
                      focus:ring-2 focus:ring-brand-500 focus:border-brand-400 focus:outline-none transition"
               autocomplete="off">
        <span x-cloak x-show="loading" class="absolute right-4 top-1/2 -translate-y-1/2">
            <span class="loader-spinner sm"></span>
        </span>
    </div>

    {{-- Dropdown hasil terkelompok --}}
    <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute left-0 right-0 mt-2 bg-white rounded-2xl border shadow-2xl shadow-indigo-200/50 overflow-hidden search-panel">
        <template x-for="group in groups" :key="group.key">
            <div>
                <p class="search-group-label px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-gray-400 border-b" x-text="group.label"></p>
                <template x-for="(item, idx) in group.items" :key="group.key + '-' + idx">
                    <a :href="item.url" @click="open = false" class="search-result-item flex items-center justify-between gap-3 px-4 py-2.5 border-b border-gray-50">
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold truncate" x-text="item.title"></span>
                            <span class="block text-xs text-gray-400 truncate" x-text="item.subtitle"></span>
                        </span>
                        <span class="sr-arrow text-brand-600 text-sm shrink-0">→</span>
                    </a>
                </template>
            </div>
        </template>
        <p x-show="!loading && searched && total === 0" class="px-4 py-8 text-center text-sm text-gray-400">
            😔 Tidak ada hasil untuk “<span x-text="q"></span>”.
        </p>
    </div>
</div>

{{-- ==== Quick filter chips ==== --}}
<div class="flex flex-wrap gap-2 reveal">
    <a href="{{ route('admin.properties.index', ['verification' => 'pending']) }}" class="filter-chip">🏢 Verifikasi Properti @if ($stats['pending_property'])<span class="chip-count">{{ $stats['pending_property'] }}</span>@endif</a>
    <a href="{{ route('admin.services.index', ['verification' => 'pending']) }}" class="filter-chip">🛠 Verifikasi Jasa @if ($stats['pending_service'])<span class="chip-count">{{ $stats['pending_service'] }}</span>@endif</a>
    <a href="{{ route('admin.bookings.index', ['status' => 'pending_confirmation']) }}" class="filter-chip">⏳ Booking Perlu Konfirmasi @if ($stats['pending_bookings'])<span class="chip-count">{{ $stats['pending_bookings'] }}</span>@endif</a>
    <a href="{{ route('admin.users.index') }}" class="filter-chip">👥 Pengguna</a>
    <a href="{{ route('admin.activity-logs.index') }}" class="filter-chip">🕘 History Log</a>
</div>

{{-- ==== Kartu statistik: klik -> langsung ke datanya ==== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <a href="{{ route('admin.users.index') }}" class="action-card stat-card bg-white rounded-2xl border p-5 reveal">
        <p class="text-xs text-gray-400 flex items-center gap-1.5"><span class="ac-icon text-base">👥</span> Pengguna <span class="ac-arrow ml-auto text-brand-500 text-xs font-bold">Lihat →</span></p>
        <p class="ac-value text-2xl font-extrabold mt-1" data-countup="{{ $stats['users'] }}">0</p>
    </a>
    <a href="{{ route('admin.properties.index') }}" class="action-card stat-card bg-white rounded-2xl border p-5 reveal reveal-delay-1">
        <p class="text-xs text-gray-400 flex items-center gap-1.5"><span class="ac-icon text-base">🏢</span> Properti <span class="ac-arrow ml-auto text-brand-500 text-xs font-bold">Lihat →</span></p>
        <p class="ac-value text-2xl font-extrabold mt-1" data-countup="{{ $stats['properties'] }}">0</p>
    </a>
    <a href="{{ route('admin.services.index') }}" class="action-card stat-card bg-white rounded-2xl border p-5 reveal reveal-delay-2">
        <p class="text-xs text-gray-400 flex items-center gap-1.5"><span class="ac-icon text-base">🛠</span> Jasa <span class="ac-arrow ml-auto text-brand-500 text-xs font-bold">Lihat →</span></p>
        <p class="ac-value text-2xl font-extrabold mt-1" data-countup="{{ $stats['services'] }}">0</p>
    </a>
    <a href="{{ route('admin.bookings.index') }}" class="action-card stat-card bg-white rounded-2xl border p-5 reveal reveal-delay-3">
        <p class="text-xs text-gray-400 flex items-center gap-1.5"><span class="ac-icon text-base">🧾</span> Transaksi <span class="ac-arrow ml-auto text-brand-500 text-xs font-bold">Lihat →</span></p>
        <p class="ac-value text-2xl font-extrabold mt-1" data-countup="{{ $stats['bookings'] }}">0</p>
    </a>
</div>

{{-- ==== Grafik statistik: bar + pie ==== --}}
<div class="grid lg:grid-cols-5 gap-6">
    {{-- Bar chart: transaksi N bulan terakhir (filter interaktif) --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border p-6 reveal">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h3 class="font-semibold">📊 Statistik Transaksi {{ $range }} Bulan Terakhir</h3>
                <p class="text-xs text-gray-400 mt-0.5">Jumlah transaksi properti &amp; jasa per bulan</p>
            </div>
            <div class="flex gap-1 bg-gray-100 rounded-xl p-1 text-xs font-semibold">
                @foreach ([3 => '3 Bl', 6 => '6 Bl', 12 => '12 Bl'] as $r => $label)
                    <a href="{{ route('admin.dashboard', array_filter(['range' => $r === 6 ? null : $r])) }}"
                       class="px-3 py-1.5 rounded-lg transition {{ $range === $r ? 'bg-white text-brand-700 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="flex flex-wrap justify-between items-center gap-2 mt-2">
            <p class="text-xs text-gray-500">Total pendapatan periode ini:</p>
            <p class="text-lg font-extrabold text-brand-700" data-countup="{{ (int) $chartMonthly->sum('amount') }}" data-prefix="Rp ">Rp 0</p>
        </div>
        <div class="chart-wrap h-72 mt-3">
            <canvas id="chart-transaksi-bulanan"
                    data-labels="{{ json_encode($chartMonthly->pluck('label')) }}"
                    data-totals="{{ json_encode($chartMonthly->pluck('total')) }}"
                    data-amounts="{{ json_encode($chartMonthly->pluck('amount')) }}"></canvas>
        </div>
        <div class="flex flex-wrap gap-x-5 gap-y-1 mt-3 text-xs text-gray-500">
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span> Penyewaan Properti: <b class="text-gray-700">{{ number_format($chartType['property']) }}</b></span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Order Jasa: <b class="text-gray-700">{{ number_format($chartType['service']) }}</b></span>
        </div>
    </div>

    {{-- Pie chart: distribusi status transaksi --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border p-6 reveal reveal-delay-1">
        <h3 class="font-semibold">🥧 Distribusi Status Transaksi</h3>
        <p class="text-xs text-gray-400 mt-0.5">Komposisi seluruh transaksi berdasarkan status</p>
        <div class="chart-wrap h-64 mt-2">
            <canvas id="chart-status-transaksi"
                    data-labels="{{ json_encode($chartStatus->pluck('label')) }}"
                    data-totals="{{ json_encode($chartStatus->pluck('total')) }}"
                    data-colors="{{ json_encode($chartStatus->pluck('color')) }}"></canvas>
        </div>
        <div class="chart-legend justify-center mt-3">
            @foreach ($chartStatus as $i => $s)
                <span class="lg-item" style="animation-delay: {{ 0.35 + $i * 0.08 }}s">
                    <span class="lg-dot" style="background: {{ $s['color'] }}"></span>
                    {{ $s['label'] }} <b class="text-gray-800">({{ $s['total'] }})</b>
                </span>
            @endforeach
        </div>
    </div>
</div>

{{-- Antrian verifikasi (kartu aksi animasi) --}}
<div class="grid sm:grid-cols-3 gap-4">
    <a href="{{ route('admin.properties.index', ['verification' => 'pending']) }}" class="action-card bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
        <p class="text-xs text-yellow-600 font-semibold flex items-center gap-1.5"><span class="ac-icon text-sm">🏢</span> Properti Menunggu Verifikasi</p>
        <p class="text-3xl font-extrabold text-yellow-700 mt-1">{{ $stats['pending_property'] }}</p>
    </a>
    <a href="{{ route('admin.services.index', ['verification' => 'pending']) }}" class="action-card bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <p class="text-xs text-amber-600 font-semibold flex items-center gap-1.5"><span class="ac-icon text-sm">🛠</span> Jasa Menunggu Verifikasi</p>
        <p class="text-3xl font-extrabold text-amber-700 mt-1">{{ $stats['pending_service'] }}</p>
    </a>
    <a href="{{ route('admin.bookings.index', ['status' => 'pending_confirmation']) }}" class="action-card bg-orange-50 border border-orange-200 rounded-2xl p-5">
        <p class="text-xs text-orange-600 font-semibold flex items-center gap-1.5"><span class="ac-icon text-sm">🧾</span> Booking Menunggu Konfirmasi</p>
        <p class="text-3xl font-extrabold text-orange-700 mt-1">{{ $stats['pending_bookings'] }}</p>
    </a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Verifikasi properti --}}
    <div class="bg-white rounded-2xl border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-semibold">🏢 Properti Menunggu Verifikasi</h3>
            <a href="{{ route('admin.properties.index', ['verification' => 'pending']) }}" class="text-sm text-brand-600 font-semibold">Semua →</a>
        </div>
        <div class="divide-y text-sm">
            @forelse ($pendingProperties as $p)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div><p class="font-semibold">{{ $p->name }}</p><p class="text-xs text-gray-400">{{ $p->owner?->name }} · {{ $p->kota_kabupaten }}</p></div>
                    <a href="{{ route('admin.properties.index', ['q' => $p->name]) }}" class="text-brand-600 text-xs font-semibold">Verifikasi →</a>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-gray-400">Tidak ada antrian. 🎉</p>
            @endforelse
        </div>
    </div>

    {{-- Verifikasi jasa --}}
    <div class="bg-white rounded-2xl border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-semibold">🛠 Jasa Menunggu Verifikasi</h3>
            <a href="{{ route('admin.services.index', ['verification' => 'pending']) }}" class="text-sm text-brand-600 font-semibold">Semua →</a>
        </div>
        <div class="divide-y text-sm">
            @forelse ($pendingServices as $s)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div><p class="font-semibold">{{ $s->name }}</p><p class="text-xs text-gray-400">{{ $s->provider?->name }} · {{ $s->category?->name }}</p></div>
                    <a href="{{ route('admin.services.index', ['q' => $s->name]) }}" class="text-brand-600 text-xs font-semibold">Verifikasi →</a>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-gray-400">Tidak ada antrian. 🎉</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Transaksi terbaru + aktivitas terbaru --}}
<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-semibold">🧾 Transaksi Terbaru</h3>
            <a href="{{ route('admin.bookings.index') }}" class="text-sm text-brand-600 font-semibold">Semua →</a>
        </div>
        <div class="divide-y text-sm">
            @foreach ($recentBookings as $b)
                <a href="{{ route('admin.bookings.show', $b) }}" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div><p class="font-semibold">{{ $b->code }} · {{ $b->item_name }}</p><p class="text-xs text-gray-400">{{ $b->customer?->name }} · {{ format_idr($b->total_price) }}</p></div>
                    <span class="text-xs rounded-full px-3 py-1 {{ booking_badge($b->status) }}">{{ $b->status->label() }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-semibold">🕘 Aktivitas Terbaru</h3>
            <a href="{{ route('admin.activity-logs.index') }}" class="text-sm text-brand-600 font-semibold">History Log →</a>
        </div>
        <div class="divide-y text-sm">
            @forelse ($recentActivities as $log)
                <a href="{{ route('admin.activity-logs.index', ['q' => $log->user?->name]) }}" class="px-6 py-3 flex items-center justify-between gap-3 hover:bg-gray-50">
                    <div class="min-w-0">
                        <p class="font-semibold truncate">{{ $log->action_icon }} {{ $log->description }}</p>
                        <p class="text-xs text-gray-400">{{ $log->user?->name ?? 'Sistem' }} · {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs rounded-full px-3 py-1 font-semibold whitespace-nowrap {{ activity_badge($log->action) }}">{{ ucfirst($log->module) }}</span>
                </a>
            @empty
                <p class="px-6 py-8 text-center text-gray-400">Belum ada aktivitas tercatat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
/* Komponen Alpine: pencarian global dashboard admin */
function adminSearch() {
    return {
        q: '',
        open: false,
        loading: false,
        searched: false,
        groups: [],
        get total() { return this.groups.reduce(function (a, g) { return a + g.items.length; }, 0); },
        search() {
            var self = this;
            var term = this.q.trim();
            if (term.length < 2) { self.open = false; self.groups = []; self.searched = false; return; }
            self.loading = true;
            fetch("{{ url('/admin/search') }}?q=" + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    self.groups = (data.groups || []).filter(function (g) { return g.items.length > 0; });
                    self.searched = true;
                    self.open = true;
                })
                .catch(function () {}) // diam saja: pencarian bersifat opsional
                .finally(function () { self.loading = false; });
        },
    };
}
</script>
<script>
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var fmt = new Intl.NumberFormat('id-ID');

    /* ---------- Animasi angka count-up ---------- */
    function countUp(el) {
        if (el.dataset.countupDone) return;
        el.dataset.countupDone = '1';

        var target = parseInt(el.getAttribute('data-countup'), 10) || 0;
        var prefix = el.getAttribute('data-prefix') || '';
        if (reduceMotion || target === 0) { el.textContent = prefix + fmt.format(target); return; }

        var dur = 1300, start = null;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min(1, (ts - start) / dur);
            var eased = 1 - Math.pow(1 - p, 4); // easeOutQuart — smooth
            el.textContent = prefix + fmt.format(Math.round(target * eased));
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function initCountUps() {
        var els = document.querySelectorAll('[data-countup]');
        if (!('IntersectionObserver' in window)) { els.forEach(countUp); return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) { if (en.isIntersecting) { countUp(en.target); io.unobserve(en.target); } });
        }, { threshold: 0.4 });
        els.forEach(function (el) { io.observe(el); });
    }

    /* ---------- Grafik statistik (Chart.js, penuh animasi) ---------- */
    function initCharts() {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif";
        Chart.defaults.color = '#6b7280';

        /* == Bar chart: transaksi bulanan == */
        var barCanvas = document.getElementById('chart-transaksi-bulanan');
        if (barCanvas) {
            var labels = JSON.parse(barCanvas.dataset.labels || '[]');
            var totals = JSON.parse(barCanvas.dataset.totals || '[]');
            var amounts = JSON.parse(barCanvas.dataset.amounts || '[]');

            var ctx = barCanvas.getContext('2d');
            var gradient = ctx.createLinearGradient(0, 0, 0, 320);
            gradient.addColorStop(0, 'rgba(99,102,241,.95)');
            gradient.addColorStop(1, 'rgba(129,140,248,.45)');

            new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Transaksi',
                        data: totals,
                        backgroundColor: gradient,
                        hoverBackgroundColor: '#4f46e5',
                        borderRadius: { topLeft: 10, topRight: 10, bottomLeft: 0, bottomRight: 0 },
                        borderSkipped: false,
                        maxBarThickness: 46,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: reduceMotion ? false : {
                        duration: 1100,
                        easing: 'easeOutQuart',
                        delay: function (ctx) { return ctx.dataIndex * 90; }, // bar muncul berurutan
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false,
                            titleFont: { weight: 'bold' },
                            callbacks: {
                                label: function (item) {
                                    return '💼 ' + fmt.format(item.raw) + ' transaksi';
                                },
                                afterLabel: function (item) {
                                    return '💰 Nilai: Rp ' + fmt.format(Math.round(amounts[item.dataIndex] || 0));
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, maxTicksLimit: 6 },
                            grid: { color: 'rgba(0,0,0,.05)' },
                            border: { display: false },
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false },
                        },
                    },
                },
            });
        }

        /* == Pie chart (doughnut): distribusi status == */
        var pieCanvas = document.getElementById('chart-status-transaksi');
        if (pieCanvas) {
            var sLabels = JSON.parse(pieCanvas.dataset.labels || '[]');
            var sTotals = JSON.parse(pieCanvas.dataset.totals || '[]');
            var sColors = JSON.parse(pieCanvas.dataset.colors || '[]');
            var sTotal = sTotals.reduce(function (a, b) { return a + b; }, 0);

            // Teks total di tengah donat
            var centerText = {
                id: 'centerText',
                afterDraw: function (chart) {
                    if (chart.config.type !== 'doughnut') return;
                    var meta = chart.getDatasetMeta(0).data[0];
                    if (!meta) return;
                    var ctx2 = chart.ctx;
                    ctx2.save();
                    ctx2.textAlign = 'center';
                    ctx2.textBaseline = 'middle';
                    ctx2.fillStyle = '#111827';
                    ctx2.font = '800 26px ' + Chart.defaults.font.family;
                    ctx2.fillText(fmt.format(sTotal), meta.x, meta.y - 8);
                    ctx2.fillStyle = '#9ca3af';
                    ctx2.font = '600 11px ' + Chart.defaults.font.family;
                    ctx2.fillText('TOTAL TRANSAKSI', meta.x, meta.y + 14);
                    ctx2.restore();
                },
            };

            new Chart(pieCanvas, {
                type: 'doughnut',
                data: {
                    labels: sLabels,
                    datasets: [{
                        data: sTotals,
                        backgroundColor: sColors,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 12,
                        hoverBorderColor: '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    layout: { padding: 8 },
                    animation: reduceMotion ? false : {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1300,
                        easing: 'easeOutQuart',
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: function (item) {
                                    var pct = sTotal ? Math.round(item.raw / sTotal * 100) : 0;
                                    return ' ' + item.label + ': ' + fmt.format(item.raw) + ' (' + pct + '%)';
                                },
                            },
                        },
                    },
                },
                plugins: [centerText],
            });
        }
    }

    // Grafik hanya dirender saat section terlihat (smooth reveal)
    function initChartsOnVisible() {
        var canvases = document.querySelectorAll('canvas[id^="chart-"]');
        if (!canvases.length) return;
        if (!('IntersectionObserver' in window)) { initCharts(); return; }
        var done = false;
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting && !done) {
                    done = true;
                    initCharts();
                    io.disconnect();
                }
            });
        }, { threshold: 0.2 });
        io.observe(canvases[0]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initCountUps(); initChartsOnVisible(); });
    } else {
        initCountUps(); initChartsOnVisible();
    }
})();
</script>
@endpush
