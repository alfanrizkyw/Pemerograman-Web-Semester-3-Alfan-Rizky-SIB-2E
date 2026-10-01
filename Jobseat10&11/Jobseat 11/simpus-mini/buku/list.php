<?php
require_once __DIR__ . '/../includes/auth.php';
$st = $pdo->prepare("SELECT id, judul, pengarang, tahun, stok, kategori FROM buku ORDER BY id DESC");
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
$judul = 'Daftar Buku';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Daftar Buku</h1><a class="btn" href="tambah.php">+ Tambah Buku</a></div>
<div class="table-wrap"><table>
  <thead><tr><th>#</th><th>Judul</th><th>Pengarang</th><th>Tahun</th><th>Stok</th><th>Kategori</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="empty">Belum ada data buku.</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td><td><?= e($r['judul']) ?></td><td><?= e($r['pengarang']) ?></td>
      <td><?= e($r['tahun']) ?></td><td><?= e($r['stok']) ?></td><td><?= e($r['kategori']) ?></td>
      <td class="aksi">
        <a class="btn btn-sm" href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
        <form method="post" action="hapus.php" data-confirm="Hapus buku ini?">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-sm btn-danger">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
