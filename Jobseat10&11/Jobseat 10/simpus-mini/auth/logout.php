<?php
session_start();
$_SESSION = [];
session_destroy();
session_start();
$_SESSION['flash'] = ['success', 'Anda telah logout.'];
header("Location: login.php");
exit;
