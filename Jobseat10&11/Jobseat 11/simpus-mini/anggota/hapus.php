<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_admin();                                                          // hanya admin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('anggota/list.php'); // hapus hanya via POST
csrf_wajib('anggota/list.php');                                         // verifikasi token CSRF
$id = id_valid($_POST['id'] ?? 0);
if (!$id) redirect('anggota/list.php', 'error', 'ID anggota tidak valid.');
$st = $pdo->prepare("DELETE FROM anggota WHERE id = :id");
$st->execute([':id' => $id]);
if ($st->rowCount() === 0) redirect('anggota/list.php', 'error', 'Anggota tidak ditemukan.');
redirect('anggota/list.php', 'success', 'Anggota berhasil dihapus.');
