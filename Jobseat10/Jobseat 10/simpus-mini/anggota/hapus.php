<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_admin(); // hanya admin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('anggota/list.php');
$st = $pdo->prepare("DELETE FROM anggota WHERE id = :id");
$st->execute([':id' => (int)($_POST['id'] ?? 0)]);
redirect('anggota/list.php', 'success', 'Anggota berhasil dihapus.');
