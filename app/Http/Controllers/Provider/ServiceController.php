<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\ServiceMedia;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public const MAX_MEDIA = ImageService::MAX_IMAGES;

    public function __construct(protected ImageService $images)
    {
    }

    /** FR-SVC-01..04: daftar jasa milik penyedia. */
    public function index(Request $request): View
    {
        $services = auth()->user()->services()
            ->with(['media', 'category', 'availabilities'])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('provider.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('provider.services.form', [
            'service' => new Service(),
            'categories' => Category::service()->orderBy('name')->get(),
            'maxMedia' => self::MAX_MEDIA,
            'days' => ServiceAvailability::DAYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $service = auth()->user()->services()->create($data + ['verification' => 'pending']);

        $this->syncAvailabilities($service, $request);
        $this->syncMedia($service, $request);

        ActivityLog::record('create', 'jasa', 'Jasa baru "'.$service->name.'" didaftarkan & menunggu verifikasi.', $service);

        return redirect()->route('provider.services.index')
            ->with('success', 'Jasa berhasil didaftarkan dan menunggu verifikasi admin.');
    }

    public function show(Service $service): View
    {
        $this->authorizeProvider($service);
        $service->load(['media', 'availabilities', 'category', 'bookings.customer']);

        return view('provider.services.show', compact('service'));
    }

    public function edit(Service $service): View
    {
        $this->authorizeProvider($service);
        $service->load(['media', 'availabilities']);

        return view('provider.services.form', [
            'service' => $service,
            'categories' => Category::service()->orderBy('name')->get(),
            'maxMedia' => self::MAX_MEDIA,
            'days' => ServiceAvailability::DAYS,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->authorizeProvider($service);

        $data = $this->validateData($request);
        $service->update($data);

        $this->syncAvailabilities($service, $request);
        $this->syncMedia($service, $request, false);

        ActivityLog::record('update', 'jasa', 'Jasa "'.$service->name.'" diperbarui datanya.', $service);

        return redirect()->route('provider.services.index')
            ->with('success', 'Jasa berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorizeProvider($service);

        $this->images->deleteMediaPaths($service->media->pluck('path'));

        ActivityLog::record('delete', 'jasa', 'Jasa "'.$service->name.'" dihapus.');

        $service->delete();

        return redirect()->route('provider.services.index')->with('success', 'Jasa dihapus.');
    }

    public function destroyMedia(Service $service, ServiceMedia $media): RedirectResponse
    {
        $this->authorizeProvider($service);
        abort_unless($media->service_id === $service->id, 404);

        $wasThumbnail = $media->is_thumbnail;
        $this->images->deleteMediaPaths([$media->path]);
        $media->delete();

        if ($wasThumbnail) {
            $service->media()->orderBy('sort_order')->first()?->update(['is_thumbnail' => true]);
        }

        return back()->with('success', 'Media dihapus.');
    }

    /* ========================= Helpers ========================= */

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'starting_price' => ['required', 'numeric', 'min:0'], // FR-SVC-02: harga mulai dari
            'price_unit' => ['required', 'in:per_hour,per_day,per_project,per_pax,per_unit'],
            'status' => ['required', 'in:available,unavailable'],
            'media_new' => ['nullable', 'array', 'max:'.self::MAX_MEDIA], // FR-SVC-03: portfolio
            'media_new.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm', 'max:10240'],
            'availability' => ['nullable', 'array'],
            'availability.*.day' => ['required', 'integer', 'between:1,6'], // Senin-Sabtu
            'availability.*.start_time' => ['required', 'date_format:H:i'],
            'availability.*.end_time' => ['required', 'date_format:H:i', 'after:availability.*.start_time'],
        ], [
            'name.required' => 'Nama jasa wajib diisi.',
            'description.required' => 'Deskripsi jasa wajib diisi.',
            'starting_price.required' => 'Harga mulai dari wajib diisi.',
            'media_new.max' => 'Maksimal '.self::MAX_MEDIA.' file portfolio.',
        ]);
    }

    /** FR-SVC-04: availability Senin-Sabtu 00:00-24:00. */
    protected function syncAvailabilities(Service $service, Request $request): void
    {
        $service->availabilities()->delete();

        foreach ($request->input('availability', []) as $a) {
            $service->availabilities()->create([
                'day_of_week' => $a['day'],
                'start_time' => $a['start_time'],
                'end_time' => $a['end_time'] ?? '23:59',
            ]);
        }
    }

    protected function syncMedia(Service $service, Request $request, bool $isNew = true): void
    {
        if (! $request->hasFile('media_new')) {
            return;
        }

        $files = array_values($request->file('media_new'));
        $existing = $service->media()->count();
        $slots = max(0, self::MAX_MEDIA - $existing);
        $files = array_slice($files, 0, $slots);

        if (empty($files)) {
            return;
        }

        $media = $this->images->storeMedia($files, "services/{$service->id}");
        $offset = $existing;

        foreach ($media as $item) {
            $service->media()->create($item + ['sort_order' => $offset++]);
        }

        if (! $service->media()->where('is_thumbnail', true)->exists()) {
            $service->media()->orderBy('sort_order')->first()?->update(['is_thumbnail' => true]);
        }
    }

    protected function authorizeProvider(Service $service): void
    {
        abort_unless($service->user_id === auth()->id(), 403, 'Ini bukan jasa Anda.');
    }
}
