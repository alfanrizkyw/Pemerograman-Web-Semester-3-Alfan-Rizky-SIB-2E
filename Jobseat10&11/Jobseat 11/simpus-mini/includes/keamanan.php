<?php
/**
 * includes/keamanan.php — Fungsi keamanan terpusat (Jobsheet 11)
 * Di-include otomatis oleh koneksi.php, sehingga aktif di SEMUA halaman.
 *
 * Isi:  1. Mode error        4. Sesi aman
 *       2. Header keamanan   5. CSRF token
 *       3. Helper input      6. Escape output (e) & redirect
 */

// ---------------------------------------------------------------- 1. MODE ERROR
// Produksi: error TIDAK ditampilkan ke pengguna (hanya dicatat di log server).
// Saat belajar/debug boleh dinyalakan: set environment APP_DEBUG=1
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $t) {
    error_log('[SIMPUS] ' . get_class($t) . ': ' . $t->getMessage() . ' @ ' . $t->getFile() . ':' . $t->getLine());
    if (!headers_sent()) { http_response_code(500); header('Content-Type: text/html; charset=UTF-8'); }
    echo APP_DEBUG
        ? '<pre>' . htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8') . '</pre>'
        : '<!DOCTYPE html><meta charset="UTF-8"><title>Kesalahan</title><h1>Terjadi kesalahan</h1><p>Permintaan tidak dapat diproses. Silakan coba lagi.</p>';
});

// ---------------------------------------------------------------- 2. HEADER KEAMANAN
function kirim_header_keamanan(): void {
    header_remove('X-Powered-By');
    // CSP: hanya sumber dari domain sendiri; script/style inline diblokir (mitigasi XSS lapis kedua)
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; "
         . "form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");
    header('X-Content-Type-Options: nosniff');   // cegah MIME sniffing
    header('X-Frame-Options: DENY');             // cegah clickjacking (browser lama)
    header('Referrer-Policy: same-origin');
}

// ---------------------------------------------------------------- 3. HELPER INPUT
/** Ambil field POST bertipe string. Array/UTF-8 rusak -> '' ; karakter kontrol (termasuk NULL byte) dibuang. */
function str_post(string $key): string {
    $v = $_POST[$key] ?? '';
    if (!is_string($v) || preg_match('//u', $v) !== 1) return '';   // bukan string / UTF-8 rusak -> kosong (tanpa butuh ekstensi mbstring)
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $v);  // tab/LF/CR tetap boleh (untuk textarea)
    return trim($v);
}
/** ID valid = integer 1..2147483647, selain itu 0. */
function id_valid($v): int {
    $r = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
    return $r === false ? 0 : (int)$r;
}
/** Panjang dalam KARAKTER (bukan byte). Memakai mbstring jika ada, jika tidak jatuh ke preg /u. */
function panjang_maks(string $s, int $max): bool {
    $n = function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : (int)preg_match_all('/./us', $s);
    return $n <= $max;
}

// ---------------------------------------------------------------- 4. SESI AMAN
function mulai_sesi_aman(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    ini_set('session.use_strict_mode', '1');   // tolak ID sesi buatan penyerang (anti session fixation)
    ini_set('session.use_only_cookies', '1');  // ID sesi tidak boleh lewat URL
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/',
        'secure'   => $https,   // otomatis aktif jika memakai HTTPS
        'httponly' => true,     // cookie tidak terbaca JavaScript
        'samesite' => 'Lax',    // cookie tidak ikut pada POST lintas-situs
    ]);
    session_start();
}

// ---------------------------------------------------------------- 5. CSRF
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function csrf_valid(): bool {
    $t = $_POST['csrf_token'] ?? '';
    return is_string($t) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $t);
}
/** Untuk endpoint tanpa form (hapus/logout): token salah -> kembali ke halaman asal dengan pesan error. */
function csrf_wajib(string $kembali): void {
    if (!csrf_valid()) redirect($kembali, 'error', 'Token keamanan (CSRF) tidak valid atau kedaluwarsa. Silakan ulangi.');
}
const PESAN_CSRF = 'Token keamanan (CSRF) tidak valid atau kedaluwarsa. Muat ulang halaman lalu coba lagi.';

// ---------------------------------------------------------------- 6. OUTPUT & REDIRECT
/** Escape output ke HTML (anti XSS). Pakai untuk SEMUA data dari user/database yang dicetak ke HTML. */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $p, ?string $type = null, ?string $msg = null): void {
    if ($type) $_SESSION['flash'] = [$type, $msg];
    header('Location: ' . BASE_URL . '/' . $p); exit;
}

kirim_header_keamanan();
mulai_sesi_aman();
