<form class="form card" method="post">
  <label for="nama">Nama</label>
  <input type="text" id="nama" name="nama" value="<?= e($a['nama']) ?>" required>
  <label for="no_anggota">No. Anggota</label>
  <input type="text" id="no_anggota" name="no_anggota" value="<?= e($a['no_anggota']) ?>" required>
  <label for="alamat">Alamat</label>
  <textarea id="alamat" name="alamat" rows="3"><?= e($a['alamat']) ?></textarea>
  <label for="no_hp">No. HP</label>
  <input type="text" id="no_hp" name="no_hp" value="<?= e($a['no_hp']) ?>">
  <div class="actions"><button class="btn" type="submit">Simpan</button><a class="btn btn-gray" href="list.php">Batal</a></div>
</form>
