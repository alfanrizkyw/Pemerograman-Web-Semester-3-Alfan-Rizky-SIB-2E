<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Anggota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Daftar Anggota</h2>
    <a href="tambah.php" class="btn btn-success mb-3">+ Tambah Anggota</a>

    <?php if (isset($_SESSION['sukses_anggota'])): ?>
        <div class="alert alert-success"><?= $_SESSION['sukses_anggota']; unset($_SESSION['sukses_anggota']); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Telepon</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($_SESSION['anggota'])): ?>
                <?php foreach ($_SESSION['anggota'] as $index => $agt): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= $agt['nama']; ?></td>
                        <td><?= $agt['email']; ?></td>
                        <td><?= $agt['telepon']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data anggota.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="../index.php" class="btn btn-secondary">Kembali ke Beranda</a>
</body>
</html>