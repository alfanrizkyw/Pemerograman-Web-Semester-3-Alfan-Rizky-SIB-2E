<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Pemrograman Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Jobsheet 7 — PHP & Form Processing</h1>
            <p class="col-md-8 fs-4">Selamat datang di aplikasi manajemen data perpustakaan sederhana menggunakan Session PHP.</p>
            <hr class="my-4">
            <p>Silakan pilih menu manajemen data di bawah ini:</p>
            <div class="d-flex gap-3">
                <a href="buku/list.php" class="btn btn-primary btn-lg">Kelola Buku</a>
                <a href="anggota/list.php" class="btn btn-success btn-lg">Kelola Anggota</a>
            </div>
        </div>
    </div>
</body>
</html>