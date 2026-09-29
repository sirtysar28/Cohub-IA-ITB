<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * FR-RNT-02: pencarian jasa berdasarkan kategori dan kisaran harga.
     */
    public function index(Request $request): View
    {
        $services = Service::published()
            ->with(['media', 'category', 'provider'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('kategori'), fn ($q) => $q->where('category_id', $request->kategori))
            ->when($request->filled('harga_min'), fn ($q) => $q->where('starting_price', '>=', $request->harga_min))
            ->when($request->filled('harga_max'), fn ($q) => $q->where('starting_price', '<=', $request->harga_max))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::service()->orderBy('name')->get();

        return view('public.services.index', compact('services', 'categories'));
    }

    public function show(Service $service): View
    {
        abort_unless($service->verification->value === 'approved', 404);

        $service->load(['media', 'availabilities', 'category', 'provider']);

        return view('public.services.show', compact('service'));
    }
}
