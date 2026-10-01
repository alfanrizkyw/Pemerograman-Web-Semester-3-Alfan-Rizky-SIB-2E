<?php
if (!defined('BASE_URL')) { http_response_code(403); exit; }   // partial: tidak boleh dibuka langsung lewat URL

// Whitelist kategori: SATU sumber kebenaran, dipakai oleh form (dropdown) dan validasi server
const KATEGORI_BUKU = ['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Referensi'];

/** Baca & validasi input buku. Return [data, daftar_error]. Jika tanpa error, tahun & stok sudah bertipe int. */
function ambil_buku(): array {
    $b = ['judul' => str_post('judul'), 'pengarang' => str_post('pengarang'), 'tahun' => str_post('tahun'),
          'isbn'  => str_post('isbn'),  'stok'      => str_post('stok'),      'kategori' => str_post('kategori')];
    $err = [];

    if ($b['judul'] === '' || $b['pengarang'] === '') $err[] = 'Judul dan pengarang wajib diisi.';
    if (!panjang_maks($b['judul'], 255) || !panjang_maks($b['pengarang'], 255)) $err[] = 'Judul dan pengarang maksimal 255 karakter.';

    // Tipe data: filter_var + rentang (menolak "1e3", "abc", "2020abc", nilai raksasa)
    $tahun = filter_var($b['tahun'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => (int)date('Y')]]);
    if ($tahun === false) $err[] = 'Tahun tidak valid (1900 - ' . date('Y') . ').';
    $stok = filter_var($b['stok'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
    if ($stok === false) $err[] = 'Stok harus bilangan bulat 0 - 100000.';

    // ISBN opsional; bila diisi: angka, tanda hubung, atau X (10-17 karakter)
    if ($b['isbn'] !== '' && !preg_match('/^[0-9Xx-]{10,17}$/D', $b['isbn'])) $err[] = 'ISBN hanya boleh angka, tanda hubung, atau X (10-17 karakter).';

    // Whitelist (strict): hanya nilai yang ada di daftar
    if (!in_array($b['kategori'], KATEGORI_BUKU, true)) $err[] = 'Kategori tidak valid.';

    if (!$err) { $b['tahun'] = $tahun; $b['stok'] = $stok; }
    return [$b, $err];
}
