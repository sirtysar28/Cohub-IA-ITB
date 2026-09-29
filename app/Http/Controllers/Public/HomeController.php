<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $properties = Property::published()
            ->with(['media', 'prices', 'category', 'owner'])
            ->latest()
            ->take(8)
            ->get();

        $services = Service::published()
            ->with(['media', 'category', 'provider'])
            ->latest()
            ->take(8)
            ->get();

        return view('public.home', compact('properties', 'services') + ['landing' => Setting::landing()]);
    }
}
