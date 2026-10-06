<?php
// Salin file ini menjadi includes/config.local.php lalu isi sesuai server Anda.
// config.local.php sudah masuk .gitignore, jadi kredensial tidak ikut ke repository.
// Alternatif: set variabel environment DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.
return [
    'host' => 'localhost',
    'port' => '5432',
    'name' => 'simpus_mini',
    'user' => 'postgres',
    'pass' => '',
    'sslmode' => 'prefer',
];
