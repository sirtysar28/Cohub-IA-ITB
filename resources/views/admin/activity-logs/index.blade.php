@extends('layouts.dashboard')

@section('title', 'History Log')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3">
    <div>
        <h2 class="text-xl font-bold">🕘 History Log Aktivitas</h2>
        <p class="text-sm text-gray-500">Audit trail seluruh aktivitas pengguna: login, verifikasi, transaksi, perubahan data &amp; pengaturan.</p>
    </div>

    @if (auth()->user()->isSuperAdmin())
        <form method="POST" action="{{ route('admin.activity-logs.clear') }}"
              onsubmit="return confirm('Bersihkan SELURUH history log? Tindakan ini tidak bisa dibatalkan.')">
            @csrf @method('DELETE')
            <button class="text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 rounded-lg px-3 py-2 transition">🗑 Bersihkan Log</button>
        </form>
    @endif
</div>

{{-- Filter interaktif --}}
<form method="GET" class="bg-white border rounded-2xl p-4 grid sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end reveal">
    <div class="lg:col-span-2">
        <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
        <input name="q" value="{{ request('q') }}" placeholder="Deskripsi / nama pengguna..."
               class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Modul</label>
        <select name="module" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Modul</option>
            @foreach (\App\Models\ActivityLog::MODULES as $m)
                <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Aksi</label>
        <select name="action" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Aksi</option>
            @foreach (\App\Models\ActivityLog::ACTIONS as $a)
                <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
        <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
    </div>
    <div class="flex gap-2">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        </div>
        <button class="bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-4 py-2 text-sm font-semibold transition self-end">Filter</button>
    </div>
</form>

{{-- Timeline log --}}
<div class="bg-white rounded-2xl border overflow-hidden reveal">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[860px]">
            <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3">Waktu</th>
                    <th class="px-5 py-3">Pengguna</th>
                    <th class="px-5 py-3">Aksi</th>
                    <th class="px-5 py-3">Modul</th>
                    <th class="px-5 py-3">Deskripsi</th>
                    <th class="px-5 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 whitespace-nowrap">
                            <p class="font-semibold">{{ $log->created_at->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</p>
                        </td>
                        <td class="px-5 py-3">
                            @if ($log->user)
                                <p class="font-semibold">{{ $log->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $log->user->role->label() }}</p>
                            @else
                                <span class="text-xs text-gray-400 italic">Sistem / terhapus</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs rounded-full px-3 py-1 font-semibold {{ activity_badge($log->action) }}">
                                {{ $log->action_icon }} {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-3 py-1 font-medium">{{ ucfirst($log->module) }}</span>
                        </td>
                        <td class="px-5 py-3 max-w-md">{{ $log->description }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Belum ada aktivitas yang dicatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $logs->links() }}
@endsection
