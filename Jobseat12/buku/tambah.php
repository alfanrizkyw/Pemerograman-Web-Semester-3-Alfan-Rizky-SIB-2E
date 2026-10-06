<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/_form.php';

$v = ['judul' => '', 'pengarang' => '', 'tahun' => '', 'isbn' => '', 'stok' => '1', 'kategori' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$v, $err] = validasi_buku();
    if (!$err) {
        try {
            $st = $pdo->prepare('INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori)
                                 VALUES (:judul, :pengarang, :tahun, :isbn, :stok, :kategori) RETURNING id');
            $st->execute([
                ':judul' => $v['judul'], ':pengarang' => $v['pengarang'], ':tahun' => (int) $v['tahun'],
                ':isbn' => $v['isbn'], ':stok' => (int) $v['stok'], ':kategori' => $v['kategori'],
            ]);
            flash('ok', 'Buku “' . $v['judul'] . '” ditambahkan.');
            redirect('buku/list.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23505') { throw $ex; }
            $err['isbn'] = 'ISBN ini sudah terdaftar pada buku lain.';
        }
    }
}

$pageTitle = 'Tambah buku';
$active = 'buku';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <p class="crumb"><a href="<?= e(url('buku/list.php')) ?>">Buku</a></p>
        <h1>Tambah buku</h1>
    </div>
</header>
<section class="panel panel--form"><?php form_buku($v, $err, 'Simpan buku'); ?></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
