<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul   = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $tahun   = trim($_POST['tahun']);
    $stok    = trim($_POST['stok']);

    // Validasi server-side
    if (empty($judul) || empty($penulis) || empty($tahun) || empty($stok)) {
        $_SESSION['error'] = "Semua field wajib diisi!";
        header("Location: tambah.php");
        exit;
    }

    if (!is_numeric($tahun)) {
        $_SESSION['error'] = "Tahun terbit harus berupa angka!";
        header("Location: tambah.php");
        exit;
    }

    if (!is_numeric($stok) || $stok < 0) {
        $_SESSION['error'] = "Stok harus berupa angka dan minimal 0!";
        header("Location: tambah.php");
        exit;
    }

    // Inisialisasi session array buku jika belum ada
    if (!isset($_SESSION['buku'])) {
        $_SESSION['buku'] = [];
    }

    // Simpan data ke session
    $_SESSION['buku'][] = [
        'judul'   => htmlspecialchars($judul),
        'penulis' => htmlspecialchars($penulis),
        'tahun'   => (int)$tahun,
        'stok'    => (int)$stok
    ];

    $_SESSION['sukses'] = "Data buku berhasil ditambahkan!";
    header("Location: list.php");
    exit;
} else {
    header("Location: tambah.php");
    exit;
}
?>