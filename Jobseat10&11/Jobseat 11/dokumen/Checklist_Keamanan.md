# Checklist Keamanan SIMPUS-Mini — Jobsheet 11

**Sub-CPMK:** Menerapkan prinsip keamanan web dasar
**Cakupan:** seluruh kode Jobsheet 7–10 (`buku/`, `anggota/`, `auth/`, `includes/`, `index.php`) — hanya hardening, tanpa fitur baru.
**Basis audit:** kode Jobsheet 10 (versi terbaru, sudah memuat auth + CRUD buku/anggota).
**Lingkungan uji:** PHP 8.3, PostgreSQL 16, PDO_PGSQL. Semua bukti di bawah berasal dari **uji yang dijalankan sungguhan** pada versi *before* (Jobsheet 10) dan *after* (Jobsheet 11), bukan asumsi.

---

## 1. Ringkasan Hasil

| | Before (Jobsheet 10) | After (Jobsheet 11) |
|---|---|---|
| Uji otomatis (`dokumen/uji_keamanan.sh`) | 27 aman · **17 rentan** | **44 aman · 0 rentan** |
| Prepared statement | 11 query + **4 `$pdo->query()`** statis | **16 query, 100% `prepare()`/`execute()`**, 0 `query()`/`exec()` |
| Output ke HTML tanpa `e()` | 0 | 0 |
| Form/aksi tulis dengan token CSRF | 0 | semua (login, register, logout, tambah/edit/hapus buku & anggota) |

> **Catatan jujur:** SQL injection dan XSS dasar **sudah aman sejak Jobsheet 10** (PDO prepared statement + `e()` sudah dipakai). Uji `' OR '1'='1` dan `<script>alert(1)</script>` hasilnya *aman* di kedua versi. Kerentanan nyata yang ditemukan ada di CSRF, validasi input, otorisasi (register), konfigurasi sesi, header, dan penanganan error — lihat bagian 3.

---

## 2. Uji Wajib Jobsheet

### 2.1 SQL Injection pada form login — `' OR '1'='1`
| Payload (kolom username) | Before | After |
|---|---|---|
| `' OR '1'='1` | Ditolak ✅ | Ditolak ✅ |
| `admin'--` | Ditolak ✅ | Ditolak ✅ |
| `' OR 1=1 --` | Ditolak ✅ | Ditolak ✅ |
| `x'; DROP TABLE users;--` | Ditolak, tabel `users` utuh ✅ | Ditolak, tabel `users` utuh ✅ |
| `' UNION SELECT 1,'a','b','c','admin'--` | Ditolak ✅ | Ditolak ✅ |
| `edit.php?id=1 OR 1=1;DROP TABLE buku;--` | Tabel utuh ✅ | Ditolak validasi (`ID buku tidak valid`) ✅ |

**Penjelasan:** input masuk sebagai *parameter terikat* (`:u`), tidak pernah digabung ke string SQL, sehingga `'` diperlakukan sebagai data biasa. Pada versi after ditambah `PDO::ATTR_EMULATE_PREPARES => false` sehingga PostgreSQL memakai prepared statement **native** (pemisahan query–data dilakukan server DB, bukan emulasi PHP).

### 2.2 XSS — judul buku `<script>alert(1)</script>`
Judul disimpan ke database apa adanya (terbukti: `SELECT judul` → `<script>alert(1)</script>`), lalu ditampilkan di `buku/list.php`:

```html
<!-- Sumber HTML di browser, Before & After -->
<td>&lt;script&gt;alert(1)&lt;/script&gt;</td>
```
Browser menampilkan teks `<script>alert(1)</script>`, **tidak** mengeksekusi skrip. Uji tambahan yang juga aman di kedua versi: pengarang `"><img src=x onerror=alert(2)>`, nama & alamat anggota berisi tag HTML, dan *attribute breakout* pada `value="..."` di form edit (`" autofocus onfocus="alert(9)`).
**Penambahan di After:** Content-Security-Policy `script-src 'self'` sebagai lapis kedua — kalaupun ada `e()` yang terlewat, skrip inline tetap diblokir browser.

---

## 3. Temuan Kerentanan & Perbaikan

Metode: **[U]** = dibuktikan lewat uji otomatis (before → after) · **[K]** = ditemukan lewat tinjauan kode.

| ID | Halaman / file | Kerentanan | Risiko | Bukti BEFORE | Perbaikan | Bukti AFTER | Metode |
|---|---|---|---|---|---|---|---|
| F-01 | `buku/hapus`, `buku/edit`, `buku/tambah`, `anggota/hapus`, `anggota/edit`, `anggota/tambah` | **CSRF** — aksi tulis hanya mengandalkan cookie sesi | Tinggi | POST `hapus.php` tanpa token → `302`, baris **terhapus** (`count=0`), flash "Buku berhasil dihapus". Edit tanpa token → judul **berubah**. Token palsu → tetap terhapus. | `csrf_token()` = `bin2hex(random_bytes(32))` disimpan di session; `csrf_field()` di semua form; `hash_equals()` saat submit (`csrf_valid()` / `csrf_wajib()`). | Tanpa token / token palsu → baris **masih ada** (`count=1`), flash "Token keamanan (CSRF) tidak valid…". Alur sah (dengan token) tetap berfungsi. | U |
| F-02 | `auth/logout.php`, `includes/header.php` | **Logout via GET** (`<img src=".../logout.php">` dapat memaksa logout) | Rendah | GET `logout.php` → user ter-logout | Logout hanya `POST` + token CSRF; link diganti tombol `<form>`. Cookie sesi dihapus & ID sesi baru dibuat. | GET `logout.php` tidak mengeluarkan user; tombol Logout (POST+token) berfungsi. | U |
| F-03 | `auth/login.php`, `auth/register.php` | Login/Register tanpa token CSRF (*login CSRF*) | Rendah | Form tanpa token | `csrf_field()` + `csrf_valid()`; token diganti baru setelah login. | Request tanpa token ditolak. | K |
| F-04 | `auth/register.php` | **Privilege escalation** — siapa pun bisa mendaftar dengan `role=admin` (whitelist ada, tetapi mengizinkan `admin`) | **Kritis** | Pengunjung anonim kirim `role=admin` → di DB tersimpan **`admin`** | Role dari pengguna hanya dihormati jika yang login adalah admin. Anonim → dipaksa `petugas`; akun pertama (tabel kosong) → `admin`. Whitelist `ROLE_VALID` dengan `in_array(..., true)`. Ditambah `CHECK (role IN (...))` di DB (`sql/02`). | Anonim kirim `role=admin` → tersimpan **`petugas`**. Admin login tetap bisa membuat admin. | U |
| F-05 | `buku/_validasi`, `anggota/_validasi`, `auth/*` | **Type confusion**: `judul[]=x` → `trim(array)` melempar `TypeError` | Sedang | HTTP **500** (di XAMPP dengan `display_errors=On` memuat path & stack trace) | Helper `str_post()` — hanya menerima `string`; array/UTF-8 rusak → `''` → ditolak validasi. | HTTP 200 + pesan "Judul dan pengarang wajib diisi." | U |
| F-06 | `buku/_validasi`, `anggota/_validasi` | Panjang & rentang tidak divalidasi → error DB tak tertangani | Sedang | `stok=99999999999` → HTTP **500**; judul 300 karakter (kolom `VARCHAR(255)`) → HTTP **500** | `filter_var(FILTER_VALIDATE_INT)` dengan `min_range/max_range` (tahun 1900–tahun ini, stok 0–100000); `panjang_maks()` (judul/pengarang ≤255, nama ≤100, alamat ≤500); regex ISBN, no. anggota, no. HP. | Ditolak dengan pesan validasi, tanpa error DB. | U |
| F-07 | semua input teks | **NULL byte** `%00` memotong/merusak data | Rendah | Judul `UJI-nul%00x` tersimpan sebagai **`UJI-nul`** (data terpotong diam-diam) | `str_post()` membuang karakter kontrol (`\x00–\x08, \x0B, \x0C, \x0E–\x1F, \x7F`); tab/LF/CR dipertahankan untuk textarea. | Tersimpan `UJI-nulx` (NULL dibuang). Karakter Unicode sah (`日本語`, `Ünïcode`) tetap lolos. | U |
| F-08 | `includes/koneksi.php`, `includes/header.php` | **Header keamanan tidak ada** (anti-XSS lapis kedua, clickjacking, MIME sniffing) | Sedang | Tidak ada `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options` | `kirim_header_keamanan()`: CSP (`default-src 'self'; script-src 'self'; style-src 'self'; frame-ancestors 'none'; form-action 'self'…`), `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`. `onsubmit=` inline dipindah ke `assets/js/app.js` (`data-confirm`), `style=` inline ke CSS. | Ketiga header terkirim; 0 handler/style inline tersisa di HTML hasil render. | U |
| F-09 | semua halaman (sesi) | **Cookie sesi lemah & tanpa strict mode** | Sedang | `Set-Cookie: PHPSESSID=…; path=/` (tanpa `HttpOnly`, tanpa `SameSite`). Server **menerima** ID sesi yang ditanam penyerang (tanpa menerbitkan ID baru). | `mulai_sesi_aman()`: `use_strict_mode`, `use_only_cookies`, `HttpOnly`, `SameSite=Lax`, `Secure` otomatis jika HTTPS. | `Set-Cookie: …; path=/; HttpOnly; SameSite=Lax`. ID sesi tanam ditolak, server menerbitkan ID baru. | U |
| F-10 | `buku/_form.php`, `buku/_validasi.php`, dll. | Partial dapat dibuka langsung via URL | Rendah | `GET buku/_form.php` → HTTP **500**, HTML terpotong (bocor path di server `display_errors=On`) | Guard `if (!defined('BASE_URL')) { http_response_code(403); exit; }` + `.htaccess` (`Require all denied` untuk `_*.php` dan folder `includes/`). | HTTP **403**, tanpa isi. | U |
| F-11 | `includes/koneksi.php` | `die("Koneksi gagal: " . $e->getMessage())` membocorkan host/port/user DB | Sedang | Pesan exception PDO ditampilkan ke pengunjung | Pesan dicatat ke `error_log`, pengguna hanya melihat "Layanan basis data sedang tidak tersedia" (HTTP 503). Kredensial bisa di-override via env (`DB_HOST`, `DB_PASS`, …). | Tidak ada detail teknis ke pengguna. | K |
| F-12 | semua halaman | Error/exception tak tertangani dapat tampil ke pengguna | Sedang | HTTP 500 (kasus F-05/F-06) | `set_exception_handler` global: detail ke log, pengguna melihat pesan generik. `display_errors` mati kecuali `APP_DEBUG=1`. | Pesan generik, tanpa stack trace. | K |
| F-13 | `anggota/tambah`, `anggota/edit` | `catch (PDOException)` menelan **semua** error DB sebagai "No. anggota sudah dipakai" (menyesatkan, menyembunyikan error lain) | Rendah | Kode lama: semua exception → pesan duplikat | `adalah_duplikat()` hanya cocok untuk SQLSTATE **23505**; error lain dilempar ke handler global. | Duplikat → pesan "sudah terdaftar"; error lain → log. | U+K |
| F-14 | `auth/login.php` | Waktu respons berbeda untuk username valid vs tidak (*user enumeration* via timing) | Rendah | `password_verify` dilewati jika user tidak ada | `password_verify` selalu dijalankan (hash dummy bila user tidak ada); pesan error sama untuk username/password salah. | Respons seragam. | K |
| F-15 | `buku/hapus`, `anggota/hapus` | ID tidak divalidasi; hapus 0 baris tetap menampilkan "berhasil" | Rendah | `(int)$_POST['id']` → `0` → pesan sukses palsu | `id_valid()` (1…2147483647) + cek `rowCount()`. | Pesan "ID tidak valid" / "tidak ditemukan". | K |
| F-16 | `buku/*` | Whitelist kategori ditulis **dua kali** (form & validasi) → rawan tidak sinkron | Info | Array literal di 2 file | Satu konstanta `KATEGORI_BUKU` dipakai form dan validasi. + `CHECK` di DB. | Satu sumber kebenaran. | K |
| F-17 | `index.php`, `buku/list`, `anggota/list` | `$pdo->query(...)` (statis, tidak rentan, tetapi tidak konsisten dengan aturan "100% prepared") | Info | 4 pemanggilan `->query()` | Diganti `prepare()` + `execute()`; `SELECT *` → kolom eksplisit. | `grep -rn "->query(\|->exec("` → **0 hasil**. | K |

---

## 4. Checklist per Halaman

Legenda: ✅ sudah aman/diterapkan · ➖ tidak berlaku

| Halaman | SQLi (prepared) | XSS (`e()`) | CSRF | Validasi tipe/panjang/whitelist | Otorisasi | Catatan perbaikan |
|---|:--:|:--:|:--:|:--:|:--:|---|
| `auth/login.php` | ✅ | ✅ | ✅ | ✅ panjang | ➖ | F-03, F-14; `session_regenerate_id(true)` + token baru setelah login |
| `auth/register.php` | ✅ | ✅ | ✅ | ✅ regex username, panjang, role whitelist | ✅ role dikunci | F-03, **F-04** |
| `auth/logout.php` | ➖ | ➖ | ✅ POST+token | ➖ | ➖ | F-02; sesi & cookie dihapus tuntas |
| `buku/list.php` | ✅ | ✅ | ✅ (form Hapus) | ➖ | login | F-17; konfirmasi via `data-confirm` |
| `buku/tambah.php` | ✅ | ✅ | ✅ | ✅ | login | F-01, F-05–F-07 |
| `buku/edit.php` | ✅ | ✅ | ✅ | ✅ + `id_valid()` | login | F-01, F-05–F-07, F-15 |
| `buku/hapus.php` | ✅ | ➖ | ✅ | ✅ `id_valid()` + `rowCount()` | login | F-01, F-15 |
| `buku/_form.php`, `_validasi.php` | ➖ | ✅ | ✅ field token | ✅ `KATEGORI_BUKU`, ISBN, tahun, stok | ➖ | F-10, F-16 |
| `anggota/list.php` | ✅ | ✅ | ✅ (form Hapus) | ➖ | tombol Hapus hanya admin | F-17 |
| `anggota/tambah.php` | ✅ | ✅ | ✅ | ✅ | login | F-01, F-13 |
| `anggota/edit.php` | ✅ | ✅ | ✅ | ✅ + `id_valid()` | login | F-01, F-13, F-15 |
| `anggota/hapus.php` | ✅ | ➖ | ✅ | ✅ `id_valid()` + `rowCount()` | **admin** (`wajib_admin()`) | F-01, F-15 |
| `anggota/_form.php`, `_validasi.php` | ➖ | ✅ | ✅ field token | ✅ no. anggota, no. HP, panjang | ➖ | F-10 |
| `index.php` | ✅ | ✅ | ➖ | ➖ | – | F-17; `style=` inline → class CSS |
| `includes/koneksi.php` | ✅ native prepare | ➖ | ➖ | ➖ | ➖ | F-11 |
| `includes/keamanan.php` *(baru)* | ➖ | ✅ `e()` | ✅ | ✅ helper | ➖ | F-08, F-09, F-12 — pusat fungsi keamanan |
| `includes/auth.php` | ➖ | ➖ | ➖ | ➖ | ✅ guard + `Cache-Control: no-store` | Halaman terproteksi tidak di-cache |
| `includes/header.php` | ➖ | ✅ | ✅ form logout | ➖ | ➖ | F-02, F-08; kelas flash di-whitelist |

---

## 5. Tugas Mandiri — `session_regenerate_id()` setelah login

`session_regenerate_id(true)` dipanggil di `auth/login.php` **tepat setelah** `password_verify()` berhasil dan **sebelum** data user ditulis ke `$_SESSION`. Parameter `true` menghapus file sesi lama sehingga ID lama tidak bisa dipakai lagi.

Bukti (uji dengan ID sesi "tanam" acak yang belum pernah ada di server, mensimulasikan penyerang yang memaksakan ID korban):

| | Before | After |
|---|---|---|
| ID sesi setelah login | berganti (`fixd9ded19…` → `525v8nfq0b…`) | berganti (`fix7ebfdee…` → `7e2k1nhu57…`) |
| ID lama dipakai penyerang | tidak bisa masuk | tidak bisa masuk |
| ID buatan penyerang diterima server sejak awal? | **ya** (mode non-strict) | **tidak** — `session.use_strict_mode=1` menolak ID yang tidak dikenal |

> Pemanggilan `session_regenerate_id(true)` sudah ada di `login.php` Jobsheet 10. Pada Jobsheet 11 pertahanannya diperkuat: strict mode, `HttpOnly` + `SameSite`, token CSRF diganti setelah login, dan `logout.php` menghancurkan sesi + menghapus cookie + membuat ID baru.

---

## 6. Perubahan Perilaku yang Perlu Diketahui

1. **Register tidak lagi menampilkan dropdown Role bagi pengunjung.** Akun baru = `petugas`. Akun pertama pada tabel `users` kosong = `admin`. Admin yang sedang login dapat membuka `/auth/register.php` untuk membuat admin/petugas baru. Untuk menaikkan akun yang sudah ada: `UPDATE users SET role='admin' WHERE username='...';`
2. **ISBN kini divalidasi** (angka, `-`, `X`; 10–17 karakter) bila diisi. Kosong tetap boleh.
3. **Logout kini berupa tombol (POST)**, bukan link.
4. **Error tidak lagi tampil di layar.** Untuk debugging lokal jalankan dengan environment `APP_DEBUG=1`; log ada di error log PHP/Apache.

---

## 7. Batasan (di luar cakupan "keamanan dasar")

Belum diterapkan, disarankan untuk pengembangan berikutnya: pembatasan percobaan login (*rate limiting / lockout*), HTTPS + HSTS di produksi, timeout sesi karena tidak aktif, pencatatan audit (*audit log*), akun DB khusus aplikasi dengan hak minimal (bukan `postgres`), dan kata sandi DB dari environment/secret manager (bukan nilai bawaan). Uji XSS di sini memverifikasi **HTML keluaran** (di-escape) dan header CSP; eksekusi di browser nyata tidak diautomasi.

---

## 8. Menjalankan Ulang Uji

```bash
# 1) pastikan ada akun uji:  admin/admin123 (role admin) dan petugas/petugas123 (role petugas)
# 2) jalankan skrip (butuh bash, curl, psql):
bash dokumen/uji_keamanan.sh http://localhost/simpus-mini
```
Skrip membatalkan diri (`[FATAL]`) bila login uji gagal, agar tidak memberi hasil "aman" palsu. Hasil mentah tersimpan di `hasil_uji_BEFORE_jobsheet10.txt` dan `hasil_uji_AFTER_jobsheet11.txt`.

Verifikasi manual cepat:
```bash
grep -rnE "\->(query|exec)\(" simpus-mini/        # harus kosong (100% prepared)
grep -rnE "onsubmit=|onclick=| style=\"" simpus-mini/ --include=*.php   # harus kosong (kompatibel CSP)
```
