<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Form Tambah Anggota</h2>
    
    <?php if (isset($_SESSION['error_anggota'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_anggota']; unset($_SESSION['error_anggota']); ?></div>
    <?php endif; ?>

    <form action="proses_tambah.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nomor Telepon</label>
            <input type="text" name="telepon" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Anggota</button>
        <a href="list.php" class="btn btn-secondary">Kembali ke List</a>
    </form>
</body>
</html>