{{-- Notifikasi live: badge 🔔 selalu update saat ada request/transaksi/chat baru
     (polling ringan tiap 20 detik) + toast untuk notifikasi yang baru masuk --}}
<div id="notif-toasts" class="fixed top-4 right-4 z-[70] space-y-2 w-80 max-w-[calc(100vw-2rem)]"></div>

<template id="notif-toast-template">
    <div class="notif-toast">
        <span class="notif-toast-icon">🔔</span>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-indigo-900">Notifikasi Baru — CoHub</p>
            <p class="notif-toast-msg text-xs text-gray-600 leading-snug"></p>
        </div>
        <button class="notif-toast-close" aria-label="Tutup">✕</button>
    </div>
</template>

<style>
.notif-toast{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e0e7ff;border-left:4px solid #4f46e5;
  border-radius:14px;padding:12px 14px;box-shadow:0 12px 32px rgba(49,46,129,.18);animation:toastIn .35s cubic-bezier(.34,1.4,.5,1);}
.notif-toast.out{animation:toastOut .3s ease forwards;}
.notif-toast-icon{font-size:20px;}
.notif-toast-close{color:#94a3b8;font-size:12px;padding:2px;}
.notif-toast-close:hover{color:#475569;}
@keyframes toastIn{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:none}}
@keyframes toastOut{to{opacity:0;transform:translateX(40px)}}
</style>

<script>
(function () {
    'use strict';

    var POLL_URL = '{{ route('notifications.poll') }}';
    var USER_KEY = 'cohub-last-notif-{{ auth()->id() }}';
    var READ_URL = '{{ route('notifications.read-all') }}';

    function csrf() { return document.querySelector('meta[name=csrf-token]')?.content || ''; }

    function showToast(message, url) {
        var wrap = document.getElementById('notif-toasts');
        var tpl = document.getElementById('notif-toast-template');
        if (!wrap || !tpl) return;

        var node = tpl.content.firstElementChild.cloneNode(true);
        node.querySelector('.notif-toast-msg').textContent = message;
        node.addEventListener('click', function (e) {
            if (e.target.closest('.notif-toast-close')) return dismiss();
            if (url) window.location.href = url;
        });

        function dismiss() {
            node.classList.add('out');
            setTimeout(function () { node.remove(); }, 300);
        }

        node.querySelector('.notif-toast-close').addEventListener('click', dismiss);
        wrap.appendChild(node);
        setTimeout(dismiss, 7000);
    }

    function updateBadge(count) {
        var badge = document.getElementById('notif-badge');
        if (!badge) return;
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = count > 0 ? '' : 'none';
    }

    function updateChatBadge(count) {
        var el = document.getElementById('chat-menu-badge');
        if (!el) return;
        el.textContent = count > 0 ? count : '';
        el.style.display = count > 0 ? '' : 'none';
    }

    function renderList(items) {
        var list = document.getElementById('notif-list');
        if (!list) return;

        if (!items.length) {
            list.innerHTML = '<p class="px-4 py-6 text-sm text-gray-400 text-center">Belum ada notifikasi.</p>';
            return;
        }

        list.innerHTML = items.map(function (n) {
            return '<a href="' + n.url + '" data-notif-id="' + n.id + '" class="notif-live-item block px-4 py-3 border-b hover:bg-gray-50 text-sm ' + (n.read ? 'opacity-60' : '') + '">'
                + '<p>' + n.message + '</p>'
                + '<p class="text-xs text-gray-400 mt-0.5">' + n.time + '</p></a>';
        }).join('');

        // Tandai dibaca via endpoint yang ada saat link diklik
        list.querySelectorAll('.notif-live-item').forEach(function (a) {
            a.addEventListener('click', function () {
                fetch(a.dataset.notifId ? '{{ url('/notifications') }}/' + a.dataset.notifId + '/read' : '#', {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                }).catch(function () {});
            });
        });
    }

    var lastKnown = null;
    try { lastKnown = localStorage.getItem(USER_KEY); } catch (e) {}

    function tick() {
        fetch(POLL_URL, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                updateBadge(data.unread || 0);
                updateChatBadge(data.chat_unread || 0);
                renderList(data.items || []);

                var latest = data.latest_id;
                if (latest) {
                    if (lastKnown && lastKnown !== latest && (data.items || []).length) {
                        var newest = data.items.find(function (i) { return i.id === latest && !i.read; });
                        if (newest) showToast(newest.message, newest.url);
                    }
                    lastKnown = latest;
                    try { localStorage.setItem(USER_KEY, latest); } catch (e) {}
                }
            })
            .catch(function () { /* offline: abaikan */ });
    }

    tick();
    setInterval(tick, 20000);

    // Toast flash pesan sukses halaman (menggunakan notifikasi baru)
    document.addEventListener('DOMContentLoaded', tick);
})();
</script>
