<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Daftar Buku</h2>
    <a href="tambah.php" class="btn btn-success mb-3">+ Tambah Buku</a>

    <?php if (isset($_SESSION['sukses'])): ?>
        <div class="alert alert-success"><?= $_SESSION['sukses']; unset($_SESSION['sukses']); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Tahun Terbit</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($_SESSION['buku'])): ?>
                <?php foreach ($_SESSION['buku'] as $index => $buku): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= $buku['judul']; ?></td>
                        <td><?= $buku['penulis']; ?></td>
                        <td><?= $buku['tahun']; ?></td>
                        <td><?= $buku['stok']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Belum ada data buku.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="../index.php" class="btn btn-secondary">Kembali ke Beranda</a>
</body>
</html>