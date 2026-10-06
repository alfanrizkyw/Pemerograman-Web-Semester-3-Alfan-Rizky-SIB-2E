<?php
declare(strict_types=1);
// Guard: include di baris paling atas setiap halaman yang khusus petugas.
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['kembali_ke'] = $_SERVER['REQUEST_URI'] ?? '';
    redirect('auth/login.php');
}

function user_role(): string
{
    return (string) ($_SESSION['role'] ?? '');
}

function require_admin(): void
{
    if (user_role() !== 'admin') {
        flash('error', 'Aksi ini hanya untuk admin.');
        redirect('index.php');
    }
}
