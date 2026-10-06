# SIMPUS-Mini — Jobseat 13 / Final

Aplikasi perpustakaan sederhana yang merupakan kelanjutan langsung dari **Jobseat 1–12**.

Final ini memakai hasil **Jobseat 12 sebagai baseline**, kemudian merapikan presentasi UI/UX agar siap dipresentasikan sebagai satu produk, tanpa mengubah konsep teknis utama yang dipelajari selama praktikum.

## Stack

- HTML5 semantic
- CSS3 responsive
- JavaScript vanilla
- PHP native 8.1+
- PostgreSQL 13+
- PDO_PGSQL

## Modul final

- Beranda dengan statistik real dari database
- Katalog buku untuk tamu
- Login, register, logout, dan session
- Role `admin` dan `petugas`
- CRUD Buku
- CRUD Anggota
- Peminjaman buku
- Pengembalian buku
- Riwayat peminjaman
- Pencarian dan pagination
- Validasi client-side + server-side
- Prepared statement
- XSS escaping
- CSRF token
- Session regeneration setelah login
- Transaksi peminjaman/pengembalian dengan penguncian stok
- Database schema dan seed data

## Prinsip UI/UX final

Tampilan sengaja dibuat **anti-AI-slop**:

- tidak memakai gradient dekoratif
- tidak memakai glassmorphism
- tidak memakai blur berlebihan
- tidak memakai kartu melayang bertumpuk
- tidak memakai icon untuk setiap label
- tidak memakai animasi hanya untuk hiasan
- hierarchy informasi dibuat dari tipografi, spacing, garis, dan warna
- layout tetap nyaman pada mobile, tablet, dan desktop
- fokus utama tetap pada pekerjaan petugas perpustakaan

## Struktur

```text
Jobseat13/
├── index.php
├── katalog.php
├── README.md
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── buku/
│   ├── list.php
│   ├── tambah.php
│   ├── edit.php
│   ├── hapus.php
│   └── _form.php
├── anggota/
│   ├── list.php
│   ├── tambah.php
│   ├── edit.php
│   ├── hapus.php
│   └── _form.php
├── peminjaman/
│   ├── list.php
│   ├── tambah.php
│   ├── kembali.php
│   └── riwayat.php
├── includes/
│   ├── auth.php
│   ├── config.example.php
│   ├── footer.php
│   ├── header.php
│   ├── helpers.php
│   └── koneksi.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
└── database/
    ├── schema.sql
    └── seed.sql
```

## Instalasi

Butuh PHP dengan `pdo_pgsql` dan PostgreSQL.

```bash
createdb simpus_mini
psql -d simpus_mini -f database/schema.sql
psql -d simpus_mini -f database/seed.sql
```

Konfigurasi database melalui environment:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASS
```

Atau gunakan `includes/config.local.php` berdasarkan `includes/config.example.php`.

Jalankan:

```bash
php -S localhost:8000
```

Lalu buka:

```text
http://localhost:8000
```

## Catatan keamanan

Jangan membawa password database atau akun contoh ke deployment publik. Ganti kredensial seed sebelum aplikasi digunakan di luar lingkungan praktikum.

## Keterhubungan dengan Jobseat 1–12

Jobseat 1 membangun struktur HTML, Jobseat 2 styling, Jobseat 3 responsive, Jobseat 4 UX, Jobseat 5 JavaScript, Jobseat 6 fetch/JSON, Jobseat 7 PHP/session, Jobseat 8 PostgreSQL, Jobseat 9 CRUD, Jobseat 10 autentikasi, Jobseat 11 hardening keamanan, dan Jobseat 12 integrasi peminjaman/pengembalian.

Dengan demikian **Jobseat13 bukan proyek baru**, melainkan produk final dari aplikasi yang sama.

## Deployment — Supabase + Deplexo

Production menggunakan PostgreSQL Supabase melalui **Session Pooler (port 5432)** karena aplikasi memakai PDO prepared statements. Jangan gunakan Transaction Pooler port 6543 untuk konfigurasi ini.

Environment variables:

- `DB_HOST` — host Session Pooler dari Supabase
- `DB_PORT=5432`
- `DB_NAME=postgres`
- `DB_USER=postgres.<PROJECT_REF>`
- `DB_PASS` — password database Supabase
- `DB_SSLMODE=require`
- `PORT=3000`
- `APP_BASE_URL` — kosong jika aplikasi berada langsung di root subdomain Deplexo

Deplexo menggunakan `Dockerfile` + `deplexo.yaml` yang sudah disediakan di root proyek. Credential database tidak disimpan di repository.
