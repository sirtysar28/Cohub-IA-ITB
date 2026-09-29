<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $stats = [
            'total' => $user->bookings()->count(),
            'active' => $user->bookings()->where('status', BookingStatus::InProgress)->count(),
            'pending' => $user->bookings()->whereIn('status', [BookingStatus::PendingConfirmation, BookingStatus::Approved])->count(),
            'completed' => $user->bookings()->where('status', BookingStatus::Completed)->count(),
        ];

        $recentBookings = $user->bookings()
            ->with(['property.media', 'service.media', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('stats', 'recentBookings'));
    }
}
