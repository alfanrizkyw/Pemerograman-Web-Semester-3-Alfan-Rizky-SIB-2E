<?php
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = (int)$_POST['id'];
    $judul     = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $tahun     = (int)$_POST['tahun'];
    $isbn      = trim($_POST['isbn']);
    $stok      = (int)$_POST['stok'];
    $kategori  = trim($_POST['kategori']);

    if (!empty($judul) && !empty($pengarang) && $tahun > 0 && $stok >= 0) {
        $sql = "UPDATE buku SET judul = :judul, pengarang = :pengarang, tahun = :tahun, isbn = :isbn, stok = :stok, kategori = :kategori WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id'        => $id,
            'judul'     => $judul,
            'pengarang' => $pengarang,
            'tahun'     => $tahun,
            'isbn'      => $isbn,
            'stok'      => $stok,
            'kategori'  => $kategori
        ]);

        header("Location: list.php?status=sukses_edit");
        exit;
    } else {
        header("Location: edit.php?id=$id&status=gagal");
        exit;
    }
}