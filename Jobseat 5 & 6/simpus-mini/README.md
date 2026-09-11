# SIMPUS-Mini

Aplikasi Perpustakaan Sederhana — Studi kasus Praktikum Desain & Pemrograman Web.

## Progres saat ini
- ✅ Jobsheet 1 — Struktur HTML5 semantic (Beranda, Daftar Buku, Tambah Buku, Anggota)
- ✅ Jobsheet 2 — Styling CSS3 (Flexbox navbar, Grid kartu statistik, tabel & form)
- ✅ Jobsheet 3 — Responsive (mobile-first, hamburger menu, tabel scroll, breakpoint)
- ✅ Jobsheet 5 — DOM & Event JS: hamburger toggle, validasi form client-side, filter
  pencarian real-time, konfirmasi hapus
- ✅ Jobsheet 6 — Fetch + JSON: render tabel dari `data/buku.json` & `data/anggota.json`,
  loading indicator, error handling, kombinasi `async/await` dan `.then()`
- ⏭️ Jobsheet 7+ — Back-end PHP & PostgreSQL (menyusul)

## Struktur folder
```
simpus-mini/
├── index.html
├── assets/css/style.css
├── assets/js/app.js
├── buku/list.html
├── buku/tambah.html
├── anggota/list.html
├── anggota/tambah.html
└── data/
    ├── buku.json
    └── anggota.json
```

## Cara menjalankan
Karena `fetch()` butuh HTTP (tidak bisa dibuka langsung via `file://`), jalankan local
server sederhana dari root folder:

```bash
cd simpus-mini
python3 -m http.server 8000
```

Lalu buka `http://localhost:8000` di browser.

## Catatan
- Data buku & anggota masih dari file JSON statis (`data/`), akan diganti PostgreSQL
  sungguhan mulai Jobsheet 8.
- Tombol Hapus baru menghapus tampilan (front-end saja), belum menghapus data permanen
  (menyusul saat sudah tersambung PHP di Jobsheet 9).


## Perbaikan versi ini
- Data buku, anggota, dan peminjaman disimpan sebagai satu sumber data di `localStorage`.
- Tombol Tambah benar-benar menambah data dan data tetap ada setelah refresh.
- Hapus benar-benar menghapus data dari penyimpanan browser.
- Edit buku dan anggota tersedia.
- Statistik Beranda mengambil angka langsung dari sumber data yang sama, sehingga sinkron dengan tabel.
- Perubahan otomatis tersinkron ke tab browser lain melalui `storage` event.
- Login demo menggunakan email `alfan@gmail.com`.
- Password demo: `alfan`.
- Untuk sinkronisasi antar perangkat/komputer, tetap diperlukan backend/database (misalnya PHP + PostgreSQL); localStorage hanya sinkron antar-tab dalam browser/perangkat yang sama.
