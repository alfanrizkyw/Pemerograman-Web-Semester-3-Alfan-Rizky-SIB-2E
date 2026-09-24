<?php
require_once 'includes/koneksi.php';
session_start();

$totalBuku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$totalAnggota = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - SIMPUS-Mini</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .card h3 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .card p {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>SIMPUS-Mini</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="buku/list.php">Daftar Buku</a>
            <a href="anggota/list.php">Daftar Anggota</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="margin-left: 15px; color: #ffeb3b;">Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
                <a href="auth/logout.php" style="color: #ff6b6b;">Logout</a>
            <?php else: ?>
                <a href="auth/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>Selamat Datang di Sistem Informasi Perpustakaan</h2>
        <p>Gunakan menu navigasi di atas untuk mengelola data Buku dan Anggota perpustakaan.</p>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>Total Buku</h3>
                <p><?= $totalBuku; ?></p>
            </div>
            <div class="card">
                <h3>Total Anggota</h3>
                <p><?= $totalAnggota; ?></p>
            </div>
        </div>
    </main>
</body>
</html>