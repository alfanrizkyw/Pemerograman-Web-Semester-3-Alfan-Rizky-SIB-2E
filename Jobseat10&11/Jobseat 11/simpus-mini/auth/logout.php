<?php
require_once __DIR__ . '/../includes/koneksi.php';
// Logout wajib POST + token CSRF: link/gambar dari situs lain (GET) tidak boleh bisa memaksa logout.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    redirect(isset($_SESSION['user_id']) ? 'index.php' : 'auth/login.php');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {            // hapus cookie sesi di browser
    $c = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $c['path'], 'domain' => $c['domain'],
                                   'secure' => $c['secure'], 'httponly' => true, 'samesite' => 'Lax']);
}
session_destroy();
mulai_sesi_aman();                                // sesi baru (ID baru) hanya untuk pesan flash
session_regenerate_id(true);
$_SESSION['flash'] = ['success', 'Anda telah logout.'];
header('Location: login.php');
exit;
