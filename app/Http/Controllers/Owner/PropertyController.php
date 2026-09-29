<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public const MAX_MEDIA = ImageService::MAX_IMAGES;

    public function __construct(protected ImageService $images)
    {
    }

    /** FR-PROP-01..04: daftar properti milik pemilik. */
    public function index(Request $request): View
    {
        $properties = auth()->user()->properties()
            ->with(['media', 'prices', 'category'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('owner.properties.index', compact('properties'));
    }

    public function create(): View
    {
        return view('owner.properties.form', [
            'property' => new Property(),
            'categories' => Category::property()->orderBy('name')->get(),
            'maxMedia' => self::MAX_MEDIA,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $property = auth()->user()->properties()->create($data + ['verification' => 'pending']);

        $this->syncPrices($property, $request);
        $this->syncMedia($property, $request);

        ActivityLog::record('create', 'properti', 'Properti baru "'.$property->name.'" didaftarkan & menunggu verifikasi.', $property);

        return redirect()->route('owner.properties.index')
            ->with('success', 'Properti berhasil ditambahkan dan menunggu verifikasi admin.');
    }

    public function show(Property $property): View
    {
        $this->authorizeOwner($property);
        $property->load(['media', 'prices', 'category', 'bookings.customer']);

        return view('owner.properties.show', compact('property'));
    }

    /** FR-PROP-03: update deskripsi, nama, foto, status. */
    public function edit(Property $property): View
    {
        $this->authorizeOwner($property);
        $property->load('media', 'prices');

        return view('owner.properties.form', [
            'property' => $property,
            'categories' => Category::property()->orderBy('name')->get(),
            'maxMedia' => self::MAX_MEDIA,
        ]);
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeOwner($property);

        $data = $this->validateData($request);
        $property->update($data);

        $this->syncPrices($property, $request);
        $this->syncMedia($property, $request, false);

        ActivityLog::record('update', 'properti', 'Properti "'.$property->name.'" diperbarui datanya.', $property);

        return redirect()->route('owner.properties.index')
            ->with('success', 'Properti berhasil diperbarui.');
    }

    /** FR-PROP-04: non-aktifkan properti. */
    public function destroy(Property $property): RedirectResponse
    {
        $this->authorizeOwner($property);

        $this->images->deleteMediaPaths($property->media->pluck('path'));

        ActivityLog::record('delete', 'properti', 'Properti "'.$property->name.'" dihapus.');

        $property->delete();

        return redirect()->route('owner.properties.index')->with('success', 'Properti dihapus.');
    }

    /** Hapus satu media. */
    public function destroyMedia(Property $property, PropertyMedia $media): RedirectResponse
    {
        $this->authorizeOwner($property);
        abort_unless($media->property_id === $property->id, 404);

        $wasThumbnail = $media->is_thumbnail;
        $this->images->deleteMediaPaths([$media->path]);
        $media->delete();

        if ($wasThumbnail) {
            $first = $property->media()->orderBy('sort_order')->first();
            $first?->update(['is_thumbnail' => true]);
        }

        return back()->with('success', 'Media dihapus.');
    }

    /* ========================= HELPers ========================= */

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'land_size' => ['nullable', 'numeric', 'min:0'],
            'building_size' => ['nullable', 'numeric', 'min:0'],
            'provinsi' => ['required', 'string', 'max:255'],
            'kota_kabupaten' => ['required', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'status' => ['required', 'in:available,rented,maintenance,inactive'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.period' => ['required', 'in:daily,monthly,yearly'],
            'prices.*.price' => ['required', 'numeric', 'min:0'],
            'media_new' => ['nullable', 'array', 'max:'.self::MAX_MEDIA],
            'media_new.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm', 'max:10240'],
        ], [
            'name.required' => 'Nama properti wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'kota_kabupaten.required' => 'Kota/Kabupaten wajib diisi (untuk pencarian lokasi).',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'prices.required' => 'Minimal satu harga sewa harus diisi.',
            'media_new.max' => 'Maksimal '.self::MAX_MEDIA.' file media per properti.',
        ]);
    }

    protected function syncPrices(Property $property, Request $request): void
    {
        $property->prices()->delete();

        foreach ($request->input('prices', []) as $p) {
            if (! empty($p['price'])) {
                $property->prices()->create([
                    'period' => $p['period'],
                    'price' => $p['price'],
                ]);
            }
        }
    }

    protected function syncMedia(Property $property, Request $request, bool $isNew = true): void
    {
        if (! $request->hasFile('media_new')) {
            return;
        }

        $files = array_values($request->file('media_new'));
        $existing = $property->media()->count();
        $slots = max(0, self::MAX_MEDIA - $existing);
        $files = array_slice($files, 0, $slots);

        if (empty($files)) {
            return;
        }

        $media = $this->images->storeMedia($files, "properties/{$property->id}");
        $offset = $existing;

        foreach ($media as $item) {
            $property->media()->create($item + ['sort_order' => $offset++]);
        }

        // File pertama menjadi thumbnail jika belum ada
        if (! $property->media()->where('is_thumbnail', true)->exists()) {
            $property->media()->orderBy('sort_order')->first()?->update(['is_thumbnail' => true]);
        }
    }

    protected function authorizeOwner(Property $property): void
    {
        abort_unless($property->user_id === auth()->id(), 403, 'Ini bukan properti Anda.');
    }
}
