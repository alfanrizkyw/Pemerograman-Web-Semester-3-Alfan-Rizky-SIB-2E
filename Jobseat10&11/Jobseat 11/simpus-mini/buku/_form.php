<?php if (!defined('BASE_URL')) { http_response_code(403); exit; } // partial: tidak boleh dibuka langsung lewat URL ?>
<form class="form card" method="post">
  <?= csrf_field() ?>
  <label for="judul">Judul</label>
  <input type="text" id="judul" name="judul" value="<?= e($b['judul']) ?>" maxlength="255" required>
  <label for="pengarang">Pengarang</label>
  <input type="text" id="pengarang" name="pengarang" value="<?= e($b['pengarang']) ?>" maxlength="255" required>
  <label for="tahun">Tahun</label>
  <input type="number" id="tahun" name="tahun" value="<?= e($b['tahun']) ?>" min="1900" max="<?= date('Y') ?>" required>
  <label for="isbn">ISBN</label>
  <input type="text" id="isbn" name="isbn" value="<?= e($b['isbn']) ?>" maxlength="17">
  <label for="stok">Stok</label>
  <input type="number" id="stok" name="stok" value="<?= e($b['stok']) ?>" min="0" max="100000" required>
  <label for="kategori">Kategori</label>
  <select id="kategori" name="kategori">
    <?php foreach (KATEGORI_BUKU as $k): ?><option value="<?= e($k) ?>" <?= $b['kategori'] === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?>
  </select>
  <div class="actions"><button class="btn" type="submit">Simpan</button><a class="btn btn-gray" href="list.php">Batal</a></div>
</form>
