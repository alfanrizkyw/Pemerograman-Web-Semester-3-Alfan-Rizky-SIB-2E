<?php
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)$_POST['id'];
    $no_anggota = trim($_POST['no_anggota']);
    $nama       = trim($_POST['nama']);
    $alamat     = trim($_POST['alamat']);
    $no_hp      = trim($_POST['no_hp']);

    if (!empty($no_anggota) && !empty($nama) && !empty($alamat) && !empty($no_hp)) {
        $sql = "UPDATE anggota SET no_anggota = :no_anggota, nama = :nama, alamat = :alamat, no_hp = :no_hp WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id'         => $id,
            'no_anggota' => $no_anggota,
            'nama'       => $nama,
            'alamat'     => $alamat,
            'no_hp'      => $no_hp
        ]);

        header("Location: list.php?status=sukses_edit");
        exit;
    } else {
        header("Location: edit.php?id=$id&status=gagal");
        exit;
    }
}