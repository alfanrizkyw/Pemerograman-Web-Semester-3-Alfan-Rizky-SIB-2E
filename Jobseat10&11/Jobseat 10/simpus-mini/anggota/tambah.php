<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/_validasi.php';
$a = ['nama' => '', 'no_anggota' => '', 'alamat' => '', 'no_hp' => ''];
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$a, $err] = ambil_anggota();
    if (!$err) {
        try {
            $st = $pdo->prepare("INSERT INTO anggota (nama, no_anggota, alamat, no_hp) VALUES (:n,:no,:al,:hp)");
            $st->execute([':n' => $a['nama'], ':no' => $a['no_anggota'], ':al' => $a['alamat'], ':hp' => $a['no_hp']]);
            redirect('anggota/list.php', 'success', 'Anggota berhasil ditambahkan.');
        } catch (PDOException $ex) { $err[] = 'No. anggota sudah terdaftar.'; }
    }
}
$judul = 'Tambah Anggota';
include __DIR__ . '/../includes/header.php';
?>
<h1>Tambah Anggota</h1>
<?php foreach ($err as $x): ?><div class="alert alert-error"><?= e($x) ?></div><?php endforeach; ?>
<?php include '_form.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
