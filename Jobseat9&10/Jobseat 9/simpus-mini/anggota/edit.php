<?php
require_once '../includes/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM anggota WHERE id = :id");
$stmt->execute(['id' => $id]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggota) {
    header("Location: list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Anggota - SIMPUS-Mini</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>SIMPUS-Mini</h1>
        <nav>
            <a href="../index.php">Beranda</a>
            <a href="../buku/list.php">Daftar Buku</a>
            <a href="list.php">Daftar Anggota</a>
        </nav>
    </header>
    <main>
        <h2>Edit Data Anggota</h2>
        <form action="proses_edit.php" method="POST">
            <input type="hidden" name="id" value="<?= $anggota['id']; ?>">
            
            <label for="no_anggota">Nomor Anggota:</label>
            <input type="text" id="no_anggota" name="no_anggota" value="<?= htmlspecialchars($anggota['no_anggota']); ?>" required>

            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($anggota['nama']); ?>" required>

            <label for="alamat">Alamat:</label>
            <input type="text" id="alamat" name="alamat" value="<?= htmlspecialchars($anggota['alamat']); ?>" required>

            <label for="no_hp">Nomor HP:</label>
            <input type="text" id="no_hp" name="no_hp" value="<?= htmlspecialchars($anggota['no_hp']); ?>" required>

            <button type="submit">Simpan Perubahan</button>
        </form>
    </main>
</body>
</html>