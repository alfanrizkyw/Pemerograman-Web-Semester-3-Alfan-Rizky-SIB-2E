-- Data contoh. Jalankan setelah schema.sql
-- Login:  admin / admin123   ·   petugas / petugas123   (ganti setelah instalasi)
-- Catatan: kolom stok sudah dikurangi jumlah peminjaman yang masih aktif.

BEGIN;

INSERT INTO users (nama, username, password, role) VALUES
 ('Administrator',  'admin',    '$2y$10$gQjyRmD77mN7Jl41fr11A.ukEgfABFdnN3E8jbVF1vqGmBlR8Cpiy', 'admin'),
 ('Petugas Perpus', 'petugas',  '$2y$10$kXzBz2A9A2UbC4p2X.xpO.XIeBhORMgOHtJONiZxK9clkYEOANrKm', 'petugas');

INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori) VALUES
 ('Bumi Manusia',                     'Pramoedya Ananta Toer', 1980, '9789799731234', 3, 'Fiksi'),
 ('Laskar Pelangi',                   'Andrea Hirata',         2005, '9789793062792', 2, 'Fiksi'),
 ('Negeri 5 Menara',                  'Ahmad Fuadi',           2009, '9789792248616', 4, 'Fiksi'),
 ('Sapiens: Riwayat Singkat Umat Manusia', 'Yuval Noah Harari', 2017, '9786024241353', 2, 'Sejarah'),
 ('Atomic Habits',                    'James Clear',           2019, '9786020633176', 5, 'Nonfiksi'),
 ('Filosofi Teras',                   'Henry Manampiring',     2018, '9786024125189', 0, 'Nonfiksi'),
 ('Clean Code',                       'Robert C. Martin',      2008, '9780132350884', 2, 'Teknologi'),
 ('Pemrograman Web dengan PHP',       'Abdul Kadir',           2013, '',              6, 'Teknologi'),
 ('Basis Data: Konsep dan Perancangan', 'Fathansyah',          2018, '',              3, 'Teknologi'),
 ('Kosmos',                           'Carl Sagan',            1980, '9789799073815', 1, 'Sains'),
 ('Kamus Besar Bahasa Indonesia',     'Tim Redaksi KBBI',      2016, '9786022935551', 2, 'Referensi'),
 ('Si Kancil dan Buaya',              'Tim Penulis Cerita Anak', 2015, '',            7, 'Anak');

INSERT INTO anggota (nama, no_anggota, alamat, no_hp) VALUES
 ('Dewi Lestari',     'A-0001', 'Jl. Soekarno Hatta 12, Malang',   '081234560001'),
 ('Raka Pratama',     'A-0002', 'Jl. Veteran 45, Malang',          '081234560002'),
 ('Sinta Maharani',   'A-0003', 'Jl. Ijen 8, Malang',              '081234560003'),
 ('Bagas Kurniawan',  'A-0004', 'Jl. Kawi 21, Malang',             '081234560004'),
 ('Nadia Putri',      'A-0005', 'Jl. Semeru 3, Malang',            '081234560005'),
 ('Fajar Nugroho',    'A-0006', 'Jl. Bunga Merak 19, Malang',      '081234560006');

-- Dewi: pinjaman normal, Raka: terlambat 20 hari, Sinta: sudah dikembalikan.
INSERT INTO peminjaman (buku_id, anggota_id, petugas_id, tanggal_pinjam, tanggal_kembali, status) VALUES
 (1, 1, 2, now() - interval '3 days',  NULL, 'dipinjam'),
 (7, 2, 2, now() - interval '20 days', NULL, 'dipinjam'),
 (6, 3, 2, now() - interval '9 days',  NULL, 'dipinjam'),
 (5, 4, 1, now() - interval '12 days', now() - interval '5 days', 'dikembalikan'),
 (2, 3, 2, now() - interval '30 days', now() - interval '18 days', 'dikembalikan');

COMMIT;
