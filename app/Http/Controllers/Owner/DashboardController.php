<?php

namespace App\Http\Controllers\Owner;

use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $propertyQuery = $user->properties();
        $bookingQuery = BookingStatus::class;

        $stats = [
            'total_properties' => $propertyQuery->count(),
            'active' => $user->properties()->where('status', PropertyStatus::Available)->count(),
            'rented' => $user->properties()->where('status', PropertyStatus::Rented)->count(),
            'pending_verification' => $user->properties()->where('verification', 'pending')->count(),
            'pending_bookings' => $user->properties()
                ->whereHas('bookings', fn ($q) => $q->where('status', BookingStatus::PendingConfirmation))
                ->count(),
            'revenue' => (float) \App\Models\Booking::where('status', BookingStatus::Completed)
                ->whereHas('property', fn ($q) => $q->where('user_id', $user->id))
                ->sum('total_price'),
        ];

        $recentBookings = \App\Models\Booking::with(['property', 'customer'])
            ->where('type', 'property')
            ->whereHas('property', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(5)
            ->get();

        return view('owner.dashboard', compact('stats', 'recentBookings'));
    }
}
