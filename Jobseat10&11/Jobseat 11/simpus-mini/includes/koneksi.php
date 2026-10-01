<?php
// Sesuaikan BASE_URL dengan nama folder proyek di htdocs
$root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
define('BASE_URL', rtrim(substr($root, strlen($doc)), '/'));

require_once __DIR__ . '/keamanan.php';   // header keamanan + sesi aman + CSRF + helper (Jobsheet 11)

// Kredensial bisa di-override lewat environment (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
// nilai default di bawah hanya untuk lingkungan belajar lokal.
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$db   = getenv('DB_NAME') ?: 'simpus_mini';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASS') ?: '12345678';
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,          // prepared statement NATIVE di PostgreSQL (bukan emulasi PHP)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Jangan tampilkan $e->getMessage(): bisa membocorkan host, port, dan nama user DB.
    error_log('[SIMPUS] Koneksi DB gagal: ' . $e->getMessage());
    http_response_code(503);
    die('Layanan basis data sedang tidak tersedia.');
}
