<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
$err = [];
$v = ['nama' => '', 'username' => '', 'role' => 'petugas'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['nama'] = trim($_POST['nama'] ?? '');
    $v['username'] = trim($_POST['username'] ?? '');
    $v['role'] = $_POST['role'] ?? 'petugas';
    $pass = $_POST['password'] ?? '';
    if ($v['nama'] === '') $err[] = 'Nama wajib diisi.';
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $v['username'])) $err[] = 'Username 4-20 karakter (huruf, angka, underscore).';
    if (strlen($pass) < 6) $err[] = 'Password minimal 6 karakter.';
    if ($pass !== ($_POST['konfirmasi'] ?? '')) $err[] = 'Konfirmasi password tidak cocok.';
    if (!in_array($v['role'], ['admin', 'petugas'], true)) $err[] = 'Role tidak valid.';
    if (!$err) {
        $cek = $pdo->prepare("SELECT 1 FROM users WHERE username = :u");
        $cek->execute([':u' => $v['username']]);
        if ($cek->fetch()) $err[] = 'Username sudah dipakai.';
    }
    if (!$err) {
        $st = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (:n, :u, :p, :r)");
        $st->execute([':n' => $v['nama'], ':u' => $v['username'], ':p' => password_hash($pass, PASSWORD_DEFAULT), ':r' => $v['role']]);
        redirect('auth/login.php', 'success', 'Registrasi berhasil, silakan login.');
    }
}
$judul = 'Register';
include __DIR__ . '/../includes/header.php';
?>
<div class="card auth-box">
  <h2>Daftar Akun Petugas</h2>
  <?php foreach ($err as $x): ?><div class="alert alert-error"><?= e($x) ?></div><?php endforeach; ?>
  <form class="form" method="post" autocomplete="off">
    <label for="nama">Nama Lengkap</label>
    <input type="text" id="nama" name="nama" value="<?= e($v['nama']) ?>" required>
    <label for="username">Username</label>
    <input type="text" id="username" name="username" value="<?= e($v['username']) ?>" required>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <label for="konfirmasi">Konfirmasi Password</label>
    <input type="password" id="konfirmasi" name="konfirmasi" required>
    <label for="role">Role</label>
    <select id="role" name="role">
      <option value="petugas" <?= $v['role'] === 'petugas' ? 'selected' : '' ?>>Petugas</option>
      <option value="admin" <?= $v['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>
    <div class="actions"><button class="btn" type="submit">Daftar</button></div>
  </form>
  <p>Sudah punya akun? <a href="login.php">Login</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
