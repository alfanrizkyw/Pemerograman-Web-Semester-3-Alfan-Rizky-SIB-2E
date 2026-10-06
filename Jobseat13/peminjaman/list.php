<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$hari = LAMA_PINJAM_HARI;
$q = get_str('q');
$hanyaTelat = get_str('telat', 1) === '1';

$where = ["p.status = 'dipinjam'"];
$bind = [];
if ($q !== '') {
    $where[] = '(b.judul ILIKE :q OR a.nama ILIKE :q OR a.no_anggota ILIKE :q)';
    $bind[':q'] = '%' . addcslashes($q, '%_\\') . '%';
}
if ($hanyaTelat) {
    $where[] = "p.tanggal_pinjam < now() - interval '{$hari} days'";
}
$w = 'WHERE ' . implode(' AND ', $where);
$from = 'FROM peminjaman p JOIN buku b ON b.id = p.buku_id JOIN anggota a ON a.id = p.anggota_id';

$st = $pdo->prepare("SELECT COUNT(*) $from $w");
$st->execute($bind);
$pg = paginate((int) $st->fetchColumn(), 10);

$st = $pdo->prepare("SELECT p.id, p.tanggal_pinjam, b.judul, a.nama, a.no_anggota, a.id AS anggota_id
                     $from $w ORDER BY p.tanggal_pinjam ASC LIMIT :l OFFSET :o");
foreach ($bind as $k => $val) { $st->bindValue($k, $val); }
$st->bindValue(':l', $pg['per'], PDO::PARAM_INT);
$st->bindValue(':o', $pg['offset'], PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$pageTitle = 'Sedang dipinjam';
$active = 'dipinjam';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <h1>Sedang dipinjam</h1>
        <p class="lead"><?= $pg['total'] ?> pinjaman aktif<?= $hanyaTelat ? ' yang terlambat' : '' ?>. Yang paling lama di urutan teratas.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(url('peminjaman/tambah.php')) ?>"><?= icon('plus') ?>Pinjamkan buku</a>
</header>

<form class="filters" method="get" role="search">
    <div class="field field--grow">
        <label for="q">Cari pinjaman</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Judul, nama, atau nomor anggota">
    </div>
    <label class="check"><input type="checkbox" name="telat" value="1" <?= $hanyaTelat ? 'checked' : '' ?>> Hanya yang terlambat</label>
    <button class="btn" type="submit">Terapkan</button>
</form>

<?php if (!$rows): ?>
    <div class="panel empty">
        <p><?= ($q !== '' || $hanyaTelat) ? 'Tidak ada pinjaman yang cocok dengan filter.' : 'Semua buku sudah kembali. Tidak ada pinjaman aktif.' ?></p>
        <a class="btn" href="<?= e(url(($q !== '' || $hanyaTelat) ? 'peminjaman/list.php' : 'peminjaman/tambah.php')) ?>"><?= ($q !== '' || $hanyaTelat) ? 'Hapus filter' : 'Pinjamkan buku' ?></a>
    </div>
<?php else: ?>
<div class="panel panel--flush">
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Buku</th><th>Peminjam</th><th>Dipinjam</th><th>Batas kembali</th><th>Status</th><th class="col-aksi">Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $t = hari_terlambat($r['tanggal_pinjam']); ?>
            <tr class="<?= $t > 0 ? 'is-late' : '' ?>">
                <td data-label="Buku"><strong><?= e($r['judul']) ?></strong></td>
                <td data-label="Peminjam"><a href="<?= e(url('peminjaman/riwayat.php?anggota_id=' . (int) $r['anggota_id'])) ?>"><?= e($r['nama']) ?></a><small class="sub"><?= e($r['no_anggota']) ?></small></td>
                <td data-label="Dipinjam"><?= e(tgl($r['tanggal_pinjam'])) ?></td>
                <td data-label="Batas kembali"><?= e(tgl(date('Y-m-d', jatuh_tempo($r['tanggal_pinjam'])))) ?></td>
                <td data-label="Status">
                    <?php if ($t > 0): ?><span class="tag tag--late">Terlambat <?= $t ?> hari</span>
                    <?php elseif ($t === 0): ?><span class="tag tag--warn">Jatuh tempo hari ini</span>
                    <?php else: ?><span class="tag tag--ok">Sisa <?= -$t ?> hari</span><?php endif; ?>
                </td>
                <td class="col-aksi">
                    <form method="post" action="<?= e(url('peminjaman/kembali.php')) ?>" data-confirm="Catat “<?= e($r['judul']) ?>” dari <?= e($r['nama']) ?> sudah dikembalikan?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                        <button class="btn btn--sm btn--primary" type="submit">Kembalikan</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<?= pager($pg, ['q' => $q, 'telat' => $hanyaTelat ? '1' : '']) ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
