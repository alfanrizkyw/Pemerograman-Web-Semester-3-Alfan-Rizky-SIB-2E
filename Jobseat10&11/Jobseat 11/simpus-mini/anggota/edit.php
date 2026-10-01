<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_validasi.php';
$id = id_valid($_GET['id'] ?? 0);                 // id harus integer positif; selain itu ditolak
if (!$id) redirect('anggota/list.php', 'error', 'ID anggota tidak valid.');
$st = $pdo->prepare("SELECT id, nama, no_anggota, alamat, no_hp FROM anggota WHERE id = :id");
$st->execute([':id' => $id]);
$a = $st->fetch(PDO::FETCH_ASSOC);
if (!$a) redirect('anggota/list.php', 'error', 'Anggota tidak ditemukan.');
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$a, $err] = ambil_anggota();
    if (!csrf_valid()) $err[] = PESAN_CSRF;
    if (!$err) {
        try {
            $up = $pdo->prepare("UPDATE anggota SET nama=:n, no_anggota=:no, alamat=:al, no_hp=:hp WHERE id=:id");
            $up->execute([':n' => $a['nama'], ':no' => $a['no_anggota'], ':al' => $a['alamat'], ':hp' => $a['no_hp'], ':id' => $id]);
            redirect('anggota/list.php', 'success', 'Anggota berhasil diperbarui.');
        } catch (PDOException $ex) {
            if (!adalah_duplikat($ex)) throw $ex;
            $err[] = 'No. anggota sudah dipakai anggota lain.';
        }
    }
}
$judul = 'Edit Anggota';
include __DIR__ . '/../includes/header.php';
?>
<h1>Edit Anggota</h1>
<?php foreach ($err as $x): ?><div class="alert alert-error"><?= e($x) ?></div><?php endforeach; ?>
<?php include __DIR__ . '/_form.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
