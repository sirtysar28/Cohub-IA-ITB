/* ============================================================
   CoHub IA ITB Jatim — Interaksi UI
   Loader (login & pindah halaman), parallax, scroll reveal,
   refresh captcha, minimize sidebar/bottom-nav
   ============================================================ */
(function () {
    'use strict';

    /* ================= LOADER OVERLAY ================= */

    window.cohubShowLoader = function (message) {
        var loader = document.getElementById('cohub-loader');
        var text = document.getElementById('cohub-loader-text');
        var bar = document.getElementById('cohub-progress');
        if (text && message) text.textContent = message;
        if (loader) loader.classList.add('active');
        if (bar) { bar.classList.add('active'); bar.style.width = '70%'; }
    };

    window.cohubHideLoader = function () {
        var loader = document.getElementById('cohub-loader');
        var bar = document.getElementById('cohub-progress');
        if (loader) loader.classList.remove('active');
        if (bar) { bar.style.width = '100%'; setTimeout(function () { bar.classList.remove('active'); bar.style.width = '0'; }, 350); }
    };

    window.addEventListener('pageshow', function () {
        window.cohubHideLoader();
        // Tombol loading di form dikembalikan normal (saat back-button browser)
        document.querySelectorAll('[data-loading-text]').forEach(function (btn) {
            btn.disabled = false;
            if (btn.dataset.originalHtml !== undefined) btn.innerHTML = btn.dataset.originalHtml;
        });
    });

    document.addEventListener('submit', function (e) {
        // Form dibatalkan (mis. confirm() hapus -> "Cancel"): jangan tampilkan loader/spinner
        if (e.defaultPrevented) return;

        var form = e.target;
        if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loader')) return;

        // Tampilkan spinner di tombol submit (jika di-flag)
        var btn = form.querySelector('button[type="submit"][data-loading-text], button:not([type])[data-loading-text]');
        if (btn) {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML =
                '<span class="inline-flex items-center justify-center gap-2">' +
                '<span class="loader-spinner sm"></span>' + btn.getAttribute('data-loading-text') +
                '</span>';
        }

        // Untuk form GET biasa (filter) cukup progress bar, tanpa overlay penuh
        var isGet = (form.getAttribute('method') || 'get').toUpperCase() === 'GET';
        if (isGet) {
            var barGet = document.getElementById('cohub-progress');
            if (barGet) { barGet.classList.add('active'); barGet.style.width = '45%'; }
            return;
        }

        window.cohubShowLoader('Memproses…');
    });

    // Klik link internal → progress bar + fade out halaman
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var a = e.target.closest ? e.target.closest('a[href]') : null;
        if (!a) return;

        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || a.hasAttribute('download') || a.hasAttribute('data-no-loader') ||
            a.getAttribute('target') === '_blank') return;

        try {
            var url = new URL(a.href, window.location.href);
            if (url.origin !== window.location.origin) return;
        } catch (err) { return; }

        var bar = document.getElementById('cohub-progress');
        if (bar) { bar.classList.add('active'); bar.style.width = '45%'; }
    });

    /* ================= SCROLL REVEAL ================= */

    function initReveal() {
        var items = document.querySelectorAll('.reveal:not(.revealed)');
        if (!('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('revealed'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        items.forEach(function (el) { io.observe(el); });
    }

    /* ================= PARALLAX (hero) ================= */

    function initParallax() {
        var hero = document.querySelector('[data-parallax]');
        if (!hero || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        var layers = hero.querySelectorAll('[data-parallax-speed]');
        var orbs = hero.querySelectorAll('[data-parallax-orb]');

        // Parallax mengikuti gerakan mouse (desktop)
        if (window.matchMedia('(pointer: fine)').matches) {
            hero.addEventListener('mousemove', function (e) {
                var rect = hero.getBoundingClientRect();
                var x = (e.clientX - rect.left) / rect.width - 0.5;
                var y = (e.clientY - rect.top) / rect.height - 0.5;
                layers.forEach(function (layer) {
                    var speed = parseFloat(layer.getAttribute('data-parallax-speed')) || 10;
                    layer.style.transform = 'translate3d(' + (-x * speed) + 'px,' + (-y * speed) + 'px,0)';
                });
                orbs.forEach(function (orb) {
                    var speed = parseFloat(orb.getAttribute('data-parallax-orb')) || 20;
                    orb.style.transform = 'translate3d(' + (x * speed) + 'px,' + (y * speed) + 'px,0)';
                });
            });
            hero.addEventListener('mouseleave', function () {
                layers.forEach(function (l) { l.style.transform = 'translate3d(0,0,0)'; });
                orbs.forEach(function (o) { o.style.transform = 'translate3d(0,0,0)'; });
            });
        }

        // Parallax mengikuti scroll
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                var rect = hero.getBoundingClientRect();
                if (rect.bottom > 0) {
                    var offset = window.scrollY * 0.25;
                    var content = hero.querySelector('[data-parallax-content]');
                    if (content) content.style.transform = 'translate3d(0,' + offset * 0.35 + 'px,0)';
                    layers.forEach(function (layer) {
                        var speed = parseFloat(layer.getAttribute('data-parallax-speed')) || 10;
                        layer.style.setProperty('--scroll', offset * (speed / 40));
                    });
                }
                ticking = false;
            });
        }, { passive: true });
    }

    /* ================= REFRESH CAPTCHA ================= */

    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('[data-captcha-refresh]') : null;
        if (!btn) return;
        e.preventDefault();

        fetch(btn.getAttribute('data-captcha-refresh'), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var box = document.getElementById('captcha-box');
                if (!box) return;
                box.innerHTML = '';
                (data.code || '').split('').forEach(function (ch, i) {
                    var span = document.createElement('span');
                    span.className = 'captcha-char';
                    span.textContent = ch;
                    span.setAttribute('style', (data.styles || [])[i] || '');
                    box.appendChild(span);
                });
                var input = document.getElementById('captcha-input');
                if (input) input.value = '';
            })
            .catch(function () { window.location.reload(); });
    });

    /* ================= HAMBURGER MINIMIZE SIDEBAR (desktop) =================
       Desktop (md+): klik hamburger di header -> sidebar mengecil jadi rail ikon.
       Mobile sudah memakai tombol drawer (buka/tutup sidebar). */

    function initMinimize() {
        var btn = document.getElementById('minimize-btn');
        var sidebar = document.getElementById('sidebar');
        if (!btn || !sidebar) return;

        var mqDesktop = window.matchMedia('(min-width: 768px)');

        function readState() {
            try { return JSON.parse(localStorage.getItem('cohub-minimized') || 'null'); } catch (e) { return null; }
        }

        function apply(minimized, persist) {
            var desktop = mqDesktop.matches;
            // Rail minimize hanya berlaku di desktop; di mobile sidebar tetap drawer penuh
            sidebar.classList.toggle('minimized', minimized && desktop);
            btn.classList.toggle('is-minimized', minimized && desktop);
            btn.setAttribute('aria-label', minimized ? 'Tampilkan menu penuh' : 'Minimalkan menu');
            btn.title = minimized ? 'Tampilkan menu penuh' : 'Minimalkan menu';
            if (persist) { try { localStorage.setItem('cohub-minimized', JSON.stringify(minimized)); } catch (e) {} }
        }

        var state = readState();
        if (state !== null) apply(state, false);

        btn.addEventListener('click', function () {
            apply(!sidebar.classList.contains('minimized'), true);
        });

        // Re-apply saat ukuran layar berubah (mobile <-> desktop)
        var onMedia = function () { apply(readState() === true || sidebar.classList.contains('minimized'), false); };
        if (mqDesktop.addEventListener) mqDesktop.addEventListener('change', onMedia);
    }

    /* ================= KARTU AKSI DASHBOARD (kilau kursor + ripple) ================= */

    function initCardEffects() {
        document.querySelectorAll('.action-card').forEach(function (card) {
            // Kilau radial mengikuti posisi kursor
            card.addEventListener('pointermove', function (e) {
                var rect = card.getBoundingClientRect();
                card.style.setProperty('--mx', ((e.clientX - rect.left) / rect.width * 100) + '%');
                card.style.setProperty('--my', ((e.clientY - rect.top) / rect.height * 100) + '%');
            });

            // Ripple saat diklik (feedback taktil halus)
            card.addEventListener('pointerdown', function (e) {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                var rect = card.getBoundingClientRect();
                var size = Math.max(rect.width, rect.height) * 0.45;
                var ripple = document.createElement('span');
                ripple.className = 'ripple';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
                card.appendChild(ripple);
                setTimeout(function () { ripple.remove(); }, 700);
            });
        });
    }

    /* ================= TOGGLE INTIP PASSWORD ================= */

    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('[data-pw-toggle]') : null;
        if (!btn) return;
        e.preventDefault();
        var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? '🙈' : '👁️';
        btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Lihat password');
    });

    /* ================= PWA: REGISTER SERVICE WORKER ================= */

    function initPWA() {
        if (!('serviceWorker' in navigator)) return;

        // SW hanya jalan di http(s) — skip di file://
        if (location.protocol !== 'http:' && location.protocol !== 'https:') return;

        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {
                // Registrasi gagal (mis. belum HTTPS) — aplikasi tetap jalan normal
            });
        });
    }

    /* ================= INIT ================= */

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initReveal(); initParallax(); initMinimize(); initCardEffects(); initPWA(); });
    } else {
        initReveal(); initParallax(); initMinimize(); initCardEffects(); initPWA();
    }
})();
