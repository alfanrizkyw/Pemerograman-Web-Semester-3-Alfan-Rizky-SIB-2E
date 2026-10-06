<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

/** @var string $pageTitle  @var string $active */
$pageTitle = $pageTitle ?? 'SIMPUS-Mini';
$active    = $active ?? '';
$masuk     = isset($_SESSION['user_id']);

$nav = $masuk
    ? [
        ['home',    'Beranda',        'index.php',               'beranda'],
        ['loan',    'Sedang dipinjam', 'peminjaman/list.php',    'dipinjam'],
        ['plus',    'Pinjamkan buku', 'peminjaman/tambah.php',   'pinjam'],
        ['history', 'Riwayat',        'peminjaman/riwayat.php',  'riwayat'],
        ['book',    'Buku',           'buku/list.php',           'buku'],
        ['users',   'Anggota',        'anggota/list.php',        'anggota'],
    ]
    : [
        ['home', 'Beranda', 'index.php',   'beranda'],
        ['book', 'Katalog', 'katalog.php', 'katalog'],
    ];
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — SIMPUS-Mini</title>
    <meta name="theme-color" content="#171815">
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<a class="skip" href="#isi">Langsung ke isi</a>

<header class="topbar">
    <a class="brand" href="<?= e(url('index.php')) ?>">
        <span class="brand__mark" aria-hidden="true">S</span>
        <span>SIMPUS-Mini</span>
    </a>
    <button class="menu-btn" type="button" id="menuBtn" aria-expanded="false" aria-controls="sidebar">
        <?= icon('menu') ?><span class="sr">Buka menu</span>
    </button>
</header>

<div class="scrim" id="scrim" hidden></div>

<div class="shell">
    <aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
        <a class="brand brand--side" href="<?= e(url('index.php')) ?>">
            <span class="brand__mark" aria-hidden="true">S</span>
            <span>SIMPUS-Mini<small>Perpustakaan</small></span>
        </a>

        <nav class="nav">
            <?php foreach ($nav as [$ic, $label, $href, $key]): ?>
                <a href="<?= e(url($href)) ?>" <?= $active === $key ? 'aria-current="page"' : '' ?>>
                    <?= icon($ic) ?><span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar__user">
            <?php if ($masuk): ?>
                <div class="who">
                    <span class="who__av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) $_SESSION['nama'], 0, 1))) ?></span>
                    <span class="who__t"><strong><?= e($_SESSION['nama']) ?></strong><small><?= e($_SESSION['role']) ?></small></span>
                </div>
                <a class="nav-out" href="<?= e(url('auth/logout.php')) ?>"><?= icon('out') ?><span>Keluar</span></a>
            <?php else: ?>
                <a class="btn btn--primary btn--block" href="<?= e(url('auth/login.php')) ?>">Masuk sebagai petugas</a>
            <?php endif; ?>
        </div>
    </aside>

    <main class="main" id="isi">
        <?php foreach (flash_take() as $f): ?>
            <div class="alert alert--<?= e($f['type']) ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['msg']) ?></div>
        <?php endforeach; ?>
