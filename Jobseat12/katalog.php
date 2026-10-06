<?php
declare(strict_types=1);
// Katalog publik (aktor "Tamu"): hanya baca, tanpa tombol ubah/hapus.
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/koneksi.php';

$q   = get_str('q');
$kat = get_str('kategori', 30);
if (!in_array($kat, KATEGORI_BUKU, true)) {
    $kat = '';
}

$where = [];
$bind  = [];
if ($q !== '') {
    $where[] = '(judul ILIKE :q OR pengarang ILIKE :q)';
    $bind[':q'] = '%' . addcslashes($q, '%_\\') . '%';
}
if ($kat !== '') {
    $where[] = 'kategori = :k';
    $bind[':k'] = $kat;
}
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare("SELECT COUNT(*) FROM buku $w");
$st->execute($bind);
$pg = paginate((int) $st->fetchColumn(), 12);

$st = $pdo->prepare("SELECT judul, pengarang, tahun, kategori, stok FROM buku $w ORDER BY judul LIMIT :l OFFSET :o");
foreach ($bind as $k => $v) {
    $st->bindValue($k, $v);
}
$st->bindValue(':l', $pg['per'], PDO::PARAM_INT);
$st->bindValue(':o', $pg['offset'], PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$pageTitle = 'Katalog buku';
$active = 'katalog';
require __DIR__ . '/includes/header.php';
?>
<header class="page-head">
    <div>
        <h1>Katalog buku</h1>
        <p class="lead"><?= $pg['total'] ?> judul<?= ($q !== '' || $kat !== '') ? ' cocok dengan pencarian' : '' ?>.</p>
    </div>
</header>

<form class="filters" method="get" role="search">
    <div class="field field--grow">
        <label for="q">Judul atau pengarang</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Mis. Pramoedya">
    </div>
    <div class="field">
        <label for="kategori">Kategori</label>
        <select id="kategori" name="kategori">
            <option value="">Semua</option>
            <?php foreach (KATEGORI_BUKU as $k): ?>
                <option <?= $kat === $k ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn--primary" type="submit">Cari</button>
</form>

<?php if (!$rows): ?>
    <div class="empty panel">
        <p>Tidak ada buku yang cocok. Coba kata kunci lain atau pilih semua kategori.</p>
        <a class="btn" href="<?= e(url('katalog.php')) ?>">Hapus pencarian</a>
    </div>
<?php else: ?>
<ul class="books">
    <?php foreach ($rows as $b): ?>
        <li class="book">
            <div class="book__body">
                <h2><?= e($b['judul']) ?></h2>
                <p><?= e($b['pengarang']) ?>, <?= (int) $b['tahun'] ?></p>
            </div>
            <div class="book__meta">
                <span class="chip"><?= e($b['kategori']) ?></span>
                <?php if ((int) $b['stok'] > 0): ?>
                    <span class="tag tag--ok">Tersedia <?= (int) $b['stok'] ?></span>
                <?php else: ?>
                    <span class="tag tag--off">Sedang dipinjam semua</span>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
<?= pager($pg, ['q' => $q, 'kategori' => $kat]) ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
