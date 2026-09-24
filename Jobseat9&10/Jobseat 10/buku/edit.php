<?php
require_once '../includes/koneksi.php';
require_once '../includes/auth.php';
cek_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = :id");
$stmt->execute(['id' => $id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    header("Location: list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Buku - SIMPUS-Mini</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>SIMPUS-Mini</h1>
        <nav>
            <a href="../index.php">Beranda</a>
            <a href="list.php">Daftar Buku</a>
            <a href="../anggota/list.php">Daftar Anggota</a>
            <span style="margin-left: 15px; color: #ffeb3b;">Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../auth/logout.php" style="color: #ff6b6b;">Logout</a>
        </nav>
    </header>
    <main>
        <h2>Edit Data Buku</h2>
        <form action="proses_edit.php" method="POST">
            <input type="hidden" name="id" value="<?= $buku['id']; ?>">
            
            <label for="judul">Judul Buku:</label>
            <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($buku['judul']); ?>" required>

            <label for="pengarang">Pengarang:</label>
            <input type="text" id="pengarang" name="pengarang" value="<?= htmlspecialchars($buku['pengarang']); ?>" required>

            <label for="tahun">Tahun Terbit:</label>
            <input type="number" id="tahun" name="tahun" value="<?= $buku['tahun']; ?>" required>

            <label for="isbn">ISBN:</label>
            <input type="text" id="isbn" name="isbn" value="<?= htmlspecialchars($buku['isbn']); ?>" required>

            <label for="stok">Stok:</label>
            <input type="number" id="stok" name="stok" value="<?= $buku['stok']; ?>" min="0" required>

            <label for="kategori">Kategori:</label>
            <select id="kategori" name="kategori" required>
                <option value="Teknologi" <?= $buku['kategori'] == 'Teknologi' ? 'selected' : ''; ?>>Teknologi</option>
                <option value="Fiksi" <?= $buku['kategori'] == 'Fiksi' ? 'selected' : ''; ?>>Fiksi</option>
                <option value="Sains" <?= $buku['kategori'] == 'Sains' ? 'selected' : ''; ?>>Sains</option>
                <option value="Sejarah" <?= $buku['kategori'] == 'Sejarah' ? 'selected' : ''; ?>>Sejarah</option>
            </select>

            <button type="submit">Simpan Perubahan</button>
        </form>
    </main>
</body>
</html>