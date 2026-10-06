<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$hari = LAMA_PINJAM_HARI;
$err = [];
$pilih = ['anggota_id' => 0, 'buku_id' => get_int('buku_id')];
$pilih['anggota_id'] = get_int('anggota_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $anggotaId = (int) filter_input(INPUT_POST, 'anggota_id', FILTER_VALIDATE_INT);
    $bukuId    = (int) filter_input(INPUT_POST, 'buku_id', FILTER_VALIDATE_INT);
    $pilih = ['anggota_id' => $anggotaId, 'buku_id' => $bukuId];

    if ($anggotaId <= 0) { $err['anggota_id'] = 'Pilih anggota peminjam.'; }
    if ($bukuId <= 0)    { $err['buku_id'] = 'Pilih buku yang dipinjam.'; }

    if (!$err) {
        try {
            $pdo->beginTransaction();

            // Kunci baris buku agar dua petugas tidak mengambil stok terakhir bersamaan.
            $st = $pdo->prepare('SELECT judul, stok FROM buku WHERE id = :id FOR UPDATE');
            $st->execute([':id' => $bukuId]);
            $buku = $st->fetch();

            $st = $pdo->prepare('SELECT nama FROM anggota WHERE id = :id');
            $st->execute([':id' => $anggotaId]);
            $anggota = $st->fetch();

            if (!$buku)   { $err['buku_id'] = 'Buku tidak ditemukan.'; }
            elseif ((int) $buku['stok'] <= 0) { $err['buku_id'] = 'Stok buku ini habis.'; }
            if (!$anggota) { $err['anggota_id'] = 'Anggota tidak ditemukan.'; }

            if (!$err) {
                $st = $pdo->prepare("SELECT COUNT(*) FROM peminjaman
                                     WHERE anggota_id = :a AND status = 'dipinjam'
                                       AND tanggal_pinjam < now() - interval '{$hari} days'");
                $st->execute([':a' => $anggotaId]);
                if ((int) $st->fetchColumn() > 0) {
                    $err['anggota_id'] = $anggota['nama'] . ' masih punya pinjaman lewat ' . $hari . ' hari. Minta buku dikembalikan dulu.';
                }
            }

            if ($err) {
                $pdo->rollBack();
            } else {
                $pdo->prepare('INSERT INTO peminjaman (buku_id, anggota_id, petugas_id) VALUES (:b, :a, :u)')
                    ->execute([':b' => $bukuId, ':a' => $anggotaId, ':u' => $_SESSION['user_id']]);
                $pdo->prepare('UPDATE buku SET stok = stok - 1 WHERE id = :id')->execute([':id' => $bukuId]);
                $pdo->commit();

                flash('ok', '“' . $buku['judul'] . '” dipinjamkan ke ' . $anggota['nama'] . '. Kembali paling lambat ' . tgl(date('Y-m-d', strtotime("+{$hari} days"))) . '.');
                redirect('peminjaman/list.php');
            }
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            if ($ex->getCode() === '23505') {
                $err['buku_id'] = 'Anggota ini sedang meminjam judul yang sama.';
            } else {
                throw $ex;
            }
        }
    }
}

$anggotaList = $pdo->query("
    SELECT a.id, a.nama, a.no_anggota,
           COUNT(p.id) FILTER (WHERE p.status = 'dipinjam') AS aktif,
           COUNT(p.id) FILTER (WHERE p.status = 'dipinjam' AND p.tanggal_pinjam < now() - interval '{$hari} days') AS telat
    FROM anggota a LEFT JOIN peminjaman p ON p.anggota_id = a.id
    GROUP BY a.id ORDER BY a.nama
")->fetchAll();

$bukuList = $pdo->query('SELECT id, judul, pengarang, stok FROM buku WHERE stok > 0 ORDER BY judul')->fetchAll();
$habis = (int) $pdo->query('SELECT COUNT(*) FROM buku WHERE stok = 0')->fetchColumn();

$pageTitle = 'Pinjamkan buku';
$active = 'pinjam';
require __DIR__ . '/../includes/header.php';
?>
<header class="page-head">
    <div>
        <h1>Pinjamkan buku</h1>
        <p class="lead">Stok berkurang satu otomatis. Batas kembali <?= $hari ?> hari.</p>
    </div>
</header>

<?php if (!$anggotaList || !$bukuList): ?>
    <div class="panel empty">
        <p><?= !$anggotaList ? 'Belum ada anggota yang bisa meminjam.' : 'Semua buku sedang dipinjam. Tidak ada stok tersedia.' ?></p>
        <a class="btn" href="<?= e(url(!$anggotaList ? 'anggota/tambah.php' : 'buku/list.php')) ?>"><?= !$anggotaList ? 'Tambah anggota' : 'Lihat buku' ?></a>
    </div>
<?php else: ?>
<div class="split">
    <section class="panel panel--form">
        <form method="post" class="form" id="formPinjam" novalidate>
            <?= csrf_field() ?>
            <div class="field <?= isset($err['anggota_id']) ? 'has-error' : '' ?>">
                <label for="anggota_id">Anggota</label>
                <select id="anggota_id" name="anggota_id" required>
                    <option value="">Pilih anggota</option>
                    <?php foreach ($anggotaList as $a): $telat = (int) $a['telat'] > 0; ?>
                        <option value="<?= (int) $a['id'] ?>"
                                data-nama="<?= e($a['nama']) ?>" data-no="<?= e($a['no_anggota']) ?>" data-telat="<?= $telat ? 1 : 0 ?>"
                                <?= $pilih['anggota_id'] === (int) $a['id'] ? 'selected' : '' ?>>
                            <?= e($a['nama']) ?> (<?= e($a['no_anggota']) ?>)<?= $telat ? ' — ada pinjaman terlambat' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($err['anggota_id'])): ?><span class="error"><?= e($err['anggota_id']) ?></span><?php endif; ?>
                <span class="hint" id="hintTelat" hidden>Anggota ini punya pinjaman lewat <?= $hari ?> hari dan belum bisa meminjam.</span>
            </div>
            <div class="field <?= isset($err['buku_id']) ? 'has-error' : '' ?>">
                <label for="buku_id">Buku <span class="opt">hanya yang stoknya tersedia</span></label>
                <select id="buku_id" name="buku_id" required>
                    <option value="">Pilih buku</option>
                    <?php foreach ($bukuList as $b): ?>
                        <option value="<?= (int) $b['id'] ?>" data-judul="<?= e($b['judul']) ?>" data-pengarang="<?= e($b['pengarang']) ?>"
                                <?= $pilih['buku_id'] === (int) $b['id'] ? 'selected' : '' ?>>
                            <?= e($b['judul']) ?> — stok <?= (int) $b['stok'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($err['buku_id'])): ?><span class="error"><?= e($err['buku_id']) ?></span><?php endif; ?>
                <?php if ($habis > 0): ?><span class="hint"><?= $habis ?> judul lain tidak ditampilkan karena stoknya habis.</span><?php endif; ?>
            </div>
            <div class="actions">
                <button class="btn btn--primary" type="submit" id="btnPinjam">Catat peminjaman</button>
                <a class="btn" href="<?= e(url('peminjaman/list.php')) ?>">Batal</a>
            </div>
        </form>
    </section>

    <aside class="slip" id="slip" aria-live="polite" data-hari="<?= $hari ?>">
        <p class="slip__title">Slip peminjaman</p>
        <dl>
            <div><dt>Peminjam</dt><dd id="slipAnggota" class="is-empty">Belum dipilih</dd></div>
            <div><dt>Buku</dt><dd id="slipBuku" class="is-empty">Belum dipilih</dd></div>
            <div><dt>Tanggal pinjam</dt><dd id="slipPinjam"><?= e(tgl(date('Y-m-d'))) ?></dd></div>
            <div><dt>Kembali paling lambat</dt><dd id="slipTempo"><?= e(tgl(date('Y-m-d', strtotime("+{$hari} days")))) ?></dd></div>
            <div><dt>Petugas</dt><dd><?= e($_SESSION['nama']) ?></dd></div>
        </dl>
    </aside>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
