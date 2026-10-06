-- SIMPUS-Mini — skema database (Jobsheet 8, 10, 12)
-- Pakai:  createdb simpus_mini && psql -d simpus_mini -f database/schema.sql

BEGIN;

DROP TABLE IF EXISTS peminjaman CASCADE;
DROP TABLE IF EXISTS anggota    CASCADE;
DROP TABLE IF EXISTS buku       CASCADE;
DROP TABLE IF EXISTS users      CASCADE;

CREATE TABLE users (
    id         SERIAL PRIMARY KEY,
    nama       VARCHAR(100) NOT NULL,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,                 -- hasil password_hash()
    role       VARCHAR(10)  NOT NULL DEFAULT 'petugas'
               CHECK (role IN ('admin', 'petugas')),
    created_at TIMESTAMP    NOT NULL DEFAULT now()
);

CREATE TABLE buku (
    id        SERIAL PRIMARY KEY,
    judul     VARCHAR(200) NOT NULL,
    pengarang VARCHAR(120) NOT NULL,
    tahun     SMALLINT     NOT NULL CHECK (tahun BETWEEN 1000 AND 2200),
    isbn      VARCHAR(20)  NOT NULL DEFAULT '',
    stok      INTEGER      NOT NULL DEFAULT 0 CHECK (stok >= 0),   -- stok tidak boleh minus
    kategori  VARCHAR(30)  NOT NULL
);
CREATE UNIQUE INDEX buku_isbn_unik ON buku (isbn) WHERE isbn <> '';

CREATE TABLE anggota (
    id         SERIAL PRIMARY KEY,
    nama       VARCHAR(100) NOT NULL,
    no_anggota VARCHAR(20)  NOT NULL UNIQUE,
    alamat     TEXT         NOT NULL DEFAULT '',
    no_hp      VARCHAR(20)  NOT NULL DEFAULT ''
);

CREATE TABLE peminjaman (
    id              SERIAL PRIMARY KEY,
    buku_id         INTEGER   NOT NULL REFERENCES buku(id)    ON DELETE RESTRICT,
    anggota_id      INTEGER   NOT NULL REFERENCES anggota(id) ON DELETE RESTRICT,
    petugas_id      INTEGER            REFERENCES users(id)   ON DELETE SET NULL,
    tanggal_pinjam  TIMESTAMP NOT NULL DEFAULT now(),
    tanggal_kembali TIMESTAMP,
    status          VARCHAR(15) NOT NULL DEFAULT 'dipinjam'
                    CHECK (status IN ('dipinjam', 'dikembalikan')),
    CHECK ((status = 'dipinjam' AND tanggal_kembali IS NULL)
        OR (status = 'dikembalikan' AND tanggal_kembali IS NOT NULL))
);

CREATE INDEX peminjaman_status_idx  ON peminjaman (status);
CREATE INDEX peminjaman_anggota_idx ON peminjaman (anggota_id);
-- Satu anggota tidak boleh memegang eksemplar judul yang sama dua kali sekaligus.
CREATE UNIQUE INDEX peminjaman_aktif_unik ON peminjaman (anggota_id, buku_id) WHERE status = 'dipinjam';

COMMIT;
