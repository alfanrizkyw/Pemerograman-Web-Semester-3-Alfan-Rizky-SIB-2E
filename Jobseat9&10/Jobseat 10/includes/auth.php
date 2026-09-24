<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /simpus-mini/auth/login.php?status=belum_login");
        exit;
    }
}
?>