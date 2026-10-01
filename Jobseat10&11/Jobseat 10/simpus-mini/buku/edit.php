<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_validasi.php';
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM buku WHERE id = :id");
$st->execute([':id' => $id]);
$b = $st->fetch(PDO::FETCH_ASSOC);
if (!$b) redirect('buku/list.php', 'error', 'Buku tidak ditemukan.');
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$b, $err] = ambil_buku();
    if (!$err) {
        $up = $pdo->prepare("UPDATE buku SET judul=:j, pengarang=:p, tahun=:t, isbn=:i, stok=:s, kategori=:k WHERE id=:id");
        $up->execute([':j' => $b['judul'], ':p' => $b['pengarang'], ':t' => $b['tahun'], ':i' => $b['isbn'], ':s' => $b['stok'], ':k' => $b['kategori'], ':id' => $id]);
        redirect('buku/list.php', 'success', 'Buku berhasil diperbarui.');
    }
}
$judul = 'Edit Buku';
include __DIR__ . '/../includes/header.php';
?>
<h1>Edit Buku</h1>
<?php foreach ($err as $x): ?><div class="alert alert-error"><?= e($x) ?></div><?php endforeach; ?>
<?php include '_form.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
