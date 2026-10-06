<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$anggotaId = get_int('anggota_id');
$status = get_str('status', 15);
if (!in_array($status, ['dipinjam', 'dikembalikan'], true)) {
    $status = '';
}

$where = [];
$bind = [];
if ($anggotaId > 0) { $where[] = 'p.anggota_id = :a'; $bind[':a'] = $anggotaId; }
if ($status !== '') { $where[] = 'p.status = :s';     $bind[':s'] = $status; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// JOIN 3 tabel (peminjaman, buku, anggota) + users untuk nama petugas.
$from = 'FROM peminjaman p
         JOIN buku b    ON b.id = p.buku_id
         JOIN anggota a ON a.id = p.anggota_id
         LEFT JOIN users u ON u.id = p.petugas_id';

$st = $pdo->prepare("SELECT COUNT(*) $from $w");
$st->execute($bind);
$pg = paginate((int) $st->fetchColumn(), 10);

$st = $pdo->prepare("SELECT p.id, p.tanggal_pinjam, p.tanggal_kembali, p.status,
                            b.judul, a.nama, a.no_anggota, u.nama AS petugas
                     $from $w ORDER BY p.tanggal_pinjam DESC, p.id DESC LIMIT :l OFFSET :o");
foreach ($bind as $k => $val) { $st->bindValue($k, $val); }
$st->bindValue(':l', $pg['per'], PDO::PARAM_INT);
$st->bindValue(':o', $pg['offset'], PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$anggotaList = $pdo->query('SELECT id, nama, no_anggota FROM anggota ORDER BY nama')->fetchAll();
$terpilih = null;
foreach ($anggotaList as $a) {
    if ((int) $a['id'] === $anggotaId) { $terpilih = $a; }
}

$pageTitle = 'Riwayat peminjaman';
$active = 'riwayat';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <h1>Riwayat peminjaman</h1>
        <p class="lead">
            <?= $terpilih ? e($terpilih['nama']) . ' (' . e($terpilih['no_anggota']) . '): ' : '' ?><?= $pg['total'] ?> catatan.
        </p>
    </div>
</header>

<form class="filters" method="get">
    <div class="field field--grow">
        <label for="anggota_id">Anggota</label>
        <select id="anggota_id" name="anggota_id">
            <option value="0">Semua anggota</option>
            <?php foreach ($anggotaList as $a): ?>
                <option value="<?= (int) $a['id'] ?>" <?= $anggotaId === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['nama']) ?> (<?= e($a['no_anggota']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">Semua</option>
            <option value="dipinjam" <?= $status === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
            <option value="dikembalikan" <?= $status === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
        </select>
    </div>
    <button class="btn" type="submit">Terapkan</button>
</form>

<?php if (!$rows): ?>
    <div class="panel empty">
        <p>Belum ada catatan peminjaman untuk filter ini.</p>
        <a class="btn" href="<?= e(url('peminjaman/riwayat.php')) ?>">Tampilkan semua</a>
    </div>
<?php else: ?>
<div class="panel panel--flush">
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Buku</th><th>Peminjam</th><th>Dipinjam</th><th>Dikembalikan</th><th>Status</th><th>Petugas</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r):
            $aktif = $r['status'] === 'dipinjam';
            $t = $aktif ? hari_terlambat($r['tanggal_pinjam']) : 0; ?>
            <tr>
                <td data-label="Buku"><strong><?= e($r['judul']) ?></strong></td>
                <td data-label="Peminjam"><?= e($r['nama']) ?><small class="sub"><?= e($r['no_anggota']) ?></small></td>
                <td data-label="Dipinjam"><?= e(tgl($r['tanggal_pinjam'])) ?></td>
                <td data-label="Dikembalikan"><?= e(tgl($r['tanggal_kembali'])) ?></td>
                <td data-label="Status">
                    <?php if (!$aktif): ?><span class="tag tag--off">Dikembalikan</span>
                    <?php elseif ($t > 0): ?><span class="tag tag--late">Terlambat <?= $t ?> hari</span>
                    <?php else: ?><span class="tag tag--ok">Dipinjam</span><?php endif; ?>
                </td>
                <td data-label="Petugas"><?= $r['petugas'] !== null ? e($r['petugas']) : '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<?= pager($pg, ['anggota_id' => $anggotaId ?: '', 'status' => $status]) ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
