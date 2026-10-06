<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/_form.php';

$id = get_int('id');
$st = $pdo->prepare('SELECT * FROM anggota WHERE id = :id');
$st->execute([':id' => $id]);
$a = $st->fetch();
if (!$a) {
    flash('error', 'Anggota tidak ditemukan.');
    redirect('anggota/list.php');
}

$v = $a;
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$v, $err] = validasi_anggota();
    if (!$err) {
        try {
            $up = $pdo->prepare('UPDATE anggota SET nama=:nama, no_anggota=:no, alamat=:alamat, no_hp=:hp WHERE id=:id');
            $up->execute([':nama' => $v['nama'], ':no' => $v['no_anggota'], ':alamat' => $v['alamat'], ':hp' => $v['no_hp'], ':id' => $id]);
            flash('ok', 'Data ' . $v['nama'] . ' diperbarui.');
            redirect('anggota/list.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23505') { throw $ex; }
            $err['no_anggota'] = 'Nomor anggota sudah dipakai anggota lain.';
        }
    }
}

$pageTitle = 'Ubah anggota';
$active = 'anggota';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <p class="crumb"><a href="<?= e(url('anggota/list.php')) ?>">Anggota</a></p>
        <h1>Ubah anggota</h1>
    </div>
</header>
<section class="panel panel--form"><?php form_anggota($v, $err, 'Simpan perubahan'); ?></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
