<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - SIMPUS-Mini</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>SIMPUS-Mini</h1>
        <nav>
            <a href="../index.php">Beranda</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>
    <main style="max-width: 400px;">
        <h2>Registrasi Petugas Baru</h2>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'username_ganda'): ?>
            <p style="color: red; font-weight: bold;">Username sudah digunakan!</p>
        <?php endif; ?>

        <form action="proses_register.php" method="POST">
            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" style="width: 100%; margin-top: 10px;">Register</button>
        </form>
        <p style="margin-top: 15px; text-align: center;">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </main>
</body>
</html>