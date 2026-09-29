/* ============================================================
   CoHub IA ITB Jatim — Service Worker (PWA)
   Strategi:
   - Navigasi halaman : network-first -> cache -> offline.html
   - Aset statis      : stale-while-revalidate (css/js/img/font)
   - Permintaan lain  : network saja (POST/AJAX tidak di-cache)
   ============================================================ */

const VERSION = 'cohub-v1';
const CORE_CACHE = VERSION + '-core';
const RUNTIME_CACHE = VERSION + '-runtime';

/* Aset inti yang di-precache saat instalasi */
const CORE_ASSETS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/icons/apple-touch-icon.png',
    '/css/cohub.css',
    '/js/cohub.js',
    '/favicon.ico',
];

/* ---------- Install: precache aset inti ---------- */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CORE_CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

/* ---------- Activate: hapus cache versi lama ---------- */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => !key.startsWith(VERSION))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

/* ---------- Fetch ---------- */
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Hanya tangani GET dari origin sendiri
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // 1) Navigasi halaman (dokumen HTML): network-first
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));
        return;
    }

    // 2) Aset statis: stale-while-revalidate
    if (isStaticAsset(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // 3) Lainnya (AJAX/JSON, dll.): biarkan ke network (tanpa cache)
});

/* ---------- Strategi: navigasi network-first ---------- */
async function networkFirstNavigation(request) {
    try {
        const fresh = await fetch(request);

        // Simpan salinan halaman yang berhasil (untuk dibaca offline)
        if (fresh && fresh.ok) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, fresh.clone());
        }

        return fresh;
    } catch (err) {
        const cached = await caches.match(request, { ignoreSearch: true });
        if (cached) return cached;

        const offline = await caches.match('/offline.html');
        if (offline) return offline;

        return new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    }
}

/* ---------- Strategi: stale-while-revalidate ---------- */
async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);
    const network = fetch(request)
        .then((response) => {
            if (response && response.ok) {
                const clone = response.clone();
                caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, clone));
            }
            return response;
        })
        .catch(() => null); // offline & tidak ada cache -> biarkan gagal pelan

    return cached || (await network) || new Response('', { status: 504 });
}

/* ---------- Deteksi aset statis ---------- */
function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|webmanifest)$/i.test(pathname)
        || pathname.startsWith('/icons/')
        || pathname.startsWith('/storage/')
        || pathname.startsWith('/css/')
        || pathname.startsWith('/js/');
}

/* ---------- Pesan dari halaman: skip waiting (update langsung) ---------- */
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});
