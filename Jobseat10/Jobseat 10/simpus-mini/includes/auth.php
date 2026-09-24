<?php
// Guard clause: include di baris paling atas halaman buku/* dan anggota/*
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
function wajib_admin() {
    if (($_SESSION['role'] ?? '') !== 'admin') redirect('index.php', 'error', 'Akses ditolak: hanya admin.');
}
