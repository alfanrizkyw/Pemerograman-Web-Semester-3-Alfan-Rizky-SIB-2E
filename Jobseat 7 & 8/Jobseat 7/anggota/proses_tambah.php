<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']);
    $email   = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);

    // Validasi server-side
    if (empty($nama) || empty($email) || empty($telepon)) {
        $_SESSION['error_anggota'] = "Semua field wajib diisi!";
        header("Location: tambah.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_anggota'] = "Format email tidak valid!";
        header("Location: tambah.php");
        exit;
    }

    if (!is_numeric($telepon)) {
        $_SESSION['error_anggota'] = "Nomor telepon harus berupa angka!";
        header("Location: tambah.php");
        exit;
    }

    // Inisialisasi session array anggota jika belum ada
    if (!isset($_SESSION['anggota'])) {
        $_SESSION['anggota'] = [];
    }

    // Simpan data ke session
    $_SESSION['anggota'][] = [
        'nama'    => htmlspecialchars($nama),
        'email'   => htmlspecialchars($email),
        'telepon' => htmlspecialchars($telepon)
    ];

    $_SESSION['sukses_anggota'] = "Data anggota berhasil ditambahkan!";
    header("Location: list.php");
    exit;
} else {
    header("Location: tambah.php");
    exit;
}
?>