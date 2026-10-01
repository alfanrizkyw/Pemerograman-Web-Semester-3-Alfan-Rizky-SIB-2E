#!/usr/bin/env bash
# =============================================================================
# uji_keamanan.sh — Uji keamanan otomatis SIMPUS-Mini (Jobsheet 11)
#
# Cara pakai:
#   1. Jalankan PostgreSQL + web server (mis. XAMPP, atau: php -S 127.0.0.1:8000 -t <folder induk simpus-mini>)
#   2. Pastikan ada 2 akun uji di tabel users:
#        admin    / admin123    (role admin)
#        petugas  / petugas123  (role petugas)
#   3. bash uji_keamanan.sh http://127.0.0.1:8000/simpus-mini
#
# Env opsional:  PGPASSWORD (default 12345678), PGHOST, PGUSER, PGDB
# Skrip ini membuat & menghapus data uji sendiri (prefix "UJI-").
# Hasil: tiap baris berformat  [AMAN]/[RENTAN]/[INFO]  <deskripsi>
# =============================================================================
BASE="${1:-http://127.0.0.1:8000/simpus-mini}"
export PGPASSWORD="${PGPASSWORD:-12345678}"
PSQL="psql -h ${PGHOST:-localhost} -U ${PGUSER:-postgres} -d ${PGDB:-simpus_mini} -tA -c"
TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
q() { $PSQL "$1" 2>/dev/null; }
ok()  { echo "[AMAN]   $*"; }
bad() { echo "[RENTAN] $*"; }
info(){ echo "[INFO]   $*"; }
cek() { if [ "$1" = "1" ]; then ok "$2"; else bad "$2"; fi; }

# ambil nilai csrf_token dari sebuah halaman (kosong jika halaman tidak punya token)
token() { curl -s -b "$1" -c "$1" "$BASE/$2" | grep -o 'name="csrf_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"$//'; }
login() { # login USER PASS JAR
  local t; t=$(token "$3" auth/login.php)
  curl -s -o /dev/null -b "$3" -c "$3" --data-urlencode "username=$1" --data-urlencode "password=$2" --data-urlencode "csrf_token=$t" "$BASE/auth/login.php"
}
logged_in() { [ "$(curl -s -o /dev/null -w '%{http_code}' -b "$1" "$BASE/buku/list.php")" = "200" ]; }

# bersihkan sisa uji sebelumnya
q "DELETE FROM buku WHERE judul LIKE 'UJI-%' OR judul LIKE '%alert(%' OR pengarang LIKE 'UJI-%'" >/dev/null
q "DELETE FROM anggota WHERE no_anggota LIKE 'UJI%'" >/dev/null
q "DELETE FROM users WHERE username LIKE 'hacker%'" >/dev/null

echo "================ 1. SQL INJECTION ================"
for P in "' OR '1'='1" "admin'--" "' OR 1=1 --" "x'; DROP TABLE users;--" "' UNION SELECT 1,'a','b','c','admin'--"; do
  J="$TMP/sqli.jar"; rm -f "$J"
  login "$P" "' OR '1'='1" "$J"
  if logged_in "$J"; then bad "Login bypass dengan username: $P"; else ok "Login ditolak untuk payload: $P"; fi
done
cek "$([ "$(q "SELECT to_regclass('public.users') IS NOT NULL")" = "t" ] && echo 1 || echo 0)" "Tabel users masih ada setelah payload DROP TABLE"
J="$TMP/a.jar"; rm -f "$J"; login admin admin123 "$J"
# PRASYARAT: login sah harus berhasil, jika tidak semua uji di bawah akan "lolos" secara palsu
logged_in "$J" || { echo "[FATAL]  Login admin/admin123 gagal -> uji dibatalkan (hasil tidak valid). Cek akun uji & URL."; exit 1; }
ok "Prasyarat: login sah admin/admin123 berhasil (sesi uji valid)"
BOOK=$(q "INSERT INTO buku(judul,pengarang,tahun,stok,kategori) VALUES ('UJI-sqli','UJI-x',2020,1,'Sains') RETURNING id" | head -1)
R=$(curl -s -b "$J" "$BASE/buku/edit.php?id=$BOOK%20OR%201=1;DROP%20TABLE%20buku;--")
if [ "$(q "SELECT to_regclass('public.buku') IS NOT NULL")" = "t" ]; then ok "Parameter id=<payload SQL> tidak merusak tabel buku"; else bad "Tabel buku terhapus lewat parameter id"; fi
CODE=$(curl -s -o /dev/null -w '%{http_code}' -b "$J" "$BASE/buku/edit.php?id=abc")
info "GET edit.php?id=abc -> HTTP $CODE"

echo; echo "================ 2. XSS ================"
t=$(token "$J" buku/tambah.php)
post_buku() { curl -s -o /dev/null -b "$J" -c "$J" --data-urlencode "judul=$1" --data-urlencode "pengarang=$2" --data-urlencode "tahun=2020" --data-urlencode "isbn=" --data-urlencode "stok=1" --data-urlencode "kategori=Sains" --data-urlencode "csrf_token=$t" "$BASE/buku/tambah.php"; }
post_buku "<script>alert(1)</script>" "\"><img src=x onerror=alert(2)>"
LIST=$(curl -s -b "$J" "$BASE/buku/list.php")
echo "$LIST" | grep -q '<script>alert(1)</script>' && bad "Judul <script> muncul MENTAH di tabel buku (XSS tereksekusi)" || ok "Judul <script>alert(1)</script> tampil sebagai teks (di-escape)"
echo "$LIST" | grep -q '<img src=x onerror' && bad "Tag <img onerror> muncul mentah di tabel buku" || ok "Pengarang berisi <img onerror> di-escape"
echo "$LIST" | grep -q '&lt;script&gt;alert(1)&lt;/script&gt;' && info "Bukti HTML: &lt;script&gt;alert(1)&lt;/script&gt; ada di sumber halaman"
XID=$(q "SELECT id FROM buku WHERE judul LIKE '%alert(1)%' LIMIT 1")
[ -n "$XID" ] && ok "Prasyarat XSS: payload <script> benar-benar tersimpan di database (uji bermakna)" || bad "Payload XSS tidak tersimpan -> uji XSS tidak bermakna"
q "UPDATE buku SET judul='UJI\" autofocus onfocus=\"alert(9)' WHERE id=$XID" >/dev/null
EDIT=$(curl -s -b "$J" "$BASE/buku/edit.php?id=$XID")
echo "$EDIT" | grep -q 'onfocus="alert(9)' && bad "Atribut value= bisa di-breakout (XSS di form edit)" || ok "Breakout atribut value=\"...\" di form edit gagal (tanda kutip di-escape)"
t=$(token "$J" anggota/tambah.php)
curl -s -o /dev/null -b "$J" -c "$J" --data-urlencode "nama=<img src=x onerror=alert(3)>" --data-urlencode "no_anggota=UJI001" --data-urlencode "alamat=<script>alert(4)</script>" --data-urlencode "no_hp=081234567890" --data-urlencode "csrf_token=$t" "$BASE/anggota/tambah.php"
[ "$(q "SELECT count(*) FROM anggota WHERE no_anggota='UJI001'")" = "1" ] && ok "Prasyarat XSS: data anggota berisi tag HTML tersimpan di database" || bad "Data anggota uji tidak tersimpan -> uji tidak bermakna"
AL=$(curl -s -b "$J" "$BASE/anggota/list.php")
echo "$AL" | grep -qE '<img src=x onerror|<script>alert\(4\)' && bad "Data anggota tampil mentah (XSS)" || ok "Nama & alamat anggota berisi tag HTML di-escape"
q "DELETE FROM buku WHERE judul LIKE 'UJI%' OR judul LIKE '%alert(%' OR pengarang LIKE '%onerror%'" >/dev/null

echo; echo "================ 3. CSRF ================"
ID=$(q "INSERT INTO buku(judul,pengarang,tahun,stok,kategori) VALUES ('UJI-target-hapus','UJI-p',2020,1,'Sains') RETURNING id" | head -1)
curl -s -o /dev/null -b "$J" -c "$J" --data "id=$ID" "$BASE/buku/hapus.php"   # tanpa token = simulasi form dari situs penyerang
[ "$(q "SELECT count(*) FROM buku WHERE id=$ID")" = "1" ] && ok "POST hapus buku TANPA token ditolak (data masih ada)" || bad "Buku terhapus lewat POST tanpa token (CSRF berhasil)"
ID2=$(q "INSERT INTO buku(judul,pengarang,tahun,stok,kategori) VALUES ('UJI-target-hapus2','UJI-p',2020,1,'Sains') RETURNING id" | head -1)
curl -s -o /dev/null -b "$J" -c "$J" --data "id=$ID2&csrf_token=0000000000000000000000000000000000000000000000000000000000000000" "$BASE/buku/hapus.php"
[ "$(q "SELECT count(*) FROM buku WHERE id=$ID2")" = "1" ] && ok "POST hapus dengan token PALSU ditolak" || bad "Buku terhapus dengan token palsu"
curl -s -o /dev/null -b "$J" -c "$J" --data-urlencode "judul=UJI-DIRETAS" --data-urlencode "pengarang=UJI-p" --data "tahun=2020&isbn=&stok=1&kategori=Sains" "$BASE/buku/edit.php?id=$ID"
[ "$(q "SELECT judul FROM buku WHERE id=$ID")" = "UJI-target-hapus" ] && ok "POST edit buku TANPA token ditolak (judul tidak berubah)" || bad "Judul buku berubah lewat POST tanpa token (CSRF edit berhasil)"
ANG=$(q "INSERT INTO anggota(nama,no_anggota,alamat,no_hp) VALUES ('UJI-ang','UJI002','x','081234567890') RETURNING id" | head -1)
curl -s -o /dev/null -b "$J" -c "$J" --data "id=$ANG" "$BASE/anggota/hapus.php"
[ "$(q "SELECT count(*) FROM anggota WHERE id=$ANG")" = "1" ] && ok "POST hapus anggota TANPA token ditolak" || bad "Anggota terhapus lewat POST tanpa token"
logged_in "$J" || { rm -f "$J"; login admin admin123 "$J"; }
curl -s -o /dev/null -b "$J" -c "$J" "$BASE/auth/logout.php"   # logout lewat GET (mis. <img src=logout.php>)
logged_in "$J" && ok "Logout via GET (<img src=logout.php>) tidak mengeluarkan user" || bad "User ter-logout paksa via GET /auth/logout.php (logout-CSRF)"
rm -f "$J"; login admin admin123 "$J"
# alur sah dengan token harus tetap bekerja
t=$(token "$J" buku/list.php)
curl -s -o /dev/null -b "$J" -c "$J" --data "id=$ID&csrf_token=$t" "$BASE/buku/hapus.php"
[ "$(q "SELECT count(*) FROM buku WHERE id=$ID")" = "0" ] && ok "Hapus buku dengan token sah (alur normal) tetap berfungsi" || bad "Regresi: hapus buku dengan token sah GAGAL"

echo; echo "================ 4. VALIDASI & SANITASI INPUT ================"
t=$(token "$J" buku/tambah.php)
body() { curl -s -w '\nHTTP=%{http_code}' -b "$J" -c "$J" "$@"; }
R=$(body --data-urlencode "judul[]=x" --data "pengarang=p&tahun=2020&isbn=&stok=1&kategori=Sains&csrf_token=$t" "$BASE/buku/tambah.php")
echo "$R" | grep -qiE 'TypeError|Stack trace|Fatal error|HTTP=500' && bad "Input array judul[]=x menyebabkan error fatal / kebocoran info ($(echo "$R" | grep -o 'HTTP=.*'))" || ok "Input array judul[]=x ditangani sebagai input tidak valid"
R=$(body --data "judul=UJI-stok&pengarang=p&tahun=2020&isbn=&stok=99999999999&kategori=Sains&csrf_token=$t" "$BASE/buku/tambah.php")
echo "$R" | grep -qiE 'SQLSTATE|Stack trace|Fatal error|HTTP=500' && bad "stok=99999999999 menyebabkan error database tak tertangani ($(echo "$R" | grep -o 'HTTP=.*'))" || ok "stok=99999999999 ditolak validasi"
R=$(body --data "judul=UJI-kat&pengarang=p&tahun=2020&isbn=&stok=1&kategori=Hacking&csrf_token=$t" "$BASE/buku/tambah.php")
[ "$(q "SELECT count(*) FROM buku WHERE judul='UJI-kat'")" = "0" ] && ok "Kategori di luar whitelist ditolak" || bad "Kategori 'Hacking' tersimpan"
R=$(body --data "judul=UJI-thn&pengarang=p&tahun=1e3&isbn=&stok=1&kategori=Sains&csrf_token=$t" "$BASE/buku/tambah.php")
[ "$(q "SELECT count(*) FROM buku WHERE judul='UJI-thn'")" = "0" ] && ok "Tahun '1e3' (notasi ilmiah) ditolak" || bad "Tahun '1e3' tersimpan"
LONG=$(head -c 300 /dev/zero | tr '\0' 'A')
R=$(body --data "judul=$LONG&pengarang=p&tahun=2020&isbn=&stok=1&kategori=Sains&csrf_token=$t" "$BASE/buku/tambah.php")
echo "$R" | grep -qiE 'SQLSTATE|Stack trace|HTTP=500' && bad "Judul 300 karakter menyebabkan error database (HTTP 500)" || ok "Judul 300 karakter ditolak validasi panjang"
R=$(body --data "judul=UJI-nul%00x&pengarang=p&tahun=2020&isbn=&stok=1&kategori=Sains&csrf_token=$t" "$BASE/buku/tambah.php")
if echo "$R" | grep -qiE 'SQLSTATE|Stack trace|HTTP=500'; then bad "Null byte (%00) pada judul menyebabkan error database"
else
  SV=$(q "SELECT judul FROM buku WHERE judul LIKE 'UJI-nul%' LIMIT 1")
  if [ -z "$SV" ]; then ok "Judul dengan null byte (%00) ditolak"
  elif [ "$SV" = "UJI-nulx" ]; then ok "Null byte (%00) dibuang oleh sanitasi, tersimpan: '$SV'"
  else bad "Null byte (%00) menyebabkan data terpotong/korup, tersimpan: '$SV'"; fi
fi
t=$(token "$J" anggota/tambah.php)
R=$(body --data "nama=UJI-hp&no_anggota=UJI003&alamat=&no_hp=abc<>&csrf_token=$t" "$BASE/anggota/tambah.php")
[ "$(q "SELECT count(*) FROM anggota WHERE no_anggota='UJI003'")" = "0" ] && ok "No. HP tidak valid ditolak" || bad "No. HP 'abc<>' tersimpan"

echo; echo "================ 5. AUTENTIKASI / OTORISASI / SESSION ================"
JR="$TMP/reg.jar"; rm -f "$JR"
tr_=$(token "$JR" auth/register.php)
curl -s -o /dev/null -b "$JR" -c "$JR" --data "nama=Hacker&username=hacker1&password=hacker123&konfirmasi=hacker123&role=admin&csrf_token=$tr_" "$BASE/auth/register.php"
RL=$(q "SELECT role FROM users WHERE username='hacker1'")
case "$RL" in admin) bad "Pengunjung anonim bisa mendaftar sebagai ADMIN (privilege escalation)";; petugas) ok "Register anonim dengan role=admin dipaksa menjadi 'petugas'";; *) info "Registrasi hacker1 tidak tersimpan (role='$RL')";; esac
JP="$TMP/p.jar"; rm -f "$JP"; login petugas petugas123 "$JP"
ANG2=$(q "INSERT INTO anggota(nama,no_anggota,alamat,no_hp) VALUES ('UJI-ang2','UJI004','x','081234567890') RETURNING id" | head -1)
tp=$(token "$JP" anggota/list.php)
curl -s -o /dev/null -b "$JP" -c "$JP" --data "id=$ANG2&csrf_token=$tp" "$BASE/anggota/hapus.php"
[ "$(q "SELECT count(*) FROM anggota WHERE id=$ANG2")" = "1" ] && ok "Petugas (non-admin) tidak bisa menghapus anggota" || bad "Petugas berhasil menghapus anggota"
# session fixation
JF="$TMP/fix.jar"; rm -f "$JF"
FIX="fix$(head -c 12 /dev/urandom | od -An -tx1 | tr -d ' \n')"   # ID acak baru tiap run (belum pernah ada di server)
SC=$(curl -s -D - -o /dev/null -H "Cookie: PHPSESSID=$FIX" "$BASE/auth/login.php" | grep -i '^set-cookie: PHPSESSID' | head -1)
echo "$SC" | grep -q "$FIX" && bad "Server MENERIMA ID sesi buatan penyerang (tanpa strict mode)" || { [ -n "$SC" ] && ok "Strict mode: ID sesi buatan penyerang ditolak, server menerbitkan ID baru" || bad "Server menerima ID sesi tanam tanpa menerbitkan ID baru"; }
rm -f "$JF"
printf '127.0.0.1\tFALSE\t/\tFALSE\t0\tPHPSESSID\t%s\n' "$FIX" > "$JF"
tf=$(curl -s -b "$JF" -c "$JF" "$BASE/auth/login.php" | grep -o 'name="csrf_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"$//')
curl -s -o /dev/null -b "$JF" -c "$JF" --data "username=admin&password=admin123&csrf_token=$tf" "$BASE/auth/login.php"
NEWID=$(awk '$6=="PHPSESSID"{print $7}' "$JF" | tail -1)
[ "$NEWID" != "$FIX" ] && ok "Session ID berubah setelah login (fixation dicegah): ${FIX:0:10}… -> ${NEWID:0:10}…" || bad "Session ID tetap sama setelah login (session fixation)"
ATT="$TMP/att.jar"; printf '127.0.0.1\tFALSE\t/\tFALSE\t0\tPHPSESSID\t%s\n' "$FIX" > "$ATT"
logged_in "$ATT" && bad "Penyerang yang tahu ID lama bisa masuk sebagai admin" || ok "ID sesi lama (hasil fixation) tidak bisa dipakai masuk"
# cookie & header
HDR=$(curl -s -D - -o /dev/null "$BASE/auth/login.php")
echo "$HDR" | grep -i '^set-cookie' | grep -qi 'httponly' && ok "Cookie sesi memakai HttpOnly" || bad "Cookie sesi tanpa HttpOnly"
echo "$HDR" | grep -i '^set-cookie' | grep -qi 'samesite' && ok "Cookie sesi memakai SameSite" || bad "Cookie sesi tanpa SameSite"
echo "$HDR" | grep -qi '^content-security-policy' && ok "Header Content-Security-Policy terkirim" || bad "Tidak ada header Content-Security-Policy"
echo "$HDR" | grep -qi '^x-frame-options' && ok "Header X-Frame-Options terkirim (anti-clickjacking)" || bad "Tidak ada X-Frame-Options (rawan clickjacking)"
echo "$HDR" | grep -qi '^x-content-type-options' && ok "Header X-Content-Type-Options: nosniff terkirim" || bad "Tidak ada X-Content-Type-Options"
# akses langsung ke partial
R=$(curl -s -w '\nHTTP=%{http_code}' "$BASE/buku/_form.php")
echo "$R" | grep -qiE 'Warning|Notice|Undefined|Fatal|HTTP=500' && bad "Akses langsung buku/_form.php menghasilkan error/HTML terpotong ($(echo "$R" | grep -o 'HTTP=.*'))" || ok "Akses langsung buku/_form.php tidak membocorkan informasi ($(echo "$R" | grep -o 'HTTP=.*'))"

echo; echo "================ 6. REGRESI: ALUR NORMAL HARUS TETAP BERFUNGSI ================"
rm -f "$J"; login admin admin123 "$J"
t=$(token "$J" buku/tambah.php)
curl -s -o /dev/null -b "$J" -c "$J" --data-urlencode "judul=UJI-normal" --data "pengarang=UJI-pg&tahun=2021&isbn=978-602-8519-93-9&stok=5&kategori=Teknologi&csrf_token=$t" "$BASE/buku/tambah.php"
NB=$(q "SELECT id FROM buku WHERE judul='UJI-normal'")
[ -n "$NB" ] && ok "Tambah buku (data sah, ISBN 978-602-8519-93-9) berhasil" || bad "Tambah buku data sah GAGAL"
t=$(token "$J" "buku/edit.php?id=$NB")
curl -s -o /dev/null -b "$J" -c "$J" --data-urlencode "judul=UJI-normal-edit" --data "pengarang=UJI-pg&tahun=2022&isbn=&stok=7&kategori=Sains&csrf_token=$t" "$BASE/buku/edit.php?id=$NB"
[ "$(q "SELECT judul||'/'||stok FROM buku WHERE id=$NB")" = "UJI-normal-edit/7" ] && ok "Edit buku (data sah + token) berhasil" || bad "Edit buku data sah GAGAL"
t=$(token "$J" anggota/tambah.php)
curl -s -o /dev/null -b "$J" -c "$J" --data "nama=UJI-Budi&no_anggota=UJI100&alamat=Jl.+Mawar+1&no_hp=%2B6281234567890&csrf_token=$t" "$BASE/anggota/tambah.php"
[ "$(q "SELECT count(*) FROM anggota WHERE no_anggota='UJI100'")" = "1" ] && ok "Tambah anggota (data sah, no HP +62...) berhasil" || bad "Tambah anggota data sah GAGAL"
t=$(token "$J" anggota/tambah.php)
R=$(curl -s -b "$J" -c "$J" --data "nama=UJI-Dup&no_anggota=UJI100&alamat=&no_hp=&csrf_token=$t" "$BASE/anggota/tambah.php")
echo "$R" | grep -q 'sudah terdaftar' && ok "No. anggota duplikat -> pesan 'sudah terdaftar' (bukan error 500)" || bad "Duplikat no. anggota tidak ditangani dengan benar"
t=$(token "$J" auth/register.php)
curl -s -o /dev/null -b "$J" -c "$J" --data "nama=Admin+Baru&username=hacker_adm&password=rahasia123&konfirmasi=rahasia123&role=admin&csrf_token=$t" "$BASE/auth/register.php"
[ "$(q "SELECT role FROM users WHERE username='hacker_adm'")" = "admin" ] && ok "Admin yang sedang login tetap bisa membuat akun ber-role admin" || bad "Admin tidak bisa membuat akun admin"
t=$(token "$J" index.php)
curl -s -o /dev/null -b "$J" -c "$J" --data "csrf_token=$t" "$BASE/auth/logout.php"
logged_in "$J" && bad "Logout sah (POST + token) GAGAL" || ok "Logout via tombol (POST + token) berfungsi"

# bersihkan
q "DELETE FROM buku WHERE judul LIKE 'UJI%'" >/dev/null; q "DELETE FROM anggota WHERE no_anggota LIKE 'UJI%'" >/dev/null; q "DELETE FROM users WHERE username LIKE 'hacker%'" >/dev/null
echo; echo "Selesai."
