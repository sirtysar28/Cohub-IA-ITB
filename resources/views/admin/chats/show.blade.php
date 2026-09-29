@extends('layouts.dashboard')

@section('title', 'Detail Chat')

@section('content')
<div class="flex items-center justify-between flex-wrap gap-3">
    <div>
        <a href="{{ route('admin.chats.index') }}" class="text-sm text-gray-500 hover:text-brand-600">← Kembali ke daftar chat</a>
        <h2 class="text-xl font-bold mt-1 flex items-center gap-2">
            💬 {{ $conversation->display_name }}
            <span class="text-xs rounded-full px-2.5 py-1 font-semibold {{ $conversation->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $conversation->status === 'open' ? 'Terbuka' : 'Ditutup' }}
            </span>
        </h2>
        <p class="text-sm text-gray-500">
            Mode: {{ $conversation->type === 'ai' ? '🤖 AI Assistant' : '👨‍💻 Chat Admin' }}
            @if ($conversation->user) • Akun: {{ $conversation->user->email }} @endif
        </p>
    </div>

    <form method="POST" action="{{ route('admin.chats.toggle', $conversation) }}">
        @csrf
        <button data-loading-text="Memproses…"
                class="text-sm font-semibold px-4 py-2 rounded-xl transition {{ $conversation->status === 'open' ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-green-600 text-white hover:bg-green-700' }}">
            {{ $conversation->status === 'open' ? '🚪 Tutup Percakapan' : '↩️ Buka Kembali' }}
        </button>
    </form>
</div>

<div class="grid lg:grid-cols-3 gap-6 items-start">

    {{-- ====== Percakapan ====== --}}
    <section class="lg:col-span-2 bg-white border rounded-2xl overflow-hidden reveal">
        <div class="px-5 py-3 border-b bg-gradient-to-r from-brand-600 to-purple-600 text-white flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">{{ $conversation->type === 'ai' ? '🤖' : '👨‍💻' }}</div>
            <div>
                <p class="font-bold text-sm">{{ $conversation->display_name }}</p>
                <p class="text-[11px] text-indigo-100">{{ $conversation->guest_email ?: $conversation->user?->email ?: 'Tanpa email' }}</p>
            </div>
        </div>

        {{-- Riwayat pesan --}}
        <div id="chat-scroll" class="p-5 space-y-3 h-[420px] overflow-y-auto flex flex-col bg-gradient-to-b from-gray-50 to-white">
            @forelse ($conversation->messages as $m)
                @include('admin.chats.partials.message', ['m' => $m, 'adminId' => auth()->id()])
            @empty
                <p class="text-center text-sm text-gray-400 py-10">Belum ada pesan.</p>
            @endforelse
        </div>

        {{-- Form balasan --}}
        @if ($conversation->status === 'open')
            <form method="POST" action="{{ route('admin.chats.reply', $conversation) }}" class="p-4 border-t flex gap-2" id="chat-reply-form">
                @csrf
                <input type="text" name="message" id="chat-reply-input" required maxlength="2000" placeholder="Tulis balasan…"
                       class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none" autocomplete="off">
                <button data-loading-text="…" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition disabled:opacity-70">Kirim ➤</button>
            </form>
        @else
            <p class="p-4 border-t text-center text-sm text-gray-400">Percakapan ditutup. Buka kembali untuk melanjutkan.</p>
        @endif
    </section>

    {{-- ====== Info singkat ====== --}}
    <aside class="space-y-4">
        <div class="bg-white border rounded-2xl p-5 reveal">
            <h3 class="font-semibold text-sm mb-3">ℹ️ Info Percakapan</h3>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-500">Pengirim</dt><dd class="font-semibold">{{ $conversation->display_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-semibold">{{ $conversation->guest_email ?: $conversation->user?->email ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Mode</dt><dd class="font-semibold">{{ $conversation->type === 'ai' ? '🤖 AI' : '👨‍💻 Admin' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total Pesan</dt><dd class="font-semibold">{{ $conversation->messages->count() }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Dimulai</dt><dd class="font-semibold">{{ $conversation->created_at->translatedFormat('d M Y H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Terakhir Aktif</dt><dd class="font-semibold">{{ $conversation->last_message_at?->diffForHumans() ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-brand-50 border border-brand-100 rounded-2xl p-5 text-sm text-brand-900 reveal">
            <p class="font-bold mb-1">💡 Tips membalas</p>
            <ul class="list-disc list-inside space-y-1 text-xs text-brand-700">
                <li>Jawab singkat, ramah, dan solutif.</li>
                <li>Untuk kendala transaksi, arahkan ke menu Booking terkait.</li>
                <li>Pengguna login akan menerima 🔔 notifikasi atas balasan Anda.</li>
            </ul>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
/* Auto-scroll ke pesan terbaru + polling pesan baru tiap 5 detik */
(function () {
    var box = document.getElementById('chat-scroll');
    if (box) box.scrollTop = box.scrollHeight;

    var lastId = @js($conversation->messages->max('id') ?? 0);
    var convoId = {{ $conversation->id }};

    setInterval(function () {
        fetch('{{ url('/admin/chats/'.$conversation->id.'/messages') }}?after=' + lastId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok || !data.messages.length) return;
                data.messages.forEach(function (m) { lastId = Math.max(lastId, m.id); });
                window.location.reload(); // sederhana & aman: muat ulang riwayat
            })
            .catch(function () {});
    }, 5000);

    // Fokus ke input balasan
    var input = document.getElementById('chat-reply-input');
    if (input) input.focus();
})();
</script>
@endpush
