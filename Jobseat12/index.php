<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/koneksi.php';

$hari = LAMA_PINJAM_HARI;
$stat = $pdo->query("
    SELECT
      (SELECT COUNT(*) FROM buku)    AS buku,
      (SELECT COUNT(*) FROM anggota) AS anggota,
      (SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam') AS aktif,
      (SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam'
          AND tanggal_pinjam < now() - interval '{$hari} days') AS telat
")->fetch();

$masuk = isset($_SESSION['user_id']);
$perhatian = [];
if ($masuk) {
    $perhatian = $pdo->query("
        SELECT p.id, p.tanggal_pinjam, b.judul, a.nama
        FROM peminjaman p
        JOIN buku b    ON b.id = p.buku_id
        JOIN anggota a ON a.id = p.anggota_id
        WHERE p.status = 'dipinjam'
        ORDER BY p.tanggal_pinjam ASC
        LIMIT 5
    ")->fetchAll();
}

$pageTitle = 'Beranda';
$active = 'beranda';
require __DIR__ . '/includes/header.php';
?>
<header class="page-head">
    <div>
        <h1><?= $masuk ? 'Halo, ' . e(explode(' ', (string) $_SESSION['nama'])[0]) : 'Perpustakaan SIMPUS-Mini' ?></h1>
        <p class="lead">
            <?php if ($masuk): ?>
                <?= (int) $stat['telat'] > 0
                    ? (int) $stat['telat'] . ' pinjaman melewati batas ' . $hari . ' hari dan belum kembali.'
                    : 'Tidak ada pinjaman yang terlambat hari ini.' ?>
            <?php else: ?>
                Cari buku yang tersedia, lalu datang ke meja petugas untuk meminjam.
            <?php endif; ?>
        </p>
    </div>
    <?php if ($masuk): ?>
        <a class="btn btn--primary" href="<?= e(url('peminjaman/tambah.php')) ?>"><?= icon('plus') ?>Pinjamkan buku</a>
    <?php else: ?>
        <a class="btn btn--primary" href="<?= e(url('katalog.php')) ?>"><?= icon('search') ?>Cari di katalog</a>
    <?php endif; ?>
</header>

<dl class="stats">
    <div class="stat">
        <dt>Judul buku</dt>
        <dd><?= number_format((int) $stat['buku'], 0, ',', '.') ?></dd>
    </div>
    <div class="stat">
        <dt>Anggota</dt>
        <dd><?= number_format((int) $stat['anggota'], 0, ',', '.') ?></dd>
    </div>
    <div class="stat">
        <dt>Sedang dipinjam</dt>
        <dd><?= number_format((int) $stat['aktif'], 0, ',', '.') ?></dd>
    </div>
    <div class="stat <?= (int) $stat['telat'] > 0 ? 'stat--warn' : '' ?>">
        <dt>Terlambat</dt>
        <dd><?= number_format((int) $stat['telat'], 0, ',', '.') ?></dd>
    </div>
</dl>

<?php if ($masuk): ?>
<section class="panel" aria-labelledby="h-aktif">
    <div class="panel__head">
        <h2 id="h-aktif">Pinjaman paling lama</h2>
        <a href="<?= e(url('peminjaman/list.php')) ?>">Lihat semua</a>
    </div>
    <?php if (!$perhatian): ?>
        <div class="empty">
            <p>Belum ada buku yang sedang dipinjam.</p>
            <a class="btn" href="<?= e(url('peminjaman/tambah.php')) ?>">Catat peminjaman pertama</a>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Buku</th><th>Peminjam</th><th>Dipinjam</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($perhatian as $r): $t = hari_terlambat($r['tanggal_pinjam']); ?>
                <tr>
                    <td data-label="Buku"><strong><?= e($r['judul']) ?></strong></td>
                    <td data-label="Peminjam"><?= e($r['nama']) ?></td>
                    <td data-label="Dipinjam"><?= e(tgl($r['tanggal_pinjam'])) ?></td>
                    <td data-label="Status">
                        <?php if ($t > 0): ?><span class="tag tag--late">Terlambat <?= $t ?> hari</span>
                        <?php else: ?><span class="tag tag--ok">Sisa <?= max(0, -$t) ?> hari</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<?php else: ?>
<section class="panel">
    <div class="panel__head"><h2>Cara meminjam</h2></div>
    <ol class="steps">
        <li>Cari judul di <a href="<?= e(url('katalog.php')) ?>">katalog</a> dan pastikan stoknya tersedia.</li>
        <li>Datang ke meja petugas membawa kartu anggota.</li>
        <li>Petugas mencatat peminjaman. Batas kembali <?= $hari ?> hari.</li>
    </ol>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
