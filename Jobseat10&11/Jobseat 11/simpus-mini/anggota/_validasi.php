<?php
if (!defined('BASE_URL')) { http_response_code(403); exit; }   // partial: tidak boleh dibuka langsung lewat URL

/** Baca & validasi input anggota. Return [data, daftar_error]. */
function ambil_anggota(): array {
    $a = ['nama' => str_post('nama'), 'no_anggota' => str_post('no_anggota'),
          'alamat' => str_post('alamat'), 'no_hp' => str_post('no_hp')];
    $err = [];
    if ($a['nama'] === '' || $a['no_anggota'] === '') $err[] = 'Nama dan no. anggota wajib diisi.';
    if (!panjang_maks($a['nama'], 100)) $err[] = 'Nama maksimal 100 karakter.';
    // No. anggota: huruf/angka/-/_ / (3-50 karakter); mencegah karakter aneh & input kelewat panjang
    if ($a['no_anggota'] !== '' && !preg_match('/^[A-Za-z0-9\/_-]{3,50}$/D', $a['no_anggota'])) $err[] = 'No. anggota 3-50 karakter (huruf, angka, - _ /).';
    if (!panjang_maks($a['alamat'], 500)) $err[] = 'Alamat maksimal 500 karakter.';
    // No. HP opsional: boleh diawali +, sisanya angka/tanda hubung (8-20 karakter)
    if ($a['no_hp'] !== '' && !preg_match('/^\+?[0-9-]{8,20}$/D', $a['no_hp'])) $err[] = 'No. HP tidak valid.';
    return [$a, $err];
}

/** True bila exception adalah pelanggaran UNIQUE (SQLSTATE 23505); error DB lain jangan ditelan. */
function adalah_duplikat(PDOException $ex): bool { return ($ex->errorInfo[0] ?? '') === '23505'; }
