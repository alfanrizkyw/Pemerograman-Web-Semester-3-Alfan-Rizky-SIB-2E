<?php
declare(strict_types=1);

function validasi_anggota(): array
{
    $d = [
        'nama'       => post_str('nama', 100),
        'no_anggota' => strtoupper(post_str('no_anggota', 20)),
        'alamat'     => post_str('alamat', 500),
        'no_hp'      => preg_replace('/[\s-]/', '', post_str('no_hp', 20)) ?? '',
    ];
    $e = [];
    if ($d['nama'] === '') { $e['nama'] = 'Nama wajib diisi.'; }
    if (!preg_match('/^[A-Z0-9][A-Z0-9-]{2,19}$/', $d['no_anggota'])) {
        $e['no_anggota'] = 'Nomor anggota 3–20 karakter: huruf, angka, atau tanda hubung.';
    }
    if ($d['no_hp'] !== '' && !preg_match('/^\+?\d{8,15}$/', $d['no_hp'])) {
        $e['no_hp'] = 'Nomor HP berisi 8–15 digit.';
    }
    return [$d, $e];
}

function form_anggota(array $v, array $err, string $label): void
{
    $f = static fn(string $k): string => isset($err[$k]) ? 'has-error' : '';
    $m = static fn(string $k): string => isset($err[$k]) ? '<span class="error">' . e($err[$k]) . '</span>' : '';
    ?>
    <form method="post" class="form form--wide" id="formAnggota" novalidate>
        <?= csrf_field() ?>
        <div class="field <?= $f('nama') ?>">
            <label for="nama">Nama lengkap</label>
            <input id="nama" name="nama" type="text" maxlength="100" value="<?= e($v['nama'] ?? '') ?>" autocomplete="off" required>
            <?= $m('nama') ?>
        </div>
        <div class="grid-2">
            <div class="field <?= $f('no_anggota') ?>">
                <label for="no_anggota">Nomor anggota</label>
                <input id="no_anggota" name="no_anggota" type="text" maxlength="20" value="<?= e($v['no_anggota'] ?? '') ?>" autocapitalize="characters" required>
                <?= $m('no_anggota') ?>
            </div>
            <div class="field <?= $f('no_hp') ?>">
                <label for="no_hp">Nomor HP <span class="opt">opsional</span></label>
                <input id="no_hp" name="no_hp" type="tel" inputmode="tel" maxlength="20" value="<?= e($v['no_hp'] ?? '') ?>">
                <?= $m('no_hp') ?>
            </div>
        </div>
        <div class="field">
            <label for="alamat">Alamat <span class="opt">opsional</span></label>
            <textarea id="alamat" name="alamat" rows="3" maxlength="500"><?= e($v['alamat'] ?? '') ?></textarea>
        </div>
        <div class="actions">
            <button class="btn btn--primary" type="submit"><?= e($label) ?></button>
            <a class="btn" href="<?= e(url('anggota/list.php')) ?>">Batal</a>
        </div>
    </form>
    <?php
}
