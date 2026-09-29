{{-- ============================================================
     Widget Chat CoHub — mode AI (Groq) & Chat Admin
     Include di layouts/app & layouts/dashboard.
     Variabel opsional: $hideForAdmin (default true di dashboard)
     ============================================================ --}}
@php
    $chatSettings = \App\Services\GroqChatService::settings();
    $aiEnabled = \App\Services\GroqChatService::aiEnabled();
    $adminChatEnabled = \App\Services\GroqChatService::adminChatEnabled();
    $welcomeMessage = $chatSettings['chat_welcome_message'] ?? 'Halo! 👋 Ada yang bisa dibantu?';
    $hideWidget = ($hideForAdmin ?? true) && auth()->check() && auth()->user()->isAdmin();
@endphp

@if ($aiEnabled || $adminChatEnabled)
<div x-data="cohubChat({
    aiEnabled: {{ $aiEnabled ? 'true' : 'false' }},
    adminEnabled: {{ $adminChatEnabled ? 'true' : 'false' }},
    isGuest: {{ auth()->check() ? 'false' : 'true' }},
    welcome: {{ json_encode($welcomeMessage) }},
    openUrl: {{ json_encode(route('chat.open')) }},
    msgBase: {{ json_encode(url('/chat')) }},
})" x-cloak @keydown.escape.window="open = false">

    {{-- ====== FLOATING BUTTON ====== --}}
    <button @click="open = !open; if (open) $nextTick(() => $refs.input?.focus())"
            :class="open && 'rotate-90 scale-90'"
            class="chat-fab" aria-label="Buka chat bantuan">
        <span x-text="open ? '✕' : '💬'" class="text-2xl leading-none">💬</span>
        @if (!auth()->check())
            <span class="chat-fab-dot"></span>
        @endif
    </button>

    {{-- ====== PANEL ====== --}}
    <div class="chat-panel" :class="open ? 'chat-panel-open' : ''">

        {{-- Header --}}
        <div class="chat-header">
            <div class="flex items-center gap-3 min-w-0">
                <div class="chat-avatar" x-text="tab === 'ai' ? '🤖' : '👨‍💻'">🤖</div>
                <div class="min-w-0">
                    <p class="font-bold text-white text-sm truncate" x-text="tab === 'ai' ? 'Asisten AI CoHub' : 'Chat Admin CoHub'">Asisten AI CoHub</p>
                    <p class="text-[11px] text-indigo-200 flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        <span x-text="tab === 'ai' ? 'Online 24/7 • Groq AI' : 'Admin siap membantu'">Online 24/7</span>
                    </p>
                </div>
            </div>
            <button @click="open = false" class="text-white/70 hover:text-white transition text-lg" aria-label="Tutup chat">✕</button>
        </div>

        {{-- Tabs --}}
        @if ($aiEnabled && $adminChatEnabled)
        <div class="flex border-b bg-indigo-50/60">
            <button @click="switchTab('ai')" class="chat-tab" :class="tab === 'ai' && 'chat-tab-active'">🤖 AI Assistant</button>
            <button @click="switchTab('admin')" class="chat-tab" :class="tab === 'admin' && 'chat-tab-active'">👨‍💻 Chat Admin</button>
        </div>
        @endif

        {{-- Body: pesan --}}
        <div class="chat-body" x-ref="body">
            {{-- Pesan selamat datang --}}
            <template x-if="messages.length === 0">
                <div class="text-center py-6 px-4">
                    <div class="text-4xl mb-2">{{ $aiEnabled ? '🤖' : '👨‍💻' }}</div>
                    <p class="text-sm text-gray-600 leading-relaxed" x-text="welcome"></p>
                    <div class="mt-4 grid gap-2 text-left">
                        <template x-for="q in quickQuestions" :key="q">
                            <button @click="sendQuick(q)" class="chat-quick" x-text="q"></button>
                        </template>
                    </div>
                </div>
            </template>

            <template x-for="m in messages" :key="m.id">
                <div :class="m.sender === 'user' ? 'chat-row-user' : 'chat-row-bot'">
                    <div class="text-lg shrink-0" x-show="m.sender !== 'user'" x-text="m.sender === 'bot' ? '🤖' : '👨‍💻'">🤖</div>
                    <div :class="m.sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-bot'">
                        <span x-html="formatMessage(m.message)"></span>
                        <span class="block text-[10px] opacity-60 mt-1 text-right" x-text="m.time"></span>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div class="chat-row-bot" x-show="typing" x-cloak>
                <div class="text-lg">🤖</div>
                <div class="chat-bubble-bot flex items-center gap-1.5">
                    <span class="chat-dot"></span><span class="chat-dot" style="animation-delay:.15s"></span><span class="chat-dot" style="animation-delay:.3s"></span>
                </div>
            </div>

            <div class="text-center text-[11px] text-gray-400 px-4 py-2" x-show="convStatus === 'closed'" x-cloak>
                ⚠️ Percakapan telah ditutup admin. <button class="underline" @click="resetChat()">Mulai baru</button>
            </div>
        </div>

        {{-- Form guest --}}
        <form class="chat-guest" x-show="isGuest && !conversationId" x-cloak @submit.prevent="openConversation()">
            <p class="text-xs font-bold text-indigo-900 mb-2">👋 Kenalan dulu yuk!</p>
            <input type="text" x-model="guestName" placeholder="Nama Anda" maxlength="100" required
                   class="chat-input mb-2">
            <input type="email" x-model="guestEmail" placeholder="Email (opsional, untuk notifikasi balasan)"
                   class="chat-input mb-2">
            <button type="submit" class="chat-send w-full justify-center" :disabled="loading">
                <span x-text="loading ? 'Membuka…' : 'Mulai Ngobrol →'"></span>
            </button>
        </form>

        {{-- Input pesan --}}
        <form class="chat-footer" x-show="conversationId" @submit.prevent="send()" x-cloak>
            <input type="text" x-ref="input" x-model="draft" @keydown.enter.prevent="send()"
                   :placeholder="tab === 'ai' ? 'Tanya apa saja tentang CoHub…' : 'Tulis pesan untuk admin…'"
                   maxlength="2000" class="chat-input flex-1" :disabled="sending">
            <button type="submit" class="chat-send" :disabled="sending || !draft.trim()" aria-label="Kirim">
                <span x-show="!sending">➤</span>
                <span x-show="sending" class="chat-dot bg-white" style="animation-delay:0s"></span>
                <span x-show="sending" class="chat-dot bg-white" style="animation-delay:.15s"></span>
                <span x-show="sending" class="chat-dot bg-white" style="animation-delay:.3s"></span>
            </button>
        </form>
    </div>
</div>

<style>
/* ===== FAB ===== */
.chat-fab{position:fixed;right:1.25rem;bottom:5.75rem;z-index:60;width:60px;height:60px;border-radius:999px;
  background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;
  box-shadow:0 10px 30px rgba(79,70,229,.45);cursor:pointer;border:none;transition:transform .25s ease,box-shadow .25s;}
.chat-fab:hover{transform:translateY(-3px) scale(1.05);box-shadow:0 14px 36px rgba(79,70,229,.55);}
.chat-fab::after{content:'';position:absolute;inset:0;border-radius:999px;border:2px solid rgba(99,102,241,.5);animation:chatPulse 2.2s infinite;}
@keyframes chatPulse{0%{transform:scale(1);opacity:.8}100%{transform:scale(1.35);opacity:0}}
.chat-fab-dot{position:absolute;top:4px;right:4px;width:12px;height:12px;border-radius:50%;background:#ef4444;border:2px solid #fff;}
@media (min-width:768px){.chat-fab{bottom:1.5rem;}}

/* ===== PANEL ===== */
.chat-panel{position:fixed;right:1.25rem;bottom:9.5rem;z-index:60;width:380px;max-width:calc(100vw - 2rem);height:520px;max-height:calc(100vh - 12rem);
  background:#fff;border-radius:1.25rem;box-shadow:0 24px 60px rgba(15,23,42,.28);display:flex;flex-direction:column;overflow:hidden;
  opacity:0;transform:translateY(16px) scale(.96);pointer-events:none;transition:all .28s cubic-bezier(.34,1.4,.5,1);}
.chat-panel-open{opacity:1;transform:none;pointer-events:auto;}
@media (min-width:768px){.chat-panel{bottom:6.25rem;}}

.chat-header{background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:14px 16px;display:flex;align-items:center;justify-content:space-between;}
.chat-avatar{width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:20px;}
.chat-tab{flex:1;padding:10px 6px;font-size:12px;font-weight:700;color:#64748b;transition:.2s;border-bottom:2px solid transparent;}
.chat-tab-active{color:#4f46e5;border-color:#4f46e5;background:#fff;}
.chat-body{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:linear-gradient(180deg,#f8fafc,#fff);}

.chat-row-user{display:flex;justify-content:flex-end;gap:8px;animation:chatIn .25s ease;}
.chat-row-bot{display:flex;justify-content:flex-start;gap:8px;animation:chatIn .25s ease;}
@keyframes chatIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}

.chat-bubble-user{background:linear-gradient(135deg,#4f46e5,#6d28d9);color:#fff;border-radius:16px 16px 4px 16px;
  padding:9px 14px;font-size:13.5px;line-height:1.55;max-width:78%;word-wrap:break-word;}
.chat-bubble-bot{background:#fff;border:1px solid #e2e8f0;color:#1e293b;border-radius:16px 16px 16px 4px;
  padding:9px 14px;font-size:13.5px;line-height:1.55;max-width:78%;word-wrap:break-word;box-shadow:0 2px 6px rgba(15,23,42,.05);}

.chat-quick{font-size:12.5px;background:#fff;border:1px solid #c7d2fe;color:#4338ca;border-radius:12px;padding:8px 12px;
  text-align:left;transition:.2s;cursor:pointer;}
.chat-quick:hover{background:#eef2ff;transform:translateX(3px);}

.chat-guest{padding:14px;background:#eef2ff;border-top:1px solid #e0e7ff;}
.chat-footer{padding:12px;border-top:1px solid #eef2f7;background:#fff;display:flex;gap:8px;align-items:center;}
.chat-input{width:100%;border:1.5px solid #e2e8f0;border-radius:12px;padding:10px 14px;font-size:13.5px;outline:none;transition:.2s;background:#fff;}
.chat-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15);}
.chat-send{display:inline-flex;align-items:center;gap:4px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;
  border-radius:12px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;transition:.2s;flex-shrink:0;}
.chat-send:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 6px 16px rgba(79,70,229,.4);}
.chat-send:disabled{opacity:.5;cursor:not-allowed;}

.chat-dot{width:7px;height:7px;border-radius:50%;background:#94a3b8;display:inline-block;animation:chatBlink 1s infinite;}
@keyframes chatBlink{0%,100%{opacity:.3;transform:translateY(0)}50%{opacity:1;transform:translateY(-3px)}}
</style>

<script>
function cohubChat(config) {
    return {
        open: false,
        tab: config.aiEnabled ? 'ai' : 'admin',
        aiEnabled: config.aiEnabled,
        adminEnabled: config.adminEnabled,
        isGuest: config.isGuest,
        welcome: config.welcome,
        openUrl: config.openUrl,
        msgBase: config.msgBase || '/chat',
        conversationId: null,
        token: null,
        convStatus: 'open',
        messages: [],
        draft: '',
        guestName: '',
        guestEmail: '',
        loading: false,
        sending: false,
        typing: false,
        pollTimer: null,
        lastId: 0,
        quickQuestions: [
            '📝 Bagaimana cara booking properti?',
            '🛠 Apa saja kategori jasa yang tersedia?',
            '💳 Apa urutan status transaksinya?',
            '🏠 Bagaimana cara sewa properti?',
        ],

        init() {
            this.$watch('open', (val) => {
                clearInterval(this.pollTimer);
                if (val) {
                    this.startPolling();
                }
            });
        },

        switchTab(tab) {
            if (this.tab === tab) return;
            this.tab = tab;
            this.resetChat(false);
        },

        async openConversation() {
            if (!this.isGuest && this.conversationId) return;
            this.loading = true;
            try {
                const res = await fetch(this.openUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ type: this.tab, name: this.guestName || 'Tamu', email: this.guestEmail || null }),
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'Gagal membuka chat');
                this.conversationId = data.conversation.id;
                this.token = data.token;
                this.convStatus = data.conversation.status;
                this.setMessages(data.messages);
                this.scrollToBottom();
            } catch (e) {
                alert(e.message || 'Gagal membuka percakapan.');
            } finally {
                this.loading = false;
            }
        },

        async send() {
            const text = this.draft.trim();
            if (!text || this.sending) return;
            if (!this.conversationId) { await this.openConversation(); if (!this.conversationId) return; }

            this.sending = true;
            this.draft = '';
            // Tampilkan pesan user langsung (optimistic)
            this.messages.push({ id: 'tmp-' + Date.now(), sender: 'user', message: text, time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) });
            this.scrollToBottom();
            if (this.tab === 'ai') this.typing = true;

            try {
                const url = this.msgBase + '/' + this.conversationId + '/messages' + (this.token ? '?token=' + this.token : '');
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ message: text, token: this.token }),
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'Gagal mengirim pesan');
                this.setMessages(data.messages);
            } catch (e) {
                this.messages.push({ id: 'err-' + Date.now(), sender: 'bot', message: '⚠️ ' + (e.message || 'Gagal mengirim pesan. Coba lagi.'), time: '—' });
            } finally {
                this.typing = false;
                this.sending = false;
                this.scrollToBottom();
            }
        },

        sendQuick(q) {
            this.draft = q;
            this.send();
        },

        startPolling() {
            clearInterval(this.pollTimer);
            this.pollTimer = setInterval(async () => {
                if (!this.conversationId || this.tab !== 'admin' || this.sending) return;
                try {
                    const url = this.msgBase + '/' + this.conversationId + '/messages?after=' + this.lastId + (this.token ? '&token=' + this.token : '');
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (res.ok && data.ok) {
                        this.convStatus = data.status;
                        if (data.messages.length) {
                            this.setMessages(data.messages);
                            this.scrollToBottom();
                        }
                    }
                } catch (e) { /* diam */ }
            }, 5000);
        },

        setMessages(incoming) {
            incoming.forEach(m => {
                if (typeof m.id === 'number') this.lastId = Math.max(this.lastId, m.id);
                const i = this.messages.findIndex(x => x.id === m.id);
                if (i >= 0) this.messages.splice(i, 1, m); else this.messages.push(m);
            });
        },

        resetChat(confirmFirst = true) {
            if (confirmFirst && !confirm('Mulai percakapan baru? Riwayat tetap tersimpan di akun Anda.')) return;
            this.conversationId = null;
            this.token = null;
            this.messages = [];
            this.lastId = 0;
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.body;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        formatMessage(text) {
            const esc = String(text)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return esc
                .replace(/\*\*(.+?)\*\*/g, '<b>$1</b>')
                .replace(/\*(.+?)\*/g, '<i>$1</i>')
                .replace(/`(.+?)`/g, '<code>$1</code>')
                .replace(/\n/g, '<br>');
        },
    };
}
</script>
@endif
