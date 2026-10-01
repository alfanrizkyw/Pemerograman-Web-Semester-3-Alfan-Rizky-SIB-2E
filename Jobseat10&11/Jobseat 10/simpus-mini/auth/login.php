<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
if (isset($_SESSION['user_id'])) redirect('index.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare("SELECT * FROM users WHERE username = :u");
    $st->execute([':u' => trim($_POST['username'] ?? '')]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        session_regenerate_id(true); // cegah session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        redirect('index.php', 'success', 'Login berhasil. Selamat datang, ' . $user['nama'] . '!');
    }
    $err = 'Username atau password salah.';
}
$judul = 'Login';
include __DIR__ . '/../includes/header.php';
?>
<div class="card auth-box">
  <h2>Login Petugas</h2>
  <?php if ($err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endif; ?>
  <form class="form" method="post">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required autofocus>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <div class="actions"><button class="btn" type="submit">Masuk</button></div>
  </form>
  <p>Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
