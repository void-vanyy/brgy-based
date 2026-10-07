/* =========================================================================
   Barangay Sigla — shared UI behaviour
   ========================================================================= */

(function () {
    'use strict';

    /* ---------- toast notifications (server flash + client) ---------- */
    function ensureToasts() {
        var el = document.getElementById('toasts');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toasts';
            document.body.appendChild(el);
        }
        return el;
    }

    window.toast = function (message, type) {
        type = type || 'info';
        var host = ensureToasts();
        var node = document.createElement('div');
        node.className = 'toast ' + type;
        node.innerHTML =
            '<span class="grow">' + escapeHtml(message) + '</span>' +
            '<button class="close" aria-label="Dismiss">&times;</button>';
        node.querySelector('.close').addEventListener('click', function () { node.remove(); });
        host.appendChild(node);
        setTimeout(function () {
            node.style.transition = 'opacity .3s, transform .3s';
            node.style.opacity = '0';
            node.style.transform = 'translateX(40px)';
            setTimeout(function () { node.remove(); }, 320);
        }, 4600);
    };

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* Render Laravel flash messages passed through the layout. */
    document.addEventListener('DOMContentLoaded', function () {
        var flash = document.querySelectorAll('[data-flash]');
        flash.forEach(function (node) {
            toast(node.getAttribute('data-flash'), node.getAttribute('data-flash-type') || 'info');
            node.remove();
        });
    });

    /* ---------- sidebar (mobile) ---------- */
    document.addEventListener('click', function (e) {
        var burger = e.target.closest('[data-sidebar-toggle]');
        if (burger) {
            var side = document.querySelector('.sidebar');
            var back = document.querySelector('.sidebar-backdrop');
            if (side) side.classList.toggle('open');
            if (back) back.classList.toggle('show', side && side.classList.contains('open'));
            return;
        }
        if (e.target.classList && e.target.classList.contains('sidebar-backdrop')) {
            e.target.classList.remove('show');
            var s = document.querySelector('.sidebar');
            if (s) s.classList.remove('open');
        }
    });

    /* ---------- modals ---------- */
    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-modal-open]');
        if (opener) {
            e.preventDefault();
            var target = document.getElementById(opener.getAttribute('data-modal-open'));
            if (target) {
                target.classList.add('open');
                document.body.style.overflow = 'hidden';
                var first = target.querySelector('input, select, textarea, button');
                if (first) setTimeout(function () { first.focus(); }, 60);
            }
            return;
        }

        if (e.target.closest('[data-modal-close]') ||
            (e.target.classList && e.target.classList.contains('modal-backdrop'))) {
            var open = document.querySelector('.modal.open');
            if (open) {
                open.classList.remove('open');
                document.body.style.overflow = '';
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var open = document.querySelector('.modal.open');
            if (open) {
                open.classList.remove('open');
                document.body.style.overflow = '';
            }
        }
    });

    /* ---------- destructive form confirmation ---------- */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-confirm')) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        }
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-confirm-click]');
        if (btn && !window.confirm(btn.getAttribute('data-confirm-click'))) {
            e.preventDefault();
        }
    });

    /* ---------- client-side table filtering ---------- */
    document.addEventListener('input', function (e) {
        var input = e.target.closest('[data-filter]');
        if (!input) return;

        var scope = document.querySelector(input.getAttribute('data-filter'));
        if (!scope) return;

        var term = input.value.toLowerCase().trim();
        var rows = scope.querySelectorAll('[data-search]');
        var shown = 0;

        rows.forEach(function (row) {
            var hit = row.getAttribute('data-search').toLowerCase().indexOf(term) !== -1;
            row.style.display = hit ? '' : 'none';
            if (hit) shown++;
        });

        var counter = document.querySelector('[data-filter-count]');
        if (counter) counter.textContent = shown;

        var none = scope.querySelector('[data-filter-empty]');
        if (none) none.style.display = shown === 0 ? '' : 'none';
    });

    /* ---------- chip filters (same table, class based) ---------- */
    document.addEventListener('click', function (e) {
        var chip = e.target.closest('[data-chip-filter]');
        if (!chip) return;

        var group = chip.closest('[data-chip-group]');
        if (group) {
            group.querySelectorAll('[data-chip-filter]').forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
        }

        var scope = document.querySelector(chip.getAttribute('data-chip-filter'));
        if (!scope) return;

        var value = chip.getAttribute('data-chip-value') || '';
        scope.querySelectorAll('[data-chip-item]').forEach(function (item) {
            var tags = (item.getAttribute('data-chip-item') || '').split(' ');
            item.style.display = (value === '' || tags.indexOf(value) !== -1) ? '' : 'none';
        });
    });

    /* ---------- copy-to-clipboard ---------- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-copy]');
        if (!btn) return;
        e.preventDefault();
        var text = btn.getAttribute('data-copy');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () { toast('Copied: ' + text, 'success'); });
        } else {
            toast('Reference: ' + text, 'info');
        }
    });

    /* ---------- live clock on queue board ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var clocks = document.querySelectorAll('[data-clock]');
        if (!clocks.length) return;
        var tick = function () {
            var now = new Date();
            var txt = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            clocks.forEach(function (c) { c.textContent = txt; });
        };
        tick();
        setInterval(tick, 1000);
    });

    /* ---------- auto-refresh a block from a JSON endpoint ---------- */
    window.pollUrl = function (url, handler, interval) {
        var run = function () {
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) { if (data) handler(data); })
                .catch(function () {});
        };
        run();
        setInterval(run, interval || 5000);
    };
})();
