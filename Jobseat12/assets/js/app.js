/* SIMPUS-Mini — app.js (peningkatan progresif: semua fitur tetap jalan tanpa JS) */
(function () {
    'use strict';

    /* ── Menu samping (mobile) ── */
    var btn = document.getElementById('menuBtn');
    var side = document.getElementById('sidebar');
    var scrim = document.getElementById('scrim');

    function setMenu(open) {
        if (!btn || !side) { return; }
        side.classList.toggle('is-open', open);
        document.body.classList.toggle('no-scroll', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (scrim) { scrim.hidden = !open; }
        if (open) {
            var first = side.querySelector('a');
            if (first) { first.focus(); }
        }
    }

    if (btn && side) {
        btn.addEventListener('click', function () {
            setMenu(btn.getAttribute('aria-expanded') !== 'true');
        });
        if (scrim) { scrim.addEventListener('click', function () { setMenu(false); btn.focus(); }); }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && side.classList.contains('is-open')) { setMenu(false); btn.focus(); }
        });
        window.matchMedia('(min-width: 960px)').addEventListener('change', function (m) {
            if (m.matches) { setMenu(false); }
        });
    }

    /* ── Konfirmasi sebelum kirim form (hapus / kembalikan) ── */
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!window.confirm(f.getAttribute('data-confirm'))) { e.preventDefault(); }
        });
    });

    /* ── Saring baris tabel saat mengetik (server tetap mencari penuh saat Enter) ── */
    document.querySelectorAll('[data-live-filter]').forEach(function (input) {
        var table = document.querySelector(input.getAttribute('data-live-filter'));
        if (!table) { return; }
        input.addEventListener('input', function () {
            var term = input.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (tr) {
                tr.hidden = term !== '' && tr.textContent.toLowerCase().indexOf(term) === -1;
            });
        });
    });

    /* ── Validasi form di sisi klien (server tetap memvalidasi ulang) ── */
    function setError(field, msg) {
        var wrap = field.closest('.field');
        if (!wrap) { return; }
        var old = wrap.querySelector('.error');
        if (old) { old.remove(); }
        wrap.classList.toggle('has-error', !!msg);
        field.setAttribute('aria-invalid', msg ? 'true' : 'false');
        if (msg) {
            var s = document.createElement('span');
            s.className = 'error';
            s.textContent = msg;
            wrap.appendChild(s);
        }
    }

    function checkField(f) {
        var v = f.value.trim();
        var label = (f.closest('.field').querySelector('label') || {}).childNodes;
        var name = label && label[0] ? label[0].textContent.trim().toLowerCase() : 'kolom ini';
        var msg = '';
        if (f.required && v === '') {
            msg = (f.tagName === 'SELECT' ? 'Pilih ' : 'Isi ') + name + '.';
        } else if (v !== '' && f.type === 'number') {
            var n = Number(v);
            if (isNaN(n) || (f.min !== '' && n < Number(f.min)) || (f.max !== '' && n > Number(f.max))) {
                msg = 'Isi ' + name + ' antara ' + f.min + ' dan ' + f.max + '.';
            }
        } else if (v !== '' && f.minLength > 0 && v.length < f.minLength) {
            msg = 'Minimal ' + f.minLength + ' karakter.';
        }
        setError(f, msg);
        return msg === '';
    }

    document.querySelectorAll('#formBuku, #formAnggota, #formPinjam').forEach(function (form) {
        var fields = form.querySelectorAll('input:not([type=hidden]), select, textarea');
        fields.forEach(function (f) {
            f.addEventListener('blur', function () { if (f.value !== '' || f.dataset.touched) { checkField(f); } f.dataset.touched = '1'; });
            f.addEventListener('input', function () { if (f.closest('.has-error')) { checkField(f); } });
        });
        form.addEventListener('submit', function (e) {
            var bad = null;
            fields.forEach(function (f) { if (!checkField(f) && !bad) { bad = f; } });
            if (bad) { e.preventDefault(); bad.focus(); }
        });
    });

    /* ── Slip peminjaman: pratinjau langsung ── */
    var slip = document.getElementById('slip');
    if (slip) {
        var selA = document.getElementById('anggota_id');
        var selB = document.getElementById('buku_id');
        var outA = document.getElementById('slipAnggota');
        var outB = document.getElementById('slipBuku');
        var hint = document.getElementById('hintTelat');
        var submit = document.getElementById('btnPinjam');

        function fill(el, text, sub) {
            el.textContent = '';
            if (!text) { el.textContent = 'Belum dipilih'; el.classList.add('is-empty'); return; }
            el.classList.remove('is-empty');
            el.appendChild(document.createTextNode(text));
            if (sub) {
                var s = document.createElement('small');
                s.textContent = sub;
                el.appendChild(s);
            }
        }

        function refresh() {
            var a = selA.options[selA.selectedIndex];
            var b = selB.options[selB.selectedIndex];
            fill(outA, a && a.value ? a.dataset.nama : '', a && a.value ? a.dataset.no : '');
            fill(outB, b && b.value ? b.dataset.judul : '', b && b.value ? b.dataset.pengarang : '');
            var telat = !!(a && a.dataset.telat === '1');
            hint.hidden = !telat;
            submit.disabled = telat;
        }

        selA.addEventListener('change', refresh);
        selB.addEventListener('change', refresh);
        refresh();
    }
})();
