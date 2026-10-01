// Konfirmasi hapus lewat atribut data-confirm.
// Dipindah dari atribut inline onsubmit="..." agar CSP bisa memakai script-src 'self' (tanpa 'unsafe-inline').
document.addEventListener('submit', function (ev) {
  var msg = ev.target && ev.target.getAttribute && ev.target.getAttribute('data-confirm');
  if (msg && !window.confirm(msg)) ev.preventDefault();
});
