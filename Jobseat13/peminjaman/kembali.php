<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

require_post();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

try {
    $pdo->beginTransaction();

    $st = $pdo->prepare('SELECT p.buku_id, p.status, b.judul, a.nama
                         FROM peminjaman p JOIN buku b ON b.id = p.buku_id JOIN anggota a ON a.id = p.anggota_id
                         WHERE p.id = :id FOR UPDATE OF p');
    $st->execute([':id' => $id]);
    $p = $st->fetch();

    if (!$p) {
        $pdo->rollBack();
        flash('error', 'Data peminjaman tidak ditemukan.');
    } elseif ($p['status'] !== 'dipinjam') {
        // Klik ganda / tab lama: jangan menambah stok dua kali.
        $pdo->rollBack();
        flash('error', 'Buku ini sudah tercatat kembali.');
    } else {
        $pdo->prepare("UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = now() WHERE id = :id")
            ->execute([':id' => $id]);
        $pdo->prepare('UPDATE buku SET stok = stok + 1 WHERE id = :id')->execute([':id' => $p['buku_id']]);
        $pdo->commit();
        flash('ok', '“' . $p['judul'] . '” dari ' . $p['nama'] . ' tercatat kembali.');
    }
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    throw $ex;
}
redirect('peminjaman/list.php');
