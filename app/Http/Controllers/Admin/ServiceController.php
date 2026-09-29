<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Notifications\ItemVerified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\View\View;

/** Verifikasi & moderasi jasa. */
class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::with(['provider', 'category', 'media'])
            ->when($request->filled('verification'), fn ($q) => $q->where('verification', $request->verification))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function verify(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['decision'] === 'rejected' && empty($data['note'])) {
            return back()->withErrors(['note' => 'Alasan penolakan wajib diisi.']);
        }

        $service->update([
            'verification' => VerificationStatus::from($data['decision']),
            'rejection_note' => $data['decision'] === 'rejected' ? $data['note'] : null,
        ]);

        ActivityLog::record(
            'verify',
            'jasa',
            'Jasa "'.$service->name.'" '.($data['decision'] === 'approved' ? 'disetujui & dipublikasikan' : 'ditolak'.($data['note'] ? ' — alasan: '.$data['note'] : '')).'.',
            $service,
        );

        // Notifikasi 🔔 + email ke penyedia jasa
        try {
            NotificationFacade::send($service->provider, new ItemVerified(
                type: 'service',
                itemName: $service->name,
                status: VerificationStatus::from($data['decision']),
                note: $data['note'] ?? null,
                url: route('provider.services.show', $service),
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Jasa '.($data['decision'] === 'approved' ? 'diverifikasi (published)' : 'ditolak').'.');
    }
}
