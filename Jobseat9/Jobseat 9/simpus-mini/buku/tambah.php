<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku - SIMPUS-Mini</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>SIMPUS-Mini</h1>
        <nav>
            <a href="../index.php">Beranda</a>
            <a href="list.php">Daftar Buku</a>
            <a href="../anggota/list.php">Daftar Anggota</a>
        </nav>
    </header>
    <main>
        <h2>Form Tambah Buku</h2>
        <form action="proses_tambah.php" method="POST">
            <label for="judul">Judul Buku:</label>
            <input type="text" id="judul" name="judul" required>

            <label for="pengarang">Pengarang:</label>
            <input type="text" id="pengarang" name="pengarang" required>

            <label for="tahun">Tahun Terbit:</label>
            <input type="number" id="tahun" name="tahun" required>

            <label for="isbn">ISBN:</label>
            <input type="text" id="isbn" name="isbn" required>

            <label for="stok">Stok:</label>
            <input type="number" id="stok" name="stok" min="0" required>

            <label for="kategori">Kategori:</label>
            <select id="kategori" name="kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Teknologi">Teknologi</option>
                <option value="Fiksi">Fiksi</option>
                <option value="Sains">Sains</option>
                <option value="Sejarah">Sejarah</option>
            </select>

            <button type="submit">Simpan Buku</button>
        </form>
    </main>
</body>
</html>