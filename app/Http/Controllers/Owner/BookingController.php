<?php

namespace App\Http\Controllers\Owner;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen order sewa properti (FR-TRX-05): approve / reject / start / complete.
 */
class BookingController extends Controller
{
    public function __construct(protected BookingService $engine)
    {
    }

    public function index(Request $request): View
    {
        $bookings = Booking::with(['property.media', 'customer'])
            ->where('type', 'property')
            ->whereHas('property', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('owner.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        abort_unless($booking->type->value === 'property', 404);
        $this->authorizeOwner($booking);

        $booking->load(['property.media', 'customer.customerProfile', 'histories.actor', 'payment']);

        return view('owner.bookings.show', compact('booking'));
    }

    /** FR-TRX-05: setujui pengajuan sewa. */
    public function approve(Booking $booking): RedirectResponse
    {
        $this->authorizeOwner($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::Approved)) {
            return $error;
        }

        return back()->with('success', 'Booking disetujui. Menunggu pembayaran customer.');
    }

    /** FR-TRX-05: tolak pengajuan sewa. */
    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeOwner($booking);

        $data = $request->validate(['note' => ['required', 'string', 'max:1000']], [
            'note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($error = $this->transitionBooking($booking, BookingStatus::Rejected, $data['note'])) {
            return $error;
        }

        return back()->with('success', 'Booking ditolak.');
    }

    /** Paid -> In Progress / Active */
    public function start(Booking $booking): RedirectResponse
    {
        $this->authorizeOwner($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::InProgress, 'Sewa dimulai')) {
            return $error;
        }

        if ($booking->property) {
            $booking->property->update(['status' => 'rented']);
        }

        return back()->with('success', 'Sewa ditandai berlangsung. Status properti: DISEWA.');
    }

    /** In Progress -> Completed */
    public function complete(Booking $booking): RedirectResponse
    {
        $this->authorizeOwner($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::Completed, 'Sewa selesai')) {
            return $error;
        }

        if ($booking->property) {
            $booking->property->update(['status' => 'available']);
        }

        return back()->with('success', 'Booking selesai. Properti kembali tersedia.');
    }

    protected function authorizeOwner(Booking $booking): void
    {
        abort_unless($booking->property && $booking->property->user_id === auth()->id(), 403, 'Ini bukan booking properti Anda.');
    }
}
