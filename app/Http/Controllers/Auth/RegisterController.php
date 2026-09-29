<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegister(Request $request): View
    {
        // Batasi pilihan role publik (super_admin/admin tidak boleh didaftarkan sendiri)
        $requested = UserRole::tryFrom((string) $request->query('as', 'customer'));
        $role = in_array($requested, [UserRole::Customer, UserRole::Owner, UserRole::Provider], true)
            ? $requested
            : UserRole::Customer;

        return view('auth.register', ['role' => $role]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'role' => ['required', 'in:customer,owner,provider'],
            'password' => ['required', 'confirmed', 'min:8'],
            // alamat opsional (wajib untuk customer)
            'provinsi' => ['nullable', 'string', 'max:255'],
            'kota_kabupaten' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'alamat' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $role = UserRole::from($data['role']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'],
            'role' => $role,
            'password' => $data['password'],
        ]);

        // Setiap user mendapat profil alamat (dipakai customer saat booking)
        CustomerProfile::create([
            'user_id' => $user->id,
            'provinsi' => $data['provinsi'] ?? null,
            'kota_kabupaten' => $data['kota_kabupaten'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kelurahan' => $data['kelurahan'] ?? null,
            'kode_pos' => $data['kode_pos'] ?? null,
            'alamat' => $data['alamat'] ?? null,
        ]);

        auth()->login($user);

        ActivityLog::record('register', 'auth', "Registrasi akun baru: {$user->name} ({$role->label()}).", $user, $user);

        return redirect()->route($role->homeRoute())->with('success', 'Registrasi berhasil! Selamat datang di CoHub.');
    }
}
