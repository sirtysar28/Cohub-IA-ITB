@extends('layouts.dashboard')

@section('title', 'Chat Masuk')

@section('content')
<div>
    <h2 class="text-xl font-bold">💬 Chat Masuk</h2>
    <p class="text-sm text-gray-500">Percakapan dari widget chat pengunjung &amp; pengguna. Balas langsung dari halaman detail.</p>
</div>

{{-- Filter --}}
<form method="GET" class="bg-white border rounded-xl p-4 flex flex-wrap gap-3 items-end reveal">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Cari</label>
        <input name="q" value="{{ request('q') }}" placeholder="Nama / email pengirim…" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Mode</label>
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            <option value="">Semua</option>
            <option value="admin" {{ request('type') === 'admin' ? 'selected' : '' }}>👨‍💻 Chat Admin</option>
            <option value="ai" {{ request('type') === 'ai' ? 'selected' : '' }}>🤖 AI (monitoring)</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            <option value="">Semua</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Terbuka</option>
            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Ditutup</option>
        </select>
    </div>
    <button class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">🔍 Filter</button>
    <a href="{{ route('admin.chats.index') }}" class="text-sm text-gray-500 hover:text-brand-600">Reset</a>
</form>

{{-- Daftar percakapan --}}
<div class="bg-white border rounded-2xl overflow-hidden reveal">
    @forelse ($conversations as $convo)
        <a href="{{ route('admin.chats.show', $convo) }}"
           class="flex items-center gap-4 px-5 py-4 border-b last:border-0 hover:bg-brand-50/50 transition {{ $convo->admin_unread > 0 ? 'bg-indigo-50/40' : '' }}">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 {{ $convo->type === 'ai' ? 'bg-purple-100' : 'bg-brand-100' }}">
                {{ $convo->type === 'ai' ? '🤖' : '👨‍💻' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="font-semibold text-sm truncate">{{ $convo->display_name }}</p>
                    @if ($convo->admin_unread > 0)
                        <span class="bg-red-500 text-white text-[10px] font-bold rounded-full px-2 py-0.5">{{ $convo->admin_unread }} baru</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 truncate mt-0.5">{{ $convo->last_message?->message ?? 'Belum ada pesan' }}</p>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[10px] rounded-full px-2 py-0.5 font-semibold {{ $convo->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $convo->status === 'open' ? 'Terbuka' : 'Ditutup' }}
                </span>
                <p class="text-[11px] text-gray-400 mt-1">{{ $convo->last_message_at?->diffForHumans() ?? '—' }}</p>
            </div>
        </a>
    @empty
        <div class="text-center py-14">
            <p class="text-4xl mb-3">💬</p>
            <p class="text-gray-500 text-sm">Belum ada percakapan masuk.</p>
        </div>
    @endforelse
</div>

{{ $conversations->links() }}
@endsection
