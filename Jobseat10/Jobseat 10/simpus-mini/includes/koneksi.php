<?php
// Sesuaikan BASE_URL dengan nama folder proyek di htdocs
$root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
define('BASE_URL', rtrim(substr($root, strlen($doc)), '/'));
$host = "localhost"; $port = "5432"; $db = "simpus_mini"; $user = "postgres"; $pass = "12345678";
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($p, $type = null, $msg = null) {
    if ($type) $_SESSION['flash'] = [$type, $msg];
    header("Location: " . BASE_URL . "/" . $p); exit;
}
