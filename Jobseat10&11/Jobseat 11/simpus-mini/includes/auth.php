<?php
// Guard clause: include di baris paling atas halaman buku/* dan anggota/*
require_once __DIR__ . '/koneksi.php';      // sekaligus memulai sesi aman
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
header('Cache-Control: no-store');           // halaman terproteksi tidak disimpan cache (tombol Back setelah logout)
function wajib_admin() {
    if (($_SESSION['role'] ?? '') !== 'admin') redirect('index.php', 'error', 'Akses ditolak: hanya admin.');
}
