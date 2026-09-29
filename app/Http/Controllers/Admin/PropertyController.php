<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Property;
use App\Notifications\ItemVerified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\View\View;

/** Verifikasi & moderasi properti. */
class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $properties = Property::with(['owner', 'category', 'media'])
            ->when($request->filled('verification'), fn ($q) => $q->where('verification', $request->verification))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('kota_kabupaten', 'like', $term));
            })
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('admin.properties.index', compact('properties'));
    }

    public function verify(Request $request, Property $property): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'note.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($data['decision'] === 'rejected' && empty($data['note'])) {
            return back()->withErrors(['note' => 'Alasan penolakan wajib diisi.']);
        }

        $property->update([
            'verification' => VerificationStatus::from($data['decision']),
            'rejection_note' => $data['decision'] === 'rejected' ? $data['note'] : null,
        ]);

        ActivityLog::record(
            'verify',
            'properti',
            'Properti "'.$property->name.'" '.($data['decision'] === 'approved' ? 'disetujui & dipublikasikan' : 'ditolak'.($data['note'] ? ' — alasan: '.$data['note'] : '')).'.',
            $property,
        );

        // Notifikasi 🔔 + email ke pemilik properti
        try {
            NotificationFacade::send($property->owner, new ItemVerified(
                type: 'property',
                itemName: $property->name,
                status: VerificationStatus::from($data['decision']),
                note: $data['note'] ?? null,
                url: route('owner.properties.show', $property),
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Properti '.($data['decision'] === 'approved' ? 'diverifikasi (published)' : 'ditolak').'.');
    }
}
