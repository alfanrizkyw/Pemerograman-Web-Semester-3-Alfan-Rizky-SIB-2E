<?php
require_once '../includes/koneksi.php';
require_once '../includes/auth.php';
cek_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $tahun     = (int)$_POST['tahun'];
    $isbn      = trim($_POST['isbn']);
    $stok      = (int)$_POST['stok'];
    $kategori  = trim($_POST['kategori']);

    if (!empty($judul) && !empty($pengarang) && $tahun > 0 && $stok >= 0) {
        $sql = "INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori) VALUES (:judul, :pengarang, :tahun, :isbn, :stok, :kategori)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'judul'     => $judul,
            'pengarang' => $pengarang,
            'tahun'     => $tahun,
            'isbn'      => $isbn,
            'stok'      => $stok,
            'kategori'  => $kategori
        ]);

        header("Location: list.php?status=sukses_tambah");
        exit;
    } else {
        header("Location: tambah.php?status=gagal");
        exit;
    }
}