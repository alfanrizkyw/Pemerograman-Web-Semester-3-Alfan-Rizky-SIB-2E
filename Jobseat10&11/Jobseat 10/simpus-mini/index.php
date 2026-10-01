<?php
$judul = 'Beranda';
include __DIR__ . '/includes/header.php';
$tb = $ta = 0;
if ($login) {
    $tb = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
    $ta = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
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
  <div class="card stat"><h3>Role Anda</h3><p style="font-size:1.4rem"><?= e(ucfirst($_SESSION['role'])) ?></p></div>
</section>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
