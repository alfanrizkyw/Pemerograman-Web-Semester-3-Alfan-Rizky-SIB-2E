<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi.php';
$login = isset($_SESSION['user_id']);
$judul = $judul ?? 'SIMPUS-Mini';
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> | SIMPUS-Mini</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= BASE_URL ?>/index.php">SIMPUS-Mini</a>
  <nav>
    <a href="<?= BASE_URL ?>/index.php">Beranda</a>
    <?php if ($login): ?>
      <a href="<?= BASE_URL ?>/buku/list.php">Buku</a>
      <a href="<?= BASE_URL ?>/anggota/list.php">Anggota</a>
      <span class="user">👤 <?= e($_SESSION['nama']) ?> <small class="badge"><?= e($_SESSION['role']) ?></small></span>
      <a class="btn btn-out" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
      <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php if (!empty($_SESSION['flash'])): [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert alert-<?= e($t) ?>"><?= e($m) ?></div>
<?php endif; ?>
