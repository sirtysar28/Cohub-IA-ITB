<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Service;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(protected BookingService $engine)
    {
    }

    /* ==================== BOOKING PROPERTI (FR-TRX-01) ==================== */

    public function createProperty(Property $property): View
    {
        abort_unless($property->verification->value === 'approved' && $property->status->value === 'available', 404);
        abort_if($property->user_id === auth()->id(), 403, 'Anda tidak dapat menyewa properti sendiri.');

        $property->load(['prices', 'media', 'owner', 'category']);

        return view('customer.bookings.create-property', compact('property'));
    }

    public function storeProperty(Request $request, Property $property): RedirectResponse
    {
        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price_period' => ['required', 'in:daily,monthly,yearly'],
            'purpose' => ['required', 'string', 'max:1000'], // tujuan penggunaan properti
            'note' => ['nullable', 'string', 'max:2000'],    // custom note
            'action' => ['required', 'in:draft,submit'],
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
            'purpose.required' => 'Tujuan penggunaan properti wajib diisi.',
        ]);

        $price = $property->prices()->where('period', $data['price_period'])->firstOrFail();
        $days = (int) (strtotime($data['end_date']) - strtotime($data['start_date'])) / 86400 + 1;
        $total = BookingService::calculatePropertyTotal($price->price, $data['price_period'], $days);

        $booking = Booking::create([
            'code' => $this->generateCode('P'),
            'type' => BookingType::Property,
            'customer_id' => auth()->id(),
            'property_id' => $property->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'price_period' => $data['price_period'],
            'unit_price' => $price->price,
            'total_price' => $total,
            'purpose' => $data['purpose'],
            'note' => $data['note'] ?? null,
            'status' => BookingStatus::Draft,
        ]);

        $booking->histories()->create([
            'from_status' => null,
            'to_status' => BookingStatus::Draft->value,
            'actor_id' => auth()->id(),
            'note' => 'Booking dibuat',
        ]);

        ActivityLog::record('create', 'transaksi', 'Booking sewa properti "'.$property->name.'" dibuat ('.$booking->code.').', $booking);

        if ($data['action'] === 'submit') {
            $this->engine->changeStatus($booking, BookingStatus::PendingConfirmation, auth()->user());
        }

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->with('success', $data['action'] === 'submit'
                ? 'Pengajuan sewa dikirim. Menunggu konfirmasi pemilik properti.'
                : 'Booking disimpan sebagai draft.');
    }

    /* ===================== BOOKING JASA (FR-TRX-02) ===================== */

    public function createService(Service $service): View
    {
        abort_unless($service->verification->value === 'approved' && $service->status === 'available', 404);
        abort_if($service->user_id === auth()->id(), 403, 'Anda tidak dapat memesan jasa sendiri.');

        $service->load(['availabilities', 'media', 'provider', 'category']);

        return view('customer.bookings.create-service', compact('service'));
    }

    public function storeService(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'schedule_time' => ['nullable', 'date_format:H:i'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'note' => ['nullable', 'string', 'max:2000'], // custom note / brief
            'action' => ['required', 'in:draft,submit'],
        ], [
            'start_date.after_or_equal' => 'Tanggal jadwal tidak boleh di masa lalu.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        // Validasi hari sesuai availability jasa (Senin-Sabtu)
        $day = (int) date('N', strtotime($data['start_date'])); // 1=Senin..7=Minggu
        if (! $service->availabilities()->where('day_of_week', $day)->exists()) {
            return back()->withErrors(['start_date' => 'Jasa tidak tersedia pada hari '.['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][$day].'.'])->withInput();
        }

        $total = BookingService::calculateServiceTotal($service->starting_price, $data['quantity']);

        $booking = Booking::create([
            'code' => $this->generateCode('S'),
            'type' => BookingType::Service,
            'customer_id' => auth()->id(),
            'service_id' => $service->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'schedule_time' => $data['schedule_time'] ?? null,
            'quantity' => $data['quantity'],
            'unit_price' => $service->starting_price,
            'total_price' => $total,
            'note' => $data['note'] ?? null,
            'status' => BookingStatus::Draft,
        ]);

        $booking->histories()->create([
            'from_status' => null,
            'to_status' => BookingStatus::Draft->value,
            'actor_id' => auth()->id(),
            'note' => 'Booking dibuat',
        ]);

        ActivityLog::record('create', 'transaksi', 'Booking jasa "'.$service->name.'" dibuat ('.$booking->code.').', $booking);

        if ($data['action'] === 'submit') {
            $this->engine->changeStatus($booking, BookingStatus::PendingConfirmation, auth()->user());
        }

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->with('success', $data['action'] === 'submit'
                ? 'Pemesanan jasa dikirim. Menunggu konfirmasi penyedia jasa.'
                : 'Booking disimpan sebagai draft.');
    }

    /* ======================= AKSI STATUS CUSTOMER ======================= */

    /** Draft -> Pending Confirmation */
    public function submit(Booking $booking): RedirectResponse
    {
        $this->authorizeCustomer($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::PendingConfirmation)) {
            return $error;
        }

        return back()->with('success', 'Pengajuan sewa dikirim, menunggu konfirmasi.');
    }

    /** Approved -> Paid (simulasi pembayaran manual/transfer) */
    public function pay(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeCustomer($booking);

        $data = $request->validate(['method' => ['required', 'in:transfer,cash']]);

        if (! $booking->payment) {
            $booking->payment()->create([
                'invoice_no' => 'INV-'.now()->format('YmdHis').'-'.$booking->id,
                'amount' => $booking->total_price,
                'method' => $data['method'],
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);
        }

        if ($error = $this->transitionBooking($booking, BookingStatus::Paid, 'Pembayaran dikonfirmasi')) {
            return $error;
        }

        return back()->with('success', 'Pembayaran berhasil! Booking berstatus DIBAYAR.');
    }

    /** Cancel dari draft / pending / approved / paid */
    public function cancel(Booking $booking): RedirectResponse
    {
        $this->authorizeCustomer($booking);

        if ($error = $this->transitionBooking($booking, BookingStatus::Cancelled, 'Dibatalkan oleh customer')) {
            return $error;
        }

        return back()->with('success', 'Booking dibatalkan.');
    }

    /* ============================ HALAMAN ============================ */

    public function index(Request $request): View
    {
        $bookings = auth()->user()->bookings()
            ->with(['property.media', 'service.media', 'payment'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customer.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $this->authorizeCustomer($booking);

        $booking->load(['property.media', 'property.owner', 'service.media', 'service.provider', 'histories.actor', 'payment']);

        return view('customer.bookings.show', compact('booking'));
    }

    /* ============================ HELPERS ============================ */

    /** Redirect cerdas ke halaman detail sesuai role (dipakai notifikasi). */
    public function redirect(Booking $booking): RedirectResponse
    {
        $user = auth()->user();

        return match (true) {
            $user->isAdmin() => redirect()->route('admin.bookings.show', $booking),
            $user->isOwner() && $booking->property?->user_id === $user->id => redirect()->route('owner.bookings.show', $booking),
            $user->isProvider() && $booking->service?->user_id === $user->id => redirect()->route('provider.bookings.show', $booking),
            $booking->customer_id === $user->id => redirect()->route('customer.bookings.show', $booking),
            default => abort(403),
        };
    }

    protected function authorizeCustomer(Booking $booking): void
    {
        abort_unless($booking->customer_id === auth()->id(), 403, 'Ini bukan booking Anda.');
    }

    protected function generateCode(string $prefix): string
    {
        return sprintf('%s-%s-%04d', $prefix, now()->format('ymd'), Booking::whereDate('created_at', today())->count() + 1);
    }
}
