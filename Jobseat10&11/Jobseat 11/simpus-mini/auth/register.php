<?php
require_once __DIR__ . '/../includes/koneksi.php';   // sekaligus memulai sesi aman

// --- Kebijakan role (whitelist + anti privilege escalation) ---------------------------------
// Pengunjung anonim TIDAK boleh memilih role sendiri (sebelumnya bisa mendaftar sebagai admin).
//  - Belum ada user sama sekali  -> akun pertama otomatis 'admin' (bootstrap)
//  - Sudah login sebagai admin   -> boleh memilih 'admin' / 'petugas'
//  - Selain itu                  -> dipaksa 'petugas'
const ROLE_VALID = ['admin', 'petugas'];
$st = $pdo->prepare("SELECT COUNT(*) FROM users"); $st->execute();
$userPertama = ((int)$st->fetchColumn() === 0);
$adminLogin  = (($_SESSION['role'] ?? '') === 'admin');
$bolehPilihRole = $adminLogin;

$err = [];
$v = ['nama' => '', 'username' => '', 'role' => 'petugas'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['nama']     = str_post('nama');
    $v['username'] = str_post('username');
    $pass          = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $konf          = is_string($_POST['konfirmasi'] ?? null) ? $_POST['konfirmasi'] : '';
    $roleInput     = str_post('role');
    $v['role']     = $userPertama ? 'admin' : ($bolehPilihRole && in_array($roleInput, ROLE_VALID, true) ? $roleInput : 'petugas');

    if (!csrf_valid()) $err[] = PESAN_CSRF;
    if ($v['nama'] === '' || !panjang_maks($v['nama'], 100)) $err[] = 'Nama wajib diisi (maksimal 100 karakter).';
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/D', $v['username'])) $err[] = 'Username 4-20 karakter (huruf, angka, underscore).';
    if (strlen($pass) < 6 || strlen($pass) > 72) $err[] = 'Password 6-72 karakter.';   // bcrypt memotong >72 byte
    if ($pass !== $konf) $err[] = 'Konfirmasi password tidak cocok.';
    if ($bolehPilihRole && $roleInput !== '' && !in_array($roleInput, ROLE_VALID, true)) $err[] = 'Role tidak valid.';
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
    <?= csrf_field() ?>
    <label for="nama">Nama Lengkap</label>
    <input type="text" id="nama" name="nama" value="<?= e($v['nama']) ?>" maxlength="100" required>
    <label for="username">Username</label>
    <input type="text" id="username" name="username" value="<?= e($v['username']) ?>" maxlength="20" required>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" minlength="6" maxlength="72" required>
    <label for="konfirmasi">Konfirmasi Password</label>
    <input type="password" id="konfirmasi" name="konfirmasi" maxlength="72" required>
    <?php if ($bolehPilihRole): ?>
    <label for="role">Role</label>
    <select id="role" name="role">
      <option value="petugas" <?= $v['role'] === 'petugas' ? 'selected' : '' ?>>Petugas</option>
      <option value="admin" <?= $v['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>
    <?php else: ?>
    <p class="hint">Akun baru dibuat dengan role <strong><?= $userPertama ? 'Admin (akun pertama)' : 'Petugas' ?></strong>.</p>
    <?php endif; ?>
    <div class="actions"><button class="btn" type="submit">Daftar</button></div>
  </form>
  <p>Sudah punya akun? <a href="login.php">Login</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
