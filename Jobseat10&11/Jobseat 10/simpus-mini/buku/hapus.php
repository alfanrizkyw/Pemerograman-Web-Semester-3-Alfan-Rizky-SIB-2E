<?php
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('buku/list.php');
$st = $pdo->prepare("DELETE FROM buku WHERE id = :id");
$st->execute([':id' => (int)($_POST['id'] ?? 0)]);
redirect('buku/list.php', 'success', 'Buku berhasil dihapus.');
