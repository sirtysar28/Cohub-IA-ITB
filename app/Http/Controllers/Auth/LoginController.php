<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    /** Karakter captcha (tanpa karakter ambigu: 0/O, 1/I/L). */
    protected const CAPTCHA_CHARS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function showLogin(): View
    {
        $this->regenerateCaptcha();

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'captcha' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'captcha.required' => 'Kode captcha wajib diisi.',
        ]);

        // Validasi captcha (5 karakter, case-insensitive)
        $expected = strtoupper((string) session('captcha_code'));
        $given = strtoupper(trim((string) $request->input('captcha')));

        if ($expected === '' || $given !== $expected) {
            $this->regenerateCaptcha();

            return back()->withErrors(['captcha' => 'Kode captcha salah.'])->onlyInput('email');
        }

        // Captcha sekali pakai
        $request->session()->forget('captcha_code');

        $remember = $request->boolean('remember');

        if (! auth()->attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $this->regenerateCaptcha();

            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $user = auth()->user();

        if (! $user->is_active) {
            auth()->logout();
            $this->regenerateCaptcha();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        $request->session()->regenerate();

        ActivityLog::record('login', 'auth', "{$user->name} ({$user->role->label()}) berhasil masuk.", $user, $user);

        return redirect()->intended(route($user->role->homeRoute()));
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record('logout', 'auth', (auth()->user()?->name ?? 'Pengguna').' keluar dari aplikasi.');

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /** Endpoint AJAX: buat kode captcha baru (untuk tombol refresh). */
    public function refreshCaptcha(Request $request)
    {
        $code = $this->regenerateCaptcha();

        return response()->json([
            'code' => $code,
            'styles' => captcha_styles($code),
        ]);
    }

    /** Generate & simpan kode captcha 5 karakter di session. */
    protected function regenerateCaptcha(): string
    {
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= self::CAPTCHA_CHARS[random_int(0, strlen(self::CAPTCHA_CHARS) - 1)];
        }

        session(['captcha_code' => $code]);

        return $code;
    }
}
