@extends('layouts.dashboard')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <h2 class="text-xl font-bold">👥 Kelola Pengguna</h2>
    <form method="GET" class="flex gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama/email..." class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <select name="role" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Role</option>
            @foreach (\App\Enums\UserRole::cases() as $r)
                <option value="{{ $r->value }}" {{ request('role') === $r->value ? 'selected' : '' }}>{{ $r->label() }}</option>
            @endforeach
        </select>
        <button class="bg-brand-600 text-white rounded-xl px-4 py-2 text-sm font-semibold">Cari</button>
    </form>
</div>

<div class="bg-white rounded-2xl border overflow-x-auto">
    <table class="w-full text-sm min-w-[800px]">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-5 py-3">Nama</th>
                <th class="px-5 py-3">Kontak</th>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3">Aset/Booking</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach ($users as $user)
                <tr class="hover:bg-gray-50 {{ $user->is_active ? '' : 'opacity-60' }}">
                    <td class="px-5 py-3">
                        <p class="font-semibold">{{ $user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                    </td>
                    <td class="px-5 py-3">{{ $user->whatsapp ?? '-' }}</td>
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="flex gap-1">
                            @csrf @method('PUT')
                            <select name="role" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-2 py-1 text-xs">
                                @foreach (\App\Enums\UserRole::cases() as $r)
                                    <option value="{{ $r->value }}" {{ $user->role === $r ? 'selected' : '' }} {{ $user->isAdmin() ? 'disabled' : '' }}>{{ $r->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        {{ $user->properties_count }} properti · {{ $user->services_count }} jasa · {{ $user->bookings_count }} booking
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs rounded-full px-3 py-1 {{ $user->is_active ? status_badge('green') : status_badge('red') }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                @csrf
                                <button class="text-xs border rounded-lg px-2.5 py-1.5 hover:bg-gray-100 {{ $user->is_active ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @unless ($user->isAdmin())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini beserta datanya?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs border border-red-200 text-red-600 rounded-lg px-2.5 py-1.5 hover:bg-red-50">🗑</button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $users->links() }}
@endsection
