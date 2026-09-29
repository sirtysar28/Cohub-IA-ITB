<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Validasi hak akses berdasarkan role (Security NFR).
     * Usage: ->middleware('role:admin') atau 'role:owner,provider'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        $allowed = array_map(
            fn (string $r) => UserRole::tryFrom($r)?->value,
            $roles
        );

        // Super admin otomatis lolos semua area admin
        if (in_array(UserRole::Admin->value, $allowed, true)) {
            $allowed[] = UserRole::SuperAdmin->value;
        }

        if (! in_array($user->role->value, $allowed, true)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
