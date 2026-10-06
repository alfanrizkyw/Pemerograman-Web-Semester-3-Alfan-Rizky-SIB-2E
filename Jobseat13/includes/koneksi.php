<?php
declare(strict_types=1);

// Kredensial: environment variable > includes/config.local.php > default lokal.
$cfg = ['host' => 'localhost', 'port' => '5432', 'name' => 'simpus_mini', 'user' => 'postgres', 'pass' => '', 'sslmode' => 'prefer'];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $cfg = array_merge($cfg, require $local);
}
foreach (['host' => 'DB_HOST', 'port' => 'DB_PORT', 'name' => 'DB_NAME', 'user' => 'DB_USER', 'pass' => 'DB_PASS', 'sslmode' => 'DB_SSLMODE'] as $k => $env) {
    $v = getenv($env);
    if ($v !== false && $v !== '') {
        $cfg[$k] = $v;
    }
}

try {
    $pdo = new PDO(
        "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};sslmode={$cfg['sslmode']}",
        $cfg['user'],
        $cfg['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Koneksi DB gagal: ' . $e->getMessage());
    http_response_code(500);
    exit('Tidak dapat terhubung ke database. Periksa konfigurasi di includes/config.local.php.');
}
