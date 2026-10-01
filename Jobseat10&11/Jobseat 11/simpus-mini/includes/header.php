<?php
require_once __DIR__ . '/koneksi.php';      // sekaligus memulai sesi aman
$login = isset($_SESSION['user_id']);
$judul = $judul ?? 'SIMPUS-Mini';
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> | SIMPUS-Mini</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script src="<?= BASE_URL ?>/assets/js/app.js" defer></script>
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
      <!-- Logout memakai POST + token CSRF (bukan link GET) agar tidak bisa dipicu dari situs lain -->
      <form class="nav-form" method="post" action="<?= BASE_URL ?>/auth/logout.php">
        <?= csrf_field() ?>
        <button class="btn btn-out" type="submit">Logout</button>
      </form>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
      <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php if (!empty($_SESSION['flash'])): [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']);
      $t = in_array($t, ['success', 'error'], true) ? $t : 'error'; // whitelist kelas CSS ?>
  <div class="alert alert-<?= e($t) ?>"><?= e($m) ?></div>
<?php endif; ?>
