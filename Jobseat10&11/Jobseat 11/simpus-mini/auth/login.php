<?php
require_once __DIR__ . '/../includes/koneksi.php';   // sekaligus memulai sesi aman
if (isset($_SESSION['user_id'])) redirect('index.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = str_post('username');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if (!csrf_valid()) {
        $err = PESAN_CSRF;
    } elseif (!panjang_maks($username, 20) || strlen($password) > 128) {
        $err = 'Username atau password salah.';       // input tidak masuk akal: tolak tanpa menyentuh DB
    } else {
        // Prepared statement: input user TIDAK PERNAH digabung ke string SQL
        $st = $pdo->prepare("SELECT id, nama, password, role FROM users WHERE username = :u");
        $st->execute([':u' => $username]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        // password_verify selalu dijalankan (juga saat username tidak ada) agar waktu respons sama
        // -> penyerang tidak bisa menebak username yang valid dari lama respons.
        $hash = $user ? $user['password'] : password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $ok   = password_verify($password, $hash);
        if ($user && $ok) {
            session_regenerate_id(true);               // cegah session fixation (Tugas Mandiri)
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // token baru untuk sesi yang baru login
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            redirect('index.php', 'success', 'Login berhasil. Selamat datang, ' . $user['nama'] . '!');
        }
        $err = 'Username atau password salah.';        // pesan sama untuk username salah/password salah
    }
}
$judul = 'Login';
include __DIR__ . '/../includes/header.php';
?>
<div class="card auth-box">
  <h2>Login Petugas</h2>
  <?php if ($err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endif; ?>
  <form class="form" method="post">
    <?= csrf_field() ?>
    <label for="username">Username</label>
    <input type="text" id="username" name="username" maxlength="20" required autofocus>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" maxlength="128" required>
    <div class="actions"><button class="btn" type="submit">Masuk</button></div>
  </form>
  <p>Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
