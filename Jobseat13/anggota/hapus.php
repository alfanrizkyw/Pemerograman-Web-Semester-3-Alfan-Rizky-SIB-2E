<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

require_post();
require_petugas(); // petugas dan admin boleh menghapus anggota
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

try {
    $st = $pdo->prepare('DELETE FROM anggota WHERE id = :id');
    $st->execute([':id' => $id]);
    flash($st->rowCount() ? 'ok' : 'error', $st->rowCount() ? 'Anggota dihapus.' : 'Anggota tidak ditemukan.');
} catch (PDOException $ex) {
    if ($ex->getCode() !== '23503') { throw $ex; }
    flash('error', 'Anggota ini punya riwayat peminjaman sehingga tidak bisa dihapus.');
}
redirect('anggota/list.php');
