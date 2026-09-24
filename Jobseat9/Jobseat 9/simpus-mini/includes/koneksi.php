<?php
$host = "localhost";
$db   = "simpus_mini";
$user = "postgres";
$pass = "12345678"; // Sesuaikan password PostgreSQL Anda

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>