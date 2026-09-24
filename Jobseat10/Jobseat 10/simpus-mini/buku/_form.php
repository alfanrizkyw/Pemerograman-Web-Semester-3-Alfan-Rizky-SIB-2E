<?php $kat = ['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Referensi']; ?>
<form class="form card" method="post">
  <label for="judul">Judul</label>
  <input type="text" id="judul" name="judul" value="<?= e($b['judul']) ?>" required>
  <label for="pengarang">Pengarang</label>
  <input type="text" id="pengarang" name="pengarang" value="<?= e($b['pengarang']) ?>" required>
  <label for="tahun">Tahun</label>
  <input type="number" id="tahun" name="tahun" value="<?= e($b['tahun']) ?>" min="1900" max="<?= date('Y') ?>" required>
  <label for="isbn">ISBN</label>
  <input type="text" id="isbn" name="isbn" value="<?= e($b['isbn']) ?>">
  <label for="stok">Stok</label>
  <input type="number" id="stok" name="stok" value="<?= e($b['stok']) ?>" min="0" required>
  <label for="kategori">Kategori</label>
  <select id="kategori" name="kategori">
    <?php foreach ($kat as $k): ?><option <?= $b['kategori'] === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?>
  </select>
  <div class="actions"><button class="btn" type="submit">Simpan</button><a class="btn btn-gray" href="list.php">Batal</a></div>
</form>
