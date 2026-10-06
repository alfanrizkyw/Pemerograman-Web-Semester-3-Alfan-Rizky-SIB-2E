<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = post_str('username', 50);
    $password = (string) ($_POST['password'] ?? '');

    $st = $pdo->prepare('SELECT id, nama, password, role FROM users WHERE username = :u');
    $st->execute([':u' => $username]);
    $u = $st->fetch();

    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $u['id'];
        $_SESSION['nama']    = $u['nama'];
        $_SESSION['role']    = $u['role'];
        $_SESSION['csrf']    = bin2hex(random_bytes(32));

        $tujuan = $_SESSION['kembali_ke'] ?? '';
        unset($_SESSION['kembali_ke']);
        // Hanya terima path internal aplikasi.
        $base = base_url();
        if ($tujuan !== '' && str_starts_with($tujuan, $base . '/') && !str_contains($tujuan, '//', 1) && !str_contains($tujuan, "\n")) {
            header('Location: ' . $tujuan);
            exit;
        }
        redirect('index.php');
    }
    $error = 'Username atau kata sandi salah.';
}

$pageTitle = 'Masuk';
$active = '';
require __DIR__ . '/../includes/header.php';
?>
<section class="auth">
    <h1>Masuk</h1>
    <p class="lead">Khusus petugas perpustakaan. Pengunjung bisa melihat <a href="<?= e(url('katalog.php')) ?>">katalog buku</a> tanpa masuk.</p>

    <?php if ($error): ?><div class="alert alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="form" novalidate>
        <?= csrf_field() ?>
        <div class="field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="<?= e($username) ?>" autocomplete="username" autocapitalize="none" required autofocus>
        </div>
        <div class="field">
            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn--primary btn--block" type="submit">Masuk</button>
    </form>
    <p class="muted">Belum punya akun petugas? <a href="<?= e(url('auth/register.php')) ?>">Daftar</a></p>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
