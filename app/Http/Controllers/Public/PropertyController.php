<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    /**
     * FR-RNT-01: pencarian properti berdasarkan lokasi, tipe, dan rentang harga.
     */
    public function index(Request $request): View
    {
        $properties = Property::published()
            ->with(['media', 'prices', 'category', 'owner'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', $term)
                        ->orWhere('kota_kabupaten', 'like', $term)
                        ->orWhere('kecamatan', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('kota'), fn ($q) => $q->where('kota_kabupaten', 'like', '%'.$request->kota.'%'))
            ->when($request->filled('kategori'), fn ($q) => $q->where('category_id', $request->kategori))
            ->when($request->filled('harga_min'), function ($q) use ($request) {
                $q->whereHas('prices', fn ($p) => $p->where('price', '>=', $request->harga_min));
            })
            ->when($request->filled('harga_max'), function ($q) use ($request) {
                $q->whereHas('prices', fn ($p) => $p->where('price', '<=', $request->harga_max));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::property()->orderBy('name')->get();

        return view('public.properties.index', compact('properties', 'categories'));
    }

    /** FR-RNT-03: detail properti + galeri + deskripsi. */
    public function show(Property $property): View
    {
        abort_unless($property->verification->value === 'approved', 404);

        $property->load(['media', 'prices', 'category', 'owner']);

        return view('public.properties.show', compact('property'));
    }
}
