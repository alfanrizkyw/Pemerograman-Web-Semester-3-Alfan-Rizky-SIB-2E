<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$err = [];
$v = ['nama' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v['nama']     = post_str('nama', 100);
    $v['username'] = strtolower(post_str('username', 50));
    $pass          = (string) ($_POST['password'] ?? '');
    $pass2         = (string) ($_POST['password2'] ?? '');

    if ($v['nama'] === '') {
        $err['nama'] = 'Nama wajib diisi.';
    }
    if (!preg_match('/^[a-z0-9_.]{3,30}$/', $v['username'])) {
        $err['username'] = 'Username 3–30 karakter: huruf kecil, angka, titik, atau garis bawah.';
    }
    if (strlen($pass) < 8) {
        $err['password'] = 'Kata sandi minimal 8 karakter.';
    }
    if ($pass !== $pass2) {
        $err['password2'] = 'Konfirmasi tidak sama dengan kata sandi.';
    }

    if (!$err) {
        try {
            // Role selalu "petugas": admin tidak bisa dibuat lewat form publik.
            $st = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (:n, :u, :p, 'petugas')");
            $st->execute([':n' => $v['nama'], ':u' => $v['username'], ':p' => password_hash($pass, PASSWORD_DEFAULT)]);
            flash('ok', 'Akun dibuat. Silakan masuk.');
            redirect('auth/login.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23505') {
                $err['username'] = 'Username sudah dipakai.';
            } else {
                throw $ex;
            }
        }
    }
}

$pageTitle = 'Daftar petugas';
$active = '';
require __DIR__ . '/../includes/header.php';
?>
<section class="auth">
    <h1>Daftar petugas</h1>
    <p class="lead">Akun baru berperan sebagai petugas. Peran admin diatur oleh admin yang sudah ada.</p>

    <form method="post" class="form" novalidate>
        <?= csrf_field() ?>
        <div class="field <?= isset($err['nama']) ? 'has-error' : '' ?>">
            <label for="nama">Nama lengkap</label>
            <input id="nama" name="nama" type="text" value="<?= e($v['nama']) ?>" autocomplete="name" required>
            <?php if (isset($err['nama'])): ?><span class="error"><?= e($err['nama']) ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($err['username']) ? 'has-error' : '' ?>">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="<?= e($v['username']) ?>" autocomplete="username" autocapitalize="none" required>
            <?php if (isset($err['username'])): ?><span class="error"><?= e($err['username']) ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($err['password']) ? 'has-error' : '' ?>">
            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
            <?php if (isset($err['password'])): ?><span class="error"><?= e($err['password']) ?></span><?php endif; ?>
        </div>
        <div class="field <?= isset($err['password2']) ? 'has-error' : '' ?>">
            <label for="password2">Ulangi kata sandi</label>
            <input id="password2" name="password2" type="password" autocomplete="new-password" required>
            <?php if (isset($err['password2'])): ?><span class="error"><?= e($err['password2']) ?></span><?php endif; ?>
        </div>
        <button class="btn btn--primary btn--block" type="submit">Buat akun</button>
    </form>
    <p class="muted">Sudah punya akun? <a href="<?= e(url('auth/login.php')) ?>">Masuk</a></p>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
