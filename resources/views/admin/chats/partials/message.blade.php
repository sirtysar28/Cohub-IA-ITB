{{-- Satu baris pesan di konsol chat admin --}}
@php
    $isOwn = $m->sender === 'admin' && (int) $m->sender_id === (int) $adminId;
    $isUser = $m->sender === 'user';
    $isBot = $m->sender === 'bot';
@endphp
<div class="flex gap-2.5 {{ $isUser ? 'justify-start' : 'justify-end' }} max-w-[85%] {{ $isUser ? 'self-start' : 'self-end' }}">
    @if ($isUser)
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm shrink-0">🙋</div>
    @endif
    <div>
        <div class="{{ $isUser
            ? 'bg-white border border-gray-200 rounded-2xl rounded-tl-sm text-gray-800'
            : 'bg-gradient-to-br from-brand-600 to-purple-600 text-white rounded-2xl rounded-tr-sm' }} px-4 py-2.5 text-sm leading-relaxed shadow-sm">
            @if ($isBot)
                <span class="text-[10px] font-bold uppercase tracking-wide opacity-70 block mb-0.5">🤖 Jawaban AI</span>
            @elseif (! $isUser)
                <span class="text-[10px] font-bold uppercase tracking-wide opacity-70 block mb-0.5">{{ $m->senderUser?->name ?? 'Admin' }}</span>
            @endif
            {{ $m->message }}
        </div>
        <p class="text-[10px] text-gray-400 mt-1 {{ $isUser ? '' : 'text-right' }}">{{ $m->created_at->timezone('Asia/Jakarta')->translatedFormat('d M, H:i') }}</p>
    </div>
</div>
