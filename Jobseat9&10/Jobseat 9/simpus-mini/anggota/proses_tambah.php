<?php
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_anggota = trim($_POST['no_anggota']);
    $nama       = trim($_POST['nama']);
    $alamat     = trim($_POST['alamat']);
    $no_hp      = trim($_POST['no_hp']);

    if (!empty($no_anggota) && !empty($nama) && !empty($alamat) && !empty($no_hp)) {
        $sql = "INSERT INTO anggota (no_anggota, nama, alamat, no_hp) VALUES (:no_anggota, :nama, :alamat, :no_hp)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'no_anggota' => $no_anggota,
            'nama'       => $nama,
            'alamat'     => $alamat,
            'no_hp'      => $no_hp
        ]);

        header("Location: list.php?status=sukses_tambah");
        exit;
    } else {
        header("Location: tambah.php?status=gagal");
        exit;
    }
}