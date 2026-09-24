<?php
require_once '../includes/koneksi.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM anggota WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: list.php?status=sukses_hapus");
    exit;
} else {
    header("Location: list.php");
    exit;
}