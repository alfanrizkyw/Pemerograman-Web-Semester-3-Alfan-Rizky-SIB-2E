<?php if (!defined('BASE_URL')) { http_response_code(403); exit; } // partial: tidak boleh dibuka langsung lewat URL ?>
<form class="form card" method="post">
  <?= csrf_field() ?>
  <label for="nama">Nama</label>
  <input type="text" id="nama" name="nama" value="<?= e($a['nama']) ?>" maxlength="100" required>
  <label for="no_anggota">No. Anggota</label>
  <input type="text" id="no_anggota" name="no_anggota" value="<?= e($a['no_anggota']) ?>" maxlength="50" required>
  <label for="alamat">Alamat</label>
  <textarea id="alamat" name="alamat" rows="3" maxlength="500"><?= e($a['alamat']) ?></textarea>
  <label for="no_hp">No. HP</label>
  <input type="text" id="no_hp" name="no_hp" value="<?= e($a['no_hp']) ?>" maxlength="20">
  <div class="actions"><button class="btn" type="submit">Simpan</button><a class="btn btn-gray" href="list.php">Batal</a></div>
</form>
