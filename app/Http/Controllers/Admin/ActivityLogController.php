<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** History log — audit trail seluruh aktivitas pengguna. */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('description', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)));
            })
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }

    /** Bersihkan seluruh history log — hanya Super Admin. */
    public function clear(): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat membersihkan history log.');

        $count = ActivityLog::count();
        ActivityLog::truncate();

        ActivityLog::record('delete', 'pengaturan', "History log dibersihkan ({$count} entri dihapus) oleh Super Admin.");

        return back()->with('success', "History log dibersihkan — {$count} entri dihapus.");
    }
}
