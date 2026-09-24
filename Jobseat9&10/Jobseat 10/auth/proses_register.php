<?php
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role     = 'petugas';

    if (!empty($nama) && !empty($username) && !empty($_POST['password'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (:nama, :username, :password, :role)");
            $stmt->execute([
                'nama'     => $nama,
                'username' => $username,
                'password' => $password,
                'role'     => $role
            ]);

            header("Location: login.php?status=register_sukses");
            exit;
        } catch (PDOException $e) {
            header("Location: register.php?status=username_ganda");
            exit;
        }
    } else {
        header("Location: register.php?status=gagal");
        exit;
    }
}