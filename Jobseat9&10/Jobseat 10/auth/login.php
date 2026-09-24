<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - SIMPUS-Mini</title>
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
        <h2>Login Petugas</h2>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'gagal'): ?>
            <p style="color: red; font-weight: bold;">Username atau password salah!</p>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'belum_login'): ?>
            <p style="color: orange; font-weight: bold;">Silakan login terlebih dahulu.</p>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'register_sukses'): ?>
            <p style="color: green; font-weight: bold;">Registrasi berhasil, silakan login.</p>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" style="width: 100%; margin-top: 10px;">Login</button>
        </form>
        <p style="margin-top: 15px; text-align: center;">Belum punya akun? <a href="register.php">Register di sini</a></p>
    </main>
</body>
</html>