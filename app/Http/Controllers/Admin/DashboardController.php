<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'users' => User::count(),
            'properties' => Property::count(),
            'services' => Service::count(),
            'bookings' => Booking::count(),
            'pending_property' => Property::where('verification', VerificationStatus::Pending)->count(),
            'pending_service' => Service::where('verification', VerificationStatus::Pending)->count(),
            'pending_bookings' => Booking::where('status', BookingStatus::PendingConfirmation)->count(),
            'revenue' => (float) Booking::whereIn('status', [BookingStatus::Paid, BookingStatus::InProgress, BookingStatus::Completed])->sum('total_price'),
        ];

        // Rentang grafik interaktif: 3 / 6 / 12 bulan
        $range = (int) $request->query('range', 6);
        $range = in_array($range, [3, 6, 12], true) ? $range : 6;

        $recentBookings = Booking::with(['property', 'service', 'customer'])
            ->latest()
            ->take(8)
            ->get();

        $pendingProperties = Property::with('owner')
            ->where('verification', VerificationStatus::Pending)
            ->latest()->take(5)->get();

        $pendingServices = Service::with('provider')
            ->where('verification', VerificationStatus::Pending)
            ->latest()->take(5)->get();

        /* ---- Data grafik statistik ---- */

        // Bar chart: transaksi N bulan terakhir (jumlah + nilai)
        $monthRows = Booking::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total, COALESCE(SUM(total_price),0) as amount")
            ->where('created_at', '>=', now()->subMonths($range - 1)->startOfMonth())
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $chartMonthly = collect(range($range - 1, 0))->map(function (int $i) use ($monthRows) {
            $month = now()->subMonths($i)->startOfMonth();
            $row = $monthRows->get($month->format('Y-m'));

            return [
                'label' => $month->translatedFormat('M Y'),
                'total' => (int) ($row->total ?? 0),
                'amount' => (float) ($row->amount ?? 0),
            ];
        });

        // Pie chart: distribusi status transaksi (hanya status yang memiliki data)
        $statusRows = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $chartStatus = collect(BookingStatus::cases())
            ->map(fn (BookingStatus $s) => [
                'label' => $s->label(),
                'total' => (int) ($statusRows[$s->value] ?? 0),
                'color' => $s->hexColor(),
            ])
            ->filter(fn (array $s) => $s['total'] > 0)
            ->values();

        // Ringkasan tipe transaksi (properti vs jasa)
        $chartType = [
            'property' => Booking::where('type', BookingType::Property)->count(),
            'service' => Booking::where('type', BookingType::Service)->count(),
        ];

        // Aktivitas terbaru (history log)
        $recentActivities = ActivityLog::with('user')->latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'stats', 'recentBookings', 'pendingProperties', 'pendingServices',
            'chartMonthly', 'chartStatus', 'chartType', 'range', 'recentActivities'
        ));
    }

    /**
     * Pencarian global interaktif (AJAX) untuk dashboard admin.
     * Mengembalikan hasil terkelompok: properti, jasa, pengguna, transaksi.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['groups' => []]);
        }

        $like = '%'.$term.'%';

        $properties = Property::with('owner')
            ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('kota_kabupaten', 'like', $like))
            ->limit(5)->get()
            ->map(fn ($p) => [
                'title' => $p->name,
                'subtitle' => $p->kota_kabupaten.' · '.$p->owner?->name,
                'url' => route('admin.properties.index', ['q' => $p->name]),
            ]);

        $services = Service::with('provider', 'category')
            ->where(fn ($q) => $q->where('name', 'like', $like)->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like)))
            ->limit(5)->get()
            ->map(fn ($s) => [
                'title' => $s->name,
                'subtitle' => $s->category?->name.' · '.format_idr($s->starting_price),
                'url' => route('admin.services.index', ['q' => $s->name]),
            ]);

        $users = User::where(fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like))
            ->limit(5)->get()
            ->map(fn ($u) => [
                'title' => $u->name,
                'subtitle' => $u->email.' · '.$u->role->label(),
                'url' => route('admin.users.index', ['q' => $u->name]),
            ]);

        $bookings = Booking::with('customer')
            ->where(fn ($q) => $q->where('code', 'like', $like)
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like)))
            ->limit(5)->latest()->get()
            ->map(fn ($b) => [
                'title' => $b->code.' · '.$b->item_name,
                'subtitle' => $b->customer?->name.' · '.format_idr($b->total_price),
                'url' => route('admin.bookings.show', $b),
            ]);

        return response()->json([
            'groups' => [
                ['key' => 'properti', 'label' => '🏢 Properti', 'icon' => '🏢', 'items' => $properties],
                ['key' => 'jasa', 'label' => '🛠 Jasa', 'icon' => '🛠', 'items' => $services],
                ['key' => 'pengguna', 'label' => '👥 Pengguna', 'icon' => '👥', 'items' => $users],
                ['key' => 'transaksi', 'label' => '🧾 Transaksi', 'icon' => '🧾', 'items' => $bookings],
            ],
        ]);
    }
}
