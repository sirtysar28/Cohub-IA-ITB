<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Transaction Management admin. */
class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['property', 'service', 'customer', 'payment'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('code', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['property.media', 'service.media', 'customer', 'histories.actor', 'payment']);

        return view('admin.bookings.show', compact('booking'));
    }

    /** Hapus transaksi — hanya Super Admin (riwayat & pembayaran ikut terhapus). */
    public function destroy(Booking $booking): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat menghapus transaksi.');

        $code = $booking->code;
        $itemName = $booking->item_name;
        $booking->delete();

        ActivityLog::record('delete', 'transaksi', "Transaksi {$code} ({$itemName}) dihapus beserta riwayat & pembayarannya.");

        return redirect()->route('admin.bookings.index')->with('success', "Transaksi {$code} beserta riwayatnya dihapus.");
    }
}
