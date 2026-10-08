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

    /* ---------- password strength meter (registration + profile) ----------
       Markup lives in resources/views/partials/password-strength.blade.php and
       carries data-pw / data-confirm / data-match / data-common, so this stays
       in one place instead of being duplicated per form. */
    function initPasswordMeter(meter) {
        var pw = document.getElementById(meter.getAttribute('data-pw'));
        var confirmEl = document.getElementById(meter.getAttribute('data-confirm'));
        var matchBox = document.getElementById(meter.getAttribute('data-match'));
        if (!pw || !pw.form || !confirmEl) return;

        var track = meter.querySelector('.pw-track');
        var label = meter.querySelector('.pw-label');
        var hint = meter.querySelector('.pw-hint');
        var segments = Array.prototype.slice.call(meter.querySelectorAll('.pw-seg'));
        var items = Array.prototype.slice.call(meter.querySelectorAll('.pw-rules li'));
        var optional = meter.getAttribute('data-optional') === '1';

        var common = [];
        try { common = JSON.parse(meter.getAttribute('data-common') || '[]'); } catch (err) { common = []; }

        /* Mirrors PasswordPolicy: same run regex, same five-or-more repeat. */
        var RUN_RE = /(?:0123456789|abcdefghij|qwertyuiop|asdfghjkl|zxcvbnm|abcdefghijkl)/i;
        var RE_LOWER = /\p{Ll}/u;
        var RE_UPPER = /\p{Lu}/u;
        var RE_DIGIT = /\p{N}/u;
        var RE_SYMBOL = /[^\p{L}\p{N}]/u;

        var LABELS = ['', 'Too weak', 'Weak', 'Fair', 'Good', 'Strong'];
        var HINTS = {
            len: 'Use at least 8 characters',
            case: 'Mix upper and lower case letters',
            num: 'Add at least one number',
            sym: 'Add a symbol like #, ! or .',
            orig: 'Avoid common, repeated or keyboard patterns'
        };

        var nameInput = document.querySelector('input[name="name"]');
        var emailInput = document.querySelector('input[name="email"]');
        var staticEmail = meter.getAttribute('data-email') || '';

        function identity() {
            var email = emailInput ? emailInput.value : staticEmail;
            var at = email.indexOf('@');
            return {
                local: at > 0 ? email.slice(0, at).trim().toLowerCase() : '',
                name: nameInput ? nameInput.value.replace(/\s+/g, '').trim().toLowerCase() : ''
            };
        }

        function isOriginal(value, id) {
            if (common.indexOf(value.toLowerCase()) !== -1) return false;
            if (/(.)\1{4,}/.test(value)) return false;
            if (RUN_RE.test(value)) return false;
            if (id.local.length >= 3 && value.toLowerCase().indexOf(id.local) !== -1) return false;
            if (id.name.length >= 4 && value.toLowerCase().indexOf(id.name) !== -1) return false;
            return true;
        }

        function score(value, checks, unmet) {
            var pts = 0;
            if (value.length >= 8) pts += 1;
            if (value.length >= 10) pts += 1;
            if (value.length >= 12) pts += 1;
            if (value.length >= 14) pts += 1;
            if (value.length >= 16) pts += 1;

            var classes = 0;
            if (RE_LOWER.test(value)) { pts++; classes++; }
            if (RE_UPPER.test(value)) { pts++; classes++; }
            if (RE_DIGIT.test(value)) { pts++; classes++; }
            if (RE_SYMBOL.test(value)) { pts++; classes++; }
            if (classes >= 3) pts++;

            var unique = new Set(value).size;
            if (unique >= 8) pts += 1;
            if (value.length >= 8 && unique / value.length <= 0.5) pts -= 1;

            if (/(.)\1{4,}/.test(value)) pts -= 2;
            if (RUN_RE.test(value)) pts -= 2;
            if (!checks.orig) pts -= 3;

            var level = pts <= 2 ? 1 : pts <= 4 ? 2 : pts <= 6 ? 3 : pts <= 8 ? 4 : 5;

            /* Never rate above "Weak" until every requirement is ticked - otherwise
               the bar contradicts the red crosses right underneath it. */
            if (unmet >= 2) return 1;
            if (unmet === 1) return Math.min(level, 2);
            return level;
        }

        function render(level, checks) {
            var i;
            for (i = 0; i <= 5; i++) meter.classList.remove('lv' + i);
            meter.classList.add('lv' + level);

            for (i = 0; i < segments.length; i++) segments[i].classList.toggle('on', i < level);

            if (label) label.textContent = level === 0 ? 'Password strength' : LABELS[level];
            if (track) {
                track.setAttribute('aria-valuenow', level);
                track.setAttribute('aria-valuetext', level === 0 ? 'Empty' : LABELS[level]);
            }
            if (!hint) return;

            if (!pw.value) {
                hint.textContent = meter.getAttribute('data-blank-hint') || 'Start typing to see how strong it is';
                return;
            }

            var firstUnmet = items.filter(function (li) {
                return !checks[li.getAttribute('data-rule')];
            })[0];

            if (firstUnmet) hint.textContent = HINTS[firstUnmet.getAttribute('data-rule')];
            else if (level < 5) hint.textContent = 'All requirements met - longer is still stronger';
            else hint.textContent = 'Excellent - safe to use';
        }

        function evaluate() {
            var value = pw.value;
            var id = identity();

            var checks = {
                len: value.length >= 8 && value.length <= 64,
                case: RE_LOWER.test(value) && RE_UPPER.test(value),
                num: RE_DIGIT.test(value),
                sym: RE_SYMBOL.test(value),
                orig: value.length > 0 && isOriginal(value, id)
            };

            var unmet = 0;
            items.forEach(function (li) {
                var ok = !!checks[li.getAttribute('data-rule')];
                li.classList.toggle('ok', ok);
                if (!ok) unmet++;
            });

            render(value ? score(value, checks, unmet) : 0, checks);
        }

        function renderMatch() {
            if (!matchBox) return;
            if (!confirmEl.value) {
                matchBox.hidden = true;
                matchBox.classList.remove('yes', 'no');
                matchBox.textContent = '';
                return;
            }
            var ok = confirmEl.value === pw.value;
            matchBox.hidden = false;
            matchBox.classList.toggle('yes', ok);
            matchBox.classList.toggle('no', !ok);
            matchBox.textContent = ok ? 'Passwords match.' : 'Passwords do not match yet.';
        }

        pw.addEventListener('input', function () { evaluate(); renderMatch(); });
        confirmEl.addEventListener('input', renderMatch);

        /* Re-check when the resident edits the details a password must not contain. */
        if (nameInput) nameInput.addEventListener('input', evaluate);
        if (emailInput) emailInput.addEventListener('input', evaluate);

        /* Only block what the server would reject anyway. */
        pw.form.addEventListener('submit', function (e) {
            if (optional && !pw.value && !confirmEl.value) return;

            var failed = items.filter(function (li) { return !li.classList.contains('ok'); })[0];
            if (failed) {
                e.preventDefault();
                pw.focus();
                evaluate();
                return;
            }
            if (confirmEl.value !== pw.value) {
                e.preventDefault();
                (confirmEl.value ? confirmEl : pw).focus();
                renderMatch();
            }
        });

        evaluate();
        renderMatch();
    }

    function bootPasswordMeters() {
        document.querySelectorAll('.js-pw-meter').forEach(initPasswordMeter);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootPasswordMeters);
    } else {
        bootPasswordMeters();
    }
})();
