<?php

namespace App\Http\Controllers\Provider;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $stats = [
            'total_services' => $user->services()->count(),
            'active' => $user->services()->where('status', 'available')->count(),
            'pending_verification' => $user->services()->where('verification', 'pending')->count(),
            'total_bookings' => Booking::where('type', 'service')
                ->whereHas('service', fn ($q) => $q->where('user_id', $user->id))
                ->count(),
            'pending_bookings' => Booking::where('type', 'service')
                ->where('status', BookingStatus::PendingConfirmation)
                ->whereHas('service', fn ($q) => $q->where('user_id', $user->id))
                ->count(),
            'revenue' => (float) Booking::where('status', BookingStatus::Completed)
                ->where('type', 'service')
                ->whereHas('service', fn ($q) => $q->where('user_id', $user->id))
                ->sum('total_price'),
        ];

        $recentBookings = Booking::with(['service', 'customer'])
            ->where('type', 'service')
            ->whereHas('service', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(5)
            ->get();

        return view('provider.dashboard', compact('stats', 'recentBookings'));
    }
}
