@extends('layouts.dashboard')

@section('title', 'Laporan')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <div>
        <h2 class="text-xl font-bold">📈 Laporan Sistem</h2>
        <p class="text-sm text-gray-500">Ringkasan properti, jasa, pengguna, dan transaksi.</p>
    </div>

    {{-- Tombol export (bukan form -> tanpa loader pindah halaman) --}}
    <div class="flex flex-wrap gap-2" data-no-loader>
        <button type="button" id="btn-export-excel"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm shadow-green-600/30 transition">
            📊 <span class="btn-label">Export Excel</span>
        </button>
        <button type="button" id="btn-export-pdf"
                class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm shadow-red-600/30 transition">
            📄 <span class="btn-label">Export PDF</span>
        </button>
    </div>
</div>

{{-- ==== Ringkasan cepat ==== --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach ($export['summary'] as $row)
        <div class="bg-white rounded-2xl border p-4 reveal">
            <p class="text-xs text-gray-400 truncate">{{ $row[0] }}</p>
            <p class="text-lg font-extrabold mt-1 truncate" title="{{ $row[1] }}">{{ str_contains($row[0], 'Pendapatan') ? format_idr($row[1]) : number_format($row[1]) }}</p>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Status transaksi --}}
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-4">Transaksi per Status</h3>
        <div class="space-y-2">
            @foreach (\App\Enums\BookingStatus::cases() as $status)
                @php $row = $byStatus->get($status->value); @endphp
                <div class="flex items-center justify-between text-sm">
                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ booking_badge($status) }}">{{ $status->label() }}</span>
                    <span class="font-semibold">{{ $row->total ?? 0 }} · {{ format_idr($row->amount ?? 0) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Pengguna per role --}}
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-4">Pengguna per Role</h3>
        <div class="space-y-2">
            @foreach (\App\Enums\UserRole::cases() as $role)
                <div class="flex justify-between text-sm bg-gray-50 rounded-xl px-4 py-2.5">
                    <span>{{ $role->label() }}</span>
                    <span class="font-bold">{{ number_format($usersByRole[$role->value] ?? 0) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Transaksi bulanan --}}
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-4">Transaksi 6 Bulan Terakhir</h3>
        <div class="space-y-2">
            @forelse ($monthly as $row)
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-20 text-gray-400">{{ $row->bulan }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                        <div class="bg-brand-500 h-full rounded-full" style="width: {{ min(100, $row->total * 20) }}%"></div>
                    </div>
                    <span class="font-semibold w-56 text-right">{{ $row->total }} trx · {{ format_idr($row->amount) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada transaksi.</p>
            @endforelse
        </div>
    </div>

    {{-- Top properti --}}
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-4">Properti Terpopuler</h3>
        <div class="space-y-2 text-sm">
            @forelse ($topProperties as $i => $p)
                <div class="flex justify-between items-center bg-gray-50 rounded-xl px-4 py-2.5">
                    <span class="truncate">{{ $i + 1 }}. {{ $p->name }}</span>
                    <span class="font-semibold whitespace-nowrap ml-2">{{ $p->bookings_count }} booking · {{ format_idr($p->revenue ?? 0) }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm">Belum ada data.</p>
            @endforelse
        </div>
    </div>

    {{-- Top jasa --}}
    <div class="bg-white rounded-2xl border p-6 lg:col-span-2">
        <h3 class="font-bold mb-4">Jasa Terpopuler</h3>
        <div class="grid sm:grid-cols-2 gap-2 text-sm">
            @forelse ($topServices as $i => $s)
                <div class="flex justify-between items-center bg-gray-50 rounded-xl px-4 py-2.5">
                    <span class="truncate">{{ $i + 1 }}. {{ $s->name }}</span>
                    <span class="font-semibold whitespace-nowrap ml-2">{{ $s->bookings_count }} order · {{ format_idr($s->revenue ?? 0) }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm">Belum ada data.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SheetJS (Excel) + jsPDF (PDF) — export langsung di browser, tanpa server --}}
<script defer src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script>
(function () {
    'use strict';

    var report = @json($export);
    var fmt = new Intl.NumberFormat('id-ID');
    var stamp = new Date().toISOString().slice(0, 10).replace(/-/g, '');

    function setBusy(btn, busy) {
        var label = btn.querySelector('.btn-label');
        if (!label) return;
        if (busy) {
            btn.dataset.orig = label.textContent;
            label.textContent = 'Menyiapkan…';
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-wait');
        } else {
            label.textContent = btn.dataset.orig || label.textContent;
            btn.disabled = false;
            btn.classList.remove('opacity-70', 'cursor-wait');
        }
    }

    /* ================= EXPORT EXCEL (.xlsx) ================= */
    function exportExcel() {
        var btn = document.getElementById('btn-export-excel');

        // Fallback: unduh CSV bila SheetJS gagal dimuat
        if (typeof XLSX === 'undefined') {
            var rows = [['Laporan Sistem CoHub IA ITB Jatim'], ['Dibuat', report.generated_at], []]
                .concat(report.status.map(function (r) { return ['Status', r.status, r.total, r.amount]; }));
            var csv = '\uFEFF' + rows.map(function (r) { return r.join(';'); }).join('\n');
            var a = document.createElement('a');
            a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
            a.download = 'laporan-cohub-' + stamp + '.csv';
            a.click();
            return;
        }

        setBusy(btn, true);
        setTimeout(function () {
            try {
                var wb = XLSX.utils.book_new();

                var sheet = XLSX.utils.aoa_to_sheet([
                    ['LAPORAN SISTEM COHUB IA ITB JATIM'],
                    ['Dibuat', report.generated_at], [],
                    ['Ringkasan'], ['Metrik', 'Nilai'],
                ].concat(
                    report.summary.map(function (s) { return [s[0], s[1]]; }),
                    [],
                    ['Pengguna per Role'], ['Role', 'Total'],
                    report.roles.map(function (r) { return [r.role, r.total]; })
                ));
                XLSX.utils.book_append_sheet(wb, sheet, 'Ringkasan');

                var st = XLSX.utils.aoa_to_sheet([['Status', 'Jumlah Transaksi', 'Nilai (Rp)']]
                    .concat(report.status.map(function (r) { return [r.status, r.total, r.amount]; })));
                XLSX.utils.book_append_sheet(wb, st, 'Transaksi per Status');

                var mo = XLSX.utils.aoa_to_sheet([['Bulan', 'Jumlah Transaksi', 'Nilai (Rp)']]
                    .concat(report.monthly.map(function (r) { return [r.bulan, r.total, r.amount]; })));
                XLSX.utils.book_append_sheet(wb, mo, 'Transaksi Bulanan');

                var tp = XLSX.utils.aoa_to_sheet([['Properti', 'Kota', 'Jumlah Booking', 'Pendapatan (Rp)']]
                    .concat(report.top_properties.map(function (r) { return [r.nama, r.kota, r.bookings, r.revenue]; })));
                XLSX.utils.book_append_sheet(wb, tp, 'Top Properti');

                var ts = XLSX.utils.aoa_to_sheet([['Jasa', 'Penyedia', 'Jumlah Order', 'Pendapatan (Rp)']]
                    .concat(report.top_services.map(function (r) { return [r.nama, r.provider, r.orders, r.revenue]; })));
                XLSX.utils.book_append_sheet(wb, ts, 'Top Jasa');

                XLSX.writeFile(wb, 'laporan-cohub-' + stamp + '.xlsx');
            } finally {
                setBusy(btn, false);
            }
        }, 60);
    }

    /* ================= EXPORT PDF ================= */
    function exportPdf() {
        var btn = document.getElementById('btn-export-pdf');

        // Fallback: dialog print bila jsPDF gagal dimuat
        if (!window.jspdf || !window.jspdf.jsPDF) {
            window.print();
            return;
        }

        setBusy(btn, true);
        setTimeout(function () {
            try {
                var jsPDF = window.jspdf.jsPDF;
                var doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                var brand = [79, 70, 229]; // #4f46e5

                // Header
                doc.setFillColor.apply(doc, brand);
                doc.rect(0, 0, 210, 26, 'F');
                doc.setTextColor(255);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(15);
                doc.text('LAPORAN SISTEM COHUB IA ITB JATIM', 105, 11, { align: 'center' });
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9);
                doc.text('Dicetak: ' + report.generated_at, 105, 18, { align: 'center' });

                var y = 34;
                function section(title, head, body, moneyCols) {
                    if (y > 250) { doc.addPage(); y = 18; }
                    doc.setTextColor(31, 41, 55);
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(11);
                    doc.text(title, 14, y);
                    y += 3;
                    doc.autoTable({
                        startY: y,
                        head: [head],
                        body: body,
                        theme: 'grid',
                        styles: { fontSize: 8.5, cellPadding: 2.2, textColor: [55, 65, 81] },
                        headStyles: { fillColor: brand, textColor: 255, fontStyle: 'bold' },
                        alternateRowStyles: { fillColor: [244, 245, 250] },
                        margin: { left: 14, right: 14 },
                        columnStyles: (moneyCols || []).reduce(function (acc, c) {
                            acc[c] = { halign: 'right', fontStyle: 'bold' }; return acc;
                        }, {}),
                        didDrawPage: function () {},
                    });
                    y = doc.lastAutoTable.finalY + 9;
                }

                section('Ringkasan', ['Metrik', 'Nilai'],
                    report.summary.map(function (s) { return [s[0], fmt.format(s[1])]; }), [1]);

                section('Transaksi per Status', ['Status', 'Jumlah', 'Nilai (Rp)'],
                    report.status.map(function (r) { return [r.status, fmt.format(r.total), fmt.format(Math.round(r.amount))]; }), [1, 2]);

                section('Pengguna per Role', ['Role', 'Total'],
                    report.roles.map(function (r) { return [r.role, fmt.format(r.total)]; }), [1]);

                section('Transaksi 6 Bulan Terakhir', ['Bulan', 'Jumlah', 'Nilai (Rp)'],
                    report.monthly.map(function (r) { return [r.bulan, fmt.format(r.total), fmt.format(Math.round(r.amount))]; }), [1, 2]);

                section('Properti Terpopuler', ['Properti', 'Kota', 'Booking', 'Pendapatan (Rp)'],
                    report.top_properties.map(function (r) { return [r.nama, r.kota, fmt.format(r.bookings), fmt.format(Math.round(r.revenue))]; }), [2, 3]);

                section('Jasa Terpopuler', ['Jasa', 'Penyedia', 'Order', 'Pendapatan (Rp)'],
                    report.top_services.map(function (r) { return [r.nama, r.provider, fmt.format(r.orders), fmt.format(Math.round(r.revenue))]; }), [2, 3]);

                // Footer nomor halaman
                var pages = doc.getNumberOfPages();
                for (var i = 1; i <= pages; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.setTextColor(150);
                    doc.text('CoHub IA ITB Jatim — Laporan Sistem · Halaman ' + i + ' dari ' + pages, 105, 291, { align: 'center' });
                }

                doc.save('laporan-cohub-' + stamp + '.pdf');
            } finally {
                setBusy(btn, false);
            }
        }, 60);
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#btn-export-excel')) exportExcel();
        if (e.target.closest('#btn-export-pdf')) exportPdf();
    });
})();
</script>
@endpush
