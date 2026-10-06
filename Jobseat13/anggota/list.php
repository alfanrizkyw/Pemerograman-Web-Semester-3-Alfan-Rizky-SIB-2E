<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$q = get_str('q');
$bind = [];
$w = '';
if ($q !== '') {
    $w = 'WHERE a.nama ILIKE :q OR a.no_anggota ILIKE :q';
    $bind[':q'] = '%' . addcslashes($q, '%_\\') . '%';
}

$st = $pdo->prepare("SELECT COUNT(*) FROM anggota a $w");
$st->execute($bind);
$pg = paginate((int) $st->fetchColumn(), 10);

$hari = LAMA_PINJAM_HARI;
$st = $pdo->prepare("
    SELECT a.*,
           COUNT(p.id) FILTER (WHERE p.status = 'dipinjam') AS aktif,
           COUNT(p.id) FILTER (WHERE p.status = 'dipinjam' AND p.tanggal_pinjam < now() - interval '{$hari} days') AS telat
    FROM anggota a
    LEFT JOIN peminjaman p ON p.anggota_id = a.id
    $w
    GROUP BY a.id
    ORDER BY a.id DESC
    LIMIT :l OFFSET :o");
foreach ($bind as $k => $val) { $st->bindValue($k, $val); }
$st->bindValue(':l', $pg['per'], PDO::PARAM_INT);
$st->bindValue(':o', $pg['offset'], PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$petugas = in_array(user_role(), ['admin', 'petugas'], true);
$pageTitle = 'Anggota';
$active = 'anggota';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <h1>Anggota</h1>
        <p class="lead"><?= $pg['total'] ?> anggota<?= $q !== '' ? ' cocok dengan “' . e($q) . '”' : ' terdaftar' ?>.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(url('anggota/tambah.php')) ?>"><?= icon('plus') ?>Tambah anggota</a>
</header>

<form class="filters" method="get" role="search">
    <div class="field field--grow">
        <label for="q">Cari anggota</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Nama atau nomor anggota" data-live-filter="#tabelAnggota">
    </div>
    <button class="btn" type="submit">Cari</button>
</form>

<?php if (!$rows): ?>
    <div class="panel empty">
        <p><?= $q !== '' ? 'Tidak ada anggota yang cocok dengan pencarian.' : 'Belum ada anggota. Daftarkan anggota pertama.' ?></p>
        <a class="btn" href="<?= e(url($q !== '' ? 'anggota/list.php' : 'anggota/tambah.php')) ?>"><?= $q !== '' ? 'Hapus pencarian' : 'Tambah anggota' ?></a>
    </div>
<?php else: ?>
<div class="panel panel--flush">
<div class="table-wrap">
    <table class="table" id="tabelAnggota">
        <thead>
            <tr><th>Nama</th><th>No. anggota</th><th>Kontak</th><th>Pinjaman</th><th class="col-aksi">Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $a): ?>
            <tr>
                <td data-label="Nama"><strong><?= e($a['nama']) ?></strong><?php if ($a['alamat'] !== ''): ?><small class="sub"><?= e($a['alamat']) ?></small><?php endif; ?></td>
                <td data-label="No. anggota"><span class="mono"><?= e($a['no_anggota']) ?></span></td>
                <td data-label="Kontak"><?= $a['no_hp'] !== '' ? e($a['no_hp']) : '—' ?></td>
                <td data-label="Pinjaman">
                    <?php if ((int) $a['telat'] > 0): ?><span class="tag tag--late"><?= (int) $a['telat'] ?> terlambat</span>
                    <?php elseif ((int) $a['aktif'] > 0): ?><span class="tag tag--ok"><?= (int) $a['aktif'] ?> aktif</span>
                    <?php else: ?><span class="muted">Tidak ada</span><?php endif; ?>
                </td>
                <td class="col-aksi">
                    <div class="row-actions">
                        <a class="btn btn--sm" href="<?= e(url('peminjaman/riwayat.php?anggota_id=' . (int) $a['id'])) ?>">Riwayat</a>
                        <a class="btn btn--sm" href="<?= e(url('anggota/edit.php?id=' . (int) $a['id'])) ?>">Ubah</a>
                        <?php if ($petugas): ?>
                        <form method="post" action="<?= e(url('anggota/hapus.php')) ?>" data-confirm="Hapus anggota <?= e($a['nama']) ?>? Tindakan ini tidak bisa dibatalkan.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button class="btn btn--sm btn--danger" type="submit">Hapus</button>
                        </form>
                        <?php endif; ?>
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
