-- =====================================================================
-- Jobsheet 11 — SQL pendukung hardening (jalankan di database simpus_mini)
-- =====================================================================

-- 1) Tabel users (skema yang dipakai auth/register.php & auth/login.php).
--    Lewati jika tabel sudah ada dari Jobsheet 10.
CREATE TABLE IF NOT EXISTS users (
    id       SERIAL PRIMARY KEY,
    nama     VARCHAR(100) NOT NULL,
    username VARCHAR(20)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,                 -- hasil password_hash(), bukan teks biasa
    role     VARCHAR(20)  NOT NULL DEFAULT 'petugas'
);

-- 2) Whitelist di level DATABASE (defense in depth: aplikasi salah pun, DB tetap menolak).
--    Jalankan SATU KALI. Jika data lama melanggar, perbaiki dulu datanya.
ALTER TABLE users ADD CONSTRAINT users_role_check     CHECK (role IN ('admin', 'petugas'));
ALTER TABLE buku  ADD CONSTRAINT buku_kategori_check  CHECK (kategori IN ('Fiksi','Non-Fiksi','Sains','Teknologi','Sejarah','Referensi'));
ALTER TABLE buku  ADD CONSTRAINT buku_stok_check      CHECK (stok >= 0);

-- 3) Register publik sekarang selalu membuat role 'petugas' (akun pertama = admin).
--    Untuk menjadikan akun yang sudah ada sebagai admin:
-- UPDATE users SET role = 'admin' WHERE username = 'nama_user_anda';
