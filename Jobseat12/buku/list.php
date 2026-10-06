<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$q = get_str('q');
$bind = [];
$w = '';
if ($q !== '') {
    $w = 'WHERE judul ILIKE :q OR pengarang ILIKE :q OR isbn ILIKE :q';
    $bind[':q'] = '%' . addcslashes($q, '%_\\') . '%';
}

$st = $pdo->prepare("SELECT COUNT(*) FROM buku $w");
$st->execute($bind);
$pg = paginate((int) $st->fetchColumn(), 10);

$st = $pdo->prepare("SELECT * FROM buku $w ORDER BY id DESC LIMIT :l OFFSET :o");
foreach ($bind as $k => $val) { $st->bindValue($k, $val); }
$st->bindValue(':l', $pg['per'], PDO::PARAM_INT);
$st->bindValue(':o', $pg['offset'], PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$pageTitle = 'Buku';
$active = 'buku';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <h1>Buku</h1>
        <p class="lead"><?= $pg['total'] ?> judul<?= $q !== '' ? ' cocok dengan “' . e($q) . '”' : ' terdaftar' ?>.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(url('buku/tambah.php')) ?>"><?= icon('plus') ?>Tambah buku</a>
</header>

<form class="filters" method="get" role="search">
    <div class="field field--grow">
        <label for="q">Cari buku</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Judul, pengarang, atau ISBN" data-live-filter="#tabelBuku">
    </div>
    <button class="btn" type="submit">Cari</button>
</form>

<?php if (!$rows): ?>
    <div class="panel empty">
        <p><?= $q !== '' ? 'Tidak ada buku yang cocok dengan pencarian.' : 'Belum ada buku. Tambahkan buku pertama untuk mulai.' ?></p>
        <a class="btn" href="<?= e(url($q !== '' ? 'buku/list.php' : 'buku/tambah.php')) ?>"><?= $q !== '' ? 'Hapus pencarian' : 'Tambah buku' ?></a>
    </div>
<?php else: ?>
<div class="panel panel--flush">
<div class="table-wrap">
    <table class="table" id="tabelBuku">
        <thead>
            <tr><th>Judul</th><th>Pengarang</th><th class="num">Tahun</th><th>Kategori</th><th class="num">Stok</th><th class="col-aksi">Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $b): ?>
            <tr>
                <td data-label="Judul"><strong><?= e($b['judul']) ?></strong><?php if ($b['isbn'] !== ''): ?><small class="sub">ISBN <?= e($b['isbn']) ?></small><?php endif; ?></td>
                <td data-label="Pengarang"><?= e($b['pengarang']) ?></td>
                <td data-label="Tahun" class="num"><?= (int) $b['tahun'] ?></td>
                <td data-label="Kategori"><span class="chip"><?= e($b['kategori']) ?></span></td>
                <td data-label="Stok" class="num"><?= (int) $b['stok'] === 0 ? '<span class="tag tag--off">Habis</span>' : (int) $b['stok'] ?></td>
                <td class="col-aksi">
                    <div class="row-actions">
                        <a class="btn btn--sm" href="<?= e(url('buku/edit.php?id=' . (int) $b['id'])) ?>">Ubah</a>
                        <form method="post" action="<?= e(url('buku/hapus.php')) ?>" data-confirm="Hapus buku “<?= e($b['judul']) ?>”? Tindakan ini tidak bisa dibatalkan.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                            <button class="btn btn--sm btn--danger" type="submit">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<?= pager($pg, ['q' => $q]) ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
