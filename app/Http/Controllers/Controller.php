<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

abstract class Controller
{
    /**
     * Wrapper aman untuk transisi status booking:
     * transisi ilegal dikembalikan sebagai flash error, bukan HTTP 500.
     *
     * @return RedirectResponse|null null bila transisi sukses.
     */
    protected function transitionBooking(Booking $booking, BookingStatus $next, ?string $note = null): ?RedirectResponse
    {
        try {
            app(BookingService::class)->changeStatus($booking, $next, auth()->user(), $note);

            return null;
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
