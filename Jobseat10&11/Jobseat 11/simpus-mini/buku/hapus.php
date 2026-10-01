<?php
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('buku/list.php');   // hapus hanya via POST
csrf_wajib('buku/list.php');                                           // verifikasi token CSRF
$id = id_valid($_POST['id'] ?? 0);
if (!$id) redirect('buku/list.php', 'error', 'ID buku tidak valid.');
$st = $pdo->prepare("DELETE FROM buku WHERE id = :id");
$st->execute([':id' => $id]);
if ($st->rowCount() === 0) redirect('buku/list.php', 'error', 'Buku tidak ditemukan.');
redirect('buku/list.php', 'success', 'Buku berhasil dihapus.');
