/**
 * SIMPUS-Mini Main JavaScript File
 * Berisi fungsi interaktif dasar untuk aplikasi (misal: validasi form atau konfirmasi tambahan)
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log("SIMPUS-Mini JS Loaded Successfully.");

    // Contoh: Otomatis menutup alert pesan sukses/gagal setelah beberapa detik (opsional)
    const alertBoxes = document.querySelectorAll('.alert');
    if (alertBoxes.length > 0) {
        setTimeout(() => {
            alertBoxes.forEach(box => {
                box.style.transition = 'opacity 0.5s ease';
                box.style.opacity = '0';
                setTimeout(() => box.remove(), 500);
            });
        }, 4000);
    }
});