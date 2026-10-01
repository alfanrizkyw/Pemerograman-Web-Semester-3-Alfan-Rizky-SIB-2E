<?php
require_once __DIR__ . '/../includes/auth.php';
$st = $pdo->prepare("SELECT id, nama, no_anggota, alamat, no_hp FROM anggota ORDER BY id DESC");
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
$admin = ($_SESSION['role'] === 'admin');
$judul = 'Daftar Anggota';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Daftar Anggota</h1><a class="btn" href="tambah.php">+ Tambah Anggota</a></div>
<div class="table-wrap"><table>
  <thead><tr><th>#</th><th>Nama</th><th>No. Anggota</th><th>Alamat</th><th>No. HP</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="6" class="empty">Belum ada data anggota.</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['no_anggota']) ?></td>
      <td><?= e($r['alamat']) ?></td><td><?= e($r['no_hp']) ?></td>
      <td class="aksi">
        <a class="btn btn-sm" href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
        <?php if ($admin): // Tugas mandiri: hanya admin melihat tombol Hapus ?>
        <form method="post" action="hapus.php" data-confirm="Hapus anggota ini?">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-sm btn-danger">Hapus</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
