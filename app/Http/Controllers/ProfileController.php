<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Profil pengguna (semua role): data akun, alamat, dan ganti password.
 */
class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user()->loadMissing('customerProfile');

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->customerProfile ?: new CustomerProfile(),
        ]);
    }

    /** Perbarui data akun & alamat. */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'kota_kabupaten' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'alamat' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp hanya boleh angka, +, -, dan spasi.',
        ]);

        $user->update([
            'name' => $data['name'],
            'whatsapp' => $data['whatsapp'],
        ]);

        $user->customerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            collect($data)->except(['name', 'whatsapp'])->all()
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /** Ganti password (wajib password lama). */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update(['password' => $data['password']]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
