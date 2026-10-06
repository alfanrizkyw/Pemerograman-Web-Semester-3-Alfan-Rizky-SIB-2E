<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

require_post();   // hapus hanya lewat POST + token CSRF
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

try {
    $st = $pdo->prepare('DELETE FROM buku WHERE id = :id');
    $st->execute([':id' => $id]);
    flash($st->rowCount() ? 'ok' : 'error', $st->rowCount() ? 'Buku dihapus.' : 'Buku tidak ditemukan.');
} catch (PDOException $ex) {
    if ($ex->getCode() !== '23503') { throw $ex; }
    flash('error', 'Buku ini punya riwayat peminjaman sehingga tidak bisa dihapus. Ubah stoknya menjadi 0 jika sudah tidak dipakai.');
}
redirect('buku/list.php');
