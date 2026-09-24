<?php
function ambil_buku() {
    $b = ['judul' => trim($_POST['judul'] ?? ''), 'pengarang' => trim($_POST['pengarang'] ?? ''),
          'tahun' => trim($_POST['tahun'] ?? ''), 'isbn' => trim($_POST['isbn'] ?? ''),
          'stok' => trim($_POST['stok'] ?? ''), 'kategori' => $_POST['kategori'] ?? ''];
    $err = [];
    if ($b['judul'] === '' || $b['pengarang'] === '') $err[] = 'Judul dan pengarang wajib diisi.';
    if (!ctype_digit($b['tahun']) || $b['tahun'] < 1900 || $b['tahun'] > date('Y')) $err[] = 'Tahun tidak valid.';
    if (!ctype_digit($b['stok'])) $err[] = 'Stok harus angka >= 0.';
    if (!in_array($b['kategori'], ['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Referensi'], true)) $err[] = 'Kategori tidak valid.';
    return [$b, $err];
}
