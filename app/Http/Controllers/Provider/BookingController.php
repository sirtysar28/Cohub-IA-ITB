<?php

namespace App\Http\Controllers\Provider;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen order jasa (FR-TRX-05): approve / reject / start / complete.
 */
class BookingController extends Controller
{
    public function __construct(protected BookingService $engine)
    {
    }

    public function index(Request $request): View
    {
        $bookings = Booking::with(['service.media', 'customer'])
            ->where('type', 'service')
            ->whereHas('service', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('provider.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        abort_unless($booking->type->value === 'service', 404);
        $this->authorizeProvider($booking);

        $booking->load(['service.media', 'customer.customerProfile', 'histories.actor', 'payment']);

        return view('provider.bookings.show', compact('booking'));
    }

    public function approve(Booking $booking): RedirectResponse
    {
        $this->authorizeProvider($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::Approved)) {
            return $error;
        }

        return back()->with('success', 'Pemesanan disetujui. Menunggu pembayaran customer.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeProvider($booking);

        $data = $request->validate(['note' => ['required', 'string', 'max:1000']], [
            'note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($error = $this->transitionBooking($booking, BookingStatus::Rejected, $data['note'])) {
            return $error;
        }

        return back()->with('success', 'Pemesanan ditolak.');
    }

    public function start(Booking $booking): RedirectResponse
    {
        $this->authorizeProvider($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::InProgress, 'Pekerjaan dimulai')) {
            return $error;
        }

        return back()->with('success', 'Pekerjaan ditandai berlangsung.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        $this->authorizeProvider($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::Completed, 'Pekerjaan selesai')) {
            return $error;
        }

        return back()->with('success', 'Pemesanan selesai.');
    }

    protected function authorizeProvider(Booking $booking): void
    {
        abort_unless($booking->service && $booking->service->user_id === auth()->id(), 403, 'Ini bukan booking jasa Anda.');
    }
}
