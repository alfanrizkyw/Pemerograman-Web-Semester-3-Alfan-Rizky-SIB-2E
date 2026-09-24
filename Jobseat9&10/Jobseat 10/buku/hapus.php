<?php
require_once '../includes/koneksi.php';
require_once '../includes/auth.php';
cek_login();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: list.php?status=sukses_hapus");
    exit;
} else {
    header("Location: list.php");
    exit;
}