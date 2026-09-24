<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_validasi.php';
$b = ['judul' => '', 'pengarang' => '', 'tahun' => '', 'isbn' => '', 'stok' => 0, 'kategori' => 'Fiksi'];
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$b, $err] = ambil_buku();
    if (!$err) {
        $st = $pdo->prepare("INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori) VALUES (:j,:p,:t,:i,:s,:k) RETURNING id");
        $st->execute([':j' => $b['judul'], ':p' => $b['pengarang'], ':t' => $b['tahun'], ':i' => $b['isbn'], ':s' => $b['stok'], ':k' => $b['kategori']]);
        redirect('buku/list.php', 'success', 'Buku berhasil ditambahkan.');
    }
}
$judul = 'Tambah Buku';
include __DIR__ . '/../includes/header.php';
?>
<h1>Tambah Buku</h1>
<?php foreach ($err as $x): ?><div class="alert alert-error"><?= e($x) ?></div><?php endforeach; ?>
<?php include '_form.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
