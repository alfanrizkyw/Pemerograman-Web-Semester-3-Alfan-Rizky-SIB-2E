<?php
$judul = 'Beranda';
include __DIR__ . '/includes/header.php';
$tb = $ta = 0;
if ($login) {
    // Prepared statement dipakai konsisten, walau query ini tanpa input user
    $st = $pdo->prepare("SELECT COUNT(*) FROM buku");    $st->execute(); $tb = $st->fetchColumn();
    $st = $pdo->prepare("SELECT COUNT(*) FROM anggota"); $st->execute(); $ta = $st->fetchColumn();
}
?>
<section class="card">
  <h1>Selamat datang<?= $login ? ', ' . e($_SESSION['nama']) : '' ?></h1>
  <p>Sistem Informasi Perpustakaan Sederhana (SIMPUS-Mini).
  <?= $login ? 'Kelola data buku dan anggota melalui menu di atas.' : 'Silakan login sebagai petugas untuk mengelola data.' ?></p>
  <?php if (!$login): ?><a class="btn" href="<?= BASE_URL ?>/auth/login.php">Login Petugas</a><?php endif; ?>
</section>
<?php if ($login): ?>
<section class="grid">
  <div class="card stat"><h3>Total Buku</h3><p><?= (int)$tb ?></p></div>
  <div class="card stat"><h3>Total Anggota</h3><p><?= (int)$ta ?></p></div>
  <div class="card stat"><h3>Role Anda</h3><p class="stat-role"><?= e(ucfirst($_SESSION['role'])) ?></p></div>
</section>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
