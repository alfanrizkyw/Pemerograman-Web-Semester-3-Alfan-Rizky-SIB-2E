<?php
declare(strict_types=1);
// Partial dipakai tambah.php dan edit.php.

/** Validasi sisi server (lapisan kedua setelah validasi JS). @return array{0:array,1:array} */
function validasi_buku(): array
{
    $d = [
        'judul'     => post_str('judul', 200),
        'pengarang' => post_str('pengarang', 120),
        'tahun'     => post_str('tahun', 4),
        'isbn'      => preg_replace('/[\s-]/', '', post_str('isbn', 20)) ?? '',
        'stok'      => post_str('stok', 5),
        'kategori'  => post_str('kategori', 30),
    ];
    $e = [];
    if ($d['judul'] === '')     { $e['judul'] = 'Judul wajib diisi.'; }
    if ($d['pengarang'] === '') { $e['pengarang'] = 'Pengarang wajib diisi.'; }

    $maks = (int) date('Y') + 1;
    if (filter_var($d['tahun'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000, 'max_range' => $maks]]) === false) {
        $e['tahun'] = "Tahun terbit harus angka 1000–$maks.";
    }
    if ($d['isbn'] !== '' && !preg_match('/^(\d{10}|\d{13}|\d{9}[\dXx])$/', $d['isbn'])) {
        $e['isbn'] = 'ISBN berisi 10 atau 13 digit (tanda hubung boleh dikosongkan).';
    }
    if (filter_var($d['stok'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]) === false) {
        $e['stok'] = 'Stok harus angka 0 atau lebih.';
    }
    if (!in_array($d['kategori'], KATEGORI_BUKU, true)) {
        $e['kategori'] = 'Pilih salah satu kategori.';
    }
    return [$d, $e];
}

/** @param array $v nilai form  @param array $err pesan error per field */
function form_buku(array $v, array $err, string $label): void
{
    $f = static fn(string $k): string => isset($err[$k]) ? 'has-error' : '';
    $m = static function (string $k) use ($err): string {
        return isset($err[$k]) ? '<span class="error">' . e($err[$k]) . '</span>' : '';
    };
    ?>
    <form method="post" class="form form--wide" id="formBuku" novalidate>
        <?= csrf_field() ?>
        <div class="field <?= $f('judul') ?>">
            <label for="judul">Judul</label>
            <input id="judul" name="judul" type="text" maxlength="200" value="<?= e($v['judul'] ?? '') ?>" required>
            <?= $m('judul') ?>
        </div>
        <div class="field <?= $f('pengarang') ?>">
            <label for="pengarang">Pengarang</label>
            <input id="pengarang" name="pengarang" type="text" maxlength="120" value="<?= e($v['pengarang'] ?? '') ?>" required>
            <?= $m('pengarang') ?>
        </div>
        <div class="grid-2">
            <div class="field <?= $f('tahun') ?>">
                <label for="tahun">Tahun terbit</label>
                <input id="tahun" name="tahun" type="number" inputmode="numeric" min="1000" max="<?= (int) date('Y') + 1 ?>" value="<?= e((string) ($v['tahun'] ?? '')) ?>" required>
                <?= $m('tahun') ?>
            </div>
            <div class="field <?= $f('stok') ?>">
                <label for="stok">Stok</label>
                <input id="stok" name="stok" type="number" inputmode="numeric" min="0" max="9999" value="<?= e((string) ($v['stok'] ?? '1')) ?>" required>
                <?= $m('stok') ?>
            </div>
        </div>
        <div class="grid-2">
            <div class="field <?= $f('isbn') ?>">
                <label for="isbn">ISBN <span class="opt">opsional</span></label>
                <input id="isbn" name="isbn" type="text" inputmode="numeric" maxlength="20" value="<?= e($v['isbn'] ?? '') ?>">
                <?= $m('isbn') ?>
            </div>
            <div class="field <?= $f('kategori') ?>">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" required>
                    <option value="">Pilih kategori</option>
                    <?php foreach (KATEGORI_BUKU as $k): ?>
                        <option <?= ($v['kategori'] ?? '') === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $m('kategori') ?>
            </div>
        </div>
        <div class="actions">
            <button class="btn btn--primary" type="submit"><?= e($label) ?></button>
            <a class="btn" href="<?= e(url('buku/list.php')) ?>">Batal</a>
        </div>
    </form>
    <?php
}
