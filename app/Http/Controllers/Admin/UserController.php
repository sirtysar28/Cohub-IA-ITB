<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Halaman manage users (Settings pada daftar halaman dokumen). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::withCount(['properties', 'services', 'bookings'])
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menonaktifkan akun sendiri.');
        abort_if($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin(), 403, 'Akun Super Admin tidak dapat diubah.');

        $user->update(['is_active' => ! $user->is_active]);

        ActivityLog::record(
            'status',
            'pengguna',
            'Akun "'.$user->name.'" '.($user->is_active ? 'diaktifkan' : 'dinonaktifkan').'.',
            $user,
        );

        return back()->with('success', 'Status akun '.($user->is_active ? 'diaktifkan' : 'dinonaktifkan').'.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin(), 403, 'Role Super Admin tidak dapat diubah.');

        // Role super_admin hanya bisa diberikan oleh Super Admin
        $assignable = auth()->user()->isSuperAdmin()
            ? ['required', 'in:super_admin,admin,customer,owner,provider']
            : ['required', 'in:admin,customer,owner,provider'];

        $data = $request->validate(['role' => $assignable]);

        $user->update(['role' => UserRole::from($data['role'])]);

        ActivityLog::record(
            'role',
            'pengguna',
            'Role "'.$user->name.'" diubah menjadi '.$user->role->label().'.',
            $user,
        );

        return back()->with('success', 'Role pengguna diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin(), 403, 'Akun Super Admin tidak dapat dihapus.');

        $user->delete();

        ActivityLog::record('delete', 'pengguna', 'Pengguna "'.$user->name.'" ('.$user->email.') dihapus beserta datanya.');

        return back()->with('success', 'Pengguna dihapus.');
    }
}
