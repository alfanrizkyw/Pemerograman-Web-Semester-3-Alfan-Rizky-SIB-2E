<?php
require_once '../includes/koneksi.php';
require_once '../includes/auth.php';
cek_login();

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($keyword !== '') {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE nama ILIKE :keyword OR no_anggota ILIKE :keyword");
    $stmtCount->execute(['keyword' => "%$keyword%"]);
    $totalData = $stmtCount->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE nama ILIKE :keyword OR no_anggota ILIKE :keyword ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':keyword', "%$keyword%", PDO::PARAM_STR);
} else {
    $totalData = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
    $stmt = $pdo->prepare("SELECT * FROM anggota ORDER BY id DESC LIMIT :limit OFFSET :offset");
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$anggota_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($totalData / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Anggota - SIMPUS-Mini</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>SIMPUS-Mini</h1>
        <nav>
            <a href="../index.php">Beranda</a>
            <a href="../buku/list.php">Daftar Buku</a>
            <a href="list.php">Daftar Anggota</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="margin-left: 15px; color: #ffeb3b;">Halo, <?= htmlspecialchars($_SESSION['nama']); ?></span>
                <a href="../auth/logout.php" style="color: #ff6b6b;">Logout</a>
            <?php else: ?>
                <a href="../auth/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>Daftar Anggota</h2>
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px; align-items: center;">
            <a href="tambah.php" class="btn">+ Tambah Anggota</a>
            <form action="list.php" method="GET" style="display: flex; gap: 5px; margin: 0;">
                <input type="text" name="keyword" placeholder="Cari nama/no anggota..." value="<?= htmlspecialchars($keyword); ?>" style="margin: 0; padding: 6px;">
                <button type="submit">Cari</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Anggota</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($anggota_list) > 0): ?>
                    <?php $no = $offset + 1; foreach ($anggota_list as $anggota): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($anggota['no_anggota']); ?></td>
                        <td><?= htmlspecialchars($anggota['nama']); ?></td>
                        <td><?= htmlspecialchars($anggota['alamat']); ?></td>
                        <td><?= htmlspecialchars($anggota['no_hp']); ?></td>
                        <td>
                            <a href="edit.php?id=<?= $anggota['id']; ?>">Edit</a> | 
                            <a href="hapus.php?id=<?= $anggota['id']; ?>" onclick="return confirm('Yakin ingin menghapus anggota ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">Data anggota tidak ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 15px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="list.php?page=<?= $i; ?>&keyword=<?= urlencode($keyword); ?>" style="padding: 5px 10px; border: 1px solid #ccc; margin-right: 5px; text-decoration:none; <?= $page == $i ? 'background:#007bff; color:#fff;' : 'color:#333;'; ?>"><?= $i; ?></a>
            <?php endfor; ?>
        </div>
    </main>
</body>
</html>