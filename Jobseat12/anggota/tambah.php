<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/_form.php';

// Usulan nomor berikutnya, mis. A-0007.
$maks = (int) $pdo->query("SELECT COALESCE(MAX(NULLIF(regexp_replace(no_anggota, '\D', '', 'g'), '')::int), 0) FROM anggota")->fetchColumn();
$v = ['nama' => '', 'no_anggota' => sprintf('A-%04d', $maks + 1), 'alamat' => '', 'no_hp' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$v, $err] = validasi_anggota();
    if (!$err) {
        try {
            $st = $pdo->prepare('INSERT INTO anggota (nama, no_anggota, alamat, no_hp) VALUES (:nama, :no, :alamat, :hp) RETURNING id');
            $st->execute([':nama' => $v['nama'], ':no' => $v['no_anggota'], ':alamat' => $v['alamat'], ':hp' => $v['no_hp']]);
            flash('ok', 'Anggota ' . $v['nama'] . ' ditambahkan.');
            redirect('anggota/list.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23505') { throw $ex; }
            $err['no_anggota'] = 'Nomor anggota sudah dipakai.';
        }
    }
}

$pageTitle = 'Tambah anggota';
$active = 'anggota';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <p class="crumb"><a href="<?= e(url('anggota/list.php')) ?>">Anggota</a></p>
        <h1>Tambah anggota</h1>
    </div>
</header>
<section class="panel panel--form"><?php form_anggota($v, $err, 'Simpan anggota'); ?></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
