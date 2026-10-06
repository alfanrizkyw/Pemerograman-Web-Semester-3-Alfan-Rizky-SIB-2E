<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/_form.php';

$id = get_int('id');
$st = $pdo->prepare('SELECT * FROM buku WHERE id = :id');
$st->execute([':id' => $id]);
$buku = $st->fetch();
if (!$buku) {
    flash('error', 'Buku tidak ditemukan.');
    redirect('buku/list.php');
}

$v = $buku;
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$v, $err] = validasi_buku();
    if (!$err) {
        try {
            $up = $pdo->prepare('UPDATE buku SET judul=:judul, pengarang=:pengarang, tahun=:tahun, isbn=:isbn,
                                 stok=:stok, kategori=:kategori WHERE id=:id');
            $up->execute([
                ':judul' => $v['judul'], ':pengarang' => $v['pengarang'], ':tahun' => (int) $v['tahun'],
                ':isbn' => $v['isbn'], ':stok' => (int) $v['stok'], ':kategori' => $v['kategori'], ':id' => $id,
            ]);
            flash('ok', 'Perubahan pada “' . $v['judul'] . '” disimpan.');
            redirect('buku/list.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23505') { throw $ex; }
            $err['isbn'] = 'ISBN ini sudah terdaftar pada buku lain.';
        }
    }
}

$pageTitle = 'Ubah buku';
$active = 'buku';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <p class="crumb"><a href="<?= e(url('buku/list.php')) ?>">Buku</a></p>
        <h1>Ubah buku</h1>
    </div>
</header>
<section class="panel panel--form"><?php form_buku($v, $err, 'Simpan perubahan'); ?></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
