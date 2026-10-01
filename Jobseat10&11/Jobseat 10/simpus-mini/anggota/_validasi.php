<?php
function ambil_anggota() {
    $a = ['nama' => trim($_POST['nama'] ?? ''), 'no_anggota' => trim($_POST['no_anggota'] ?? ''),
          'alamat' => trim($_POST['alamat'] ?? ''), 'no_hp' => trim($_POST['no_hp'] ?? '')];
    $err = [];
    if ($a['nama'] === '' || $a['no_anggota'] === '') $err[] = 'Nama dan no. anggota wajib diisi.';
    if ($a['no_hp'] !== '' && !preg_match('/^[0-9+\-]{8,20}$/', $a['no_hp'])) $err[] = 'No. HP tidak valid.';
    return [$a, $err];
}
