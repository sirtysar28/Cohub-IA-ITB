<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $byStatus = Booking::query()
            ->selectRaw('status, COUNT(*) as total, COALESCE(SUM(total_price),0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn ($r) => $r->status instanceof BookingStatus ? $r->status->value : $r->status);

        $monthly = Booking::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total, COALESCE(SUM(total_price),0) as amount")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $topProperties = Property::withCount('bookings')
            ->withSum('bookings as revenue', 'total_price')
            ->orderByDesc('bookings_count')
            ->take(10)
            ->get();

        $topServices = Service::withCount('bookings')
            ->withSum('bookings as revenue', 'total_price')
            ->orderByDesc('bookings_count')
            ->take(10)
            ->get();

        $usersByRole = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        /* ---- Data untuk export Excel & PDF (client-side) ---- */
        $export = [
            'generated_at' => now()->translatedFormat('d F Y, H:i'),
            'summary' => [
                ['Total Pengguna', (int) User::count()],
                ['Total Properti', (int) Property::count()],
                ['Total Jasa', (int) Service::count()],
                ['Total Transaksi', (int) Booking::count()],
                ['Total Pendapatan (paid+)', (float) Booking::whereIn('status', [BookingStatus::Paid, BookingStatus::InProgress, BookingStatus::Completed])->sum('total_price')],
            ],
            'status' => collect(BookingStatus::cases())
                ->map(fn ($s) => [
                    'status' => $s->label(),
                    'total' => (int) ($byStatus->get($s->value)->total ?? 0),
                    'amount' => (float) ($byStatus->get($s->value)->amount ?? 0),
                ])->values(),
            'roles' => collect(UserRole::cases())
                ->map(fn ($r) => ['role' => $r->label(), 'total' => (int) ($usersByRole[$r->value] ?? 0)])
                ->values(),
            'monthly' => $monthly->map(fn ($r) => [
                'bulan' => now()->createFromFormat('Y-m', $r->bulan)->translatedFormat('F Y'),
                'total' => (int) $r->total,
                'amount' => (float) $r->amount,
            ])->values(),
            'top_properties' => $topProperties->map(fn ($p) => [
                'nama' => $p->name,
                'kota' => $p->kota_kabupaten,
                'bookings' => (int) $p->bookings_count,
                'revenue' => (float) ($p->revenue ?? 0),
            ])->values(),
            'top_services' => $topServices->map(fn ($s) => [
                'nama' => $s->name,
                'provider' => $s->provider?->name ?? '-',
                'orders' => (int) $s->bookings_count,
                'revenue' => (float) ($s->revenue ?? 0),
            ])->values(),
        ];

        return view('admin.reports.index', compact('byStatus', 'monthly', 'topProperties', 'topServices', 'usersByRole', 'export'));
    }
}
