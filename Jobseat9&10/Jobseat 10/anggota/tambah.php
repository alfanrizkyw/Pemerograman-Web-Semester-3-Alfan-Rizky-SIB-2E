<?php
require_once '../includes/auth.php';
cek_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota - SIMPUS-Mini</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>SIMPUS-Mini</h1>
        <nav>
            <a href="../index.php">Beranda</a>
            <a href="../buku/list.php">Daftar Buku</a>
            <a href="list.php">Daftar Anggota</a>
            <span style="margin-left: 15px; color: #ffeb3b;">Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../auth/logout.php" style="color: #ff6b6b;">Logout</a>
        </nav>
    </header>
    <main>
        <h2>Form Tambah Anggota</h2>
        <form action="proses_tambah.php" method="POST">
            <label for="no_anggota">Nomor Anggota:</label>
            <input type="text" id="no_anggota" name="no_anggota" required>

            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" required>

            <label for="alamat">Alamat:</label>
            <input type="text" id="alamat" name="alamat" required>

            <label for="no_hp">Nomor HP:</label>
            <input type="text" id="no_hp" name="no_hp" required>

            <button type="submit">Simpan Anggota</button>
        </form>
    </main>
</body>
</html>