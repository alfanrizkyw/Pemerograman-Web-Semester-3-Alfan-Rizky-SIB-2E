<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

const LAMA_PINJAM_HARI = 14;
const KATEGORI_BUKU    = ['Fiksi', 'Nonfiksi', 'Sains', 'Teknologi', 'Sejarah', 'Referensi', 'Anak'];

/* ---------- Output & URL ---------- */

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Path dasar aplikasi, dideteksi otomatis (bisa ditimpa lewat APP_BASE_URL). */
function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $env = getenv('APP_BASE_URL');
    if ($env !== false) {
        return $base = rtrim($env, '/');
    }
    $root = realpath(__DIR__ . '/..');
    $doc  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $base = '';
    if ($root && $doc && str_starts_with($root, $doc) && $root !== $doc) {
        $rel  = trim(str_replace('\\', '/', substr($root, strlen($doc))), '/');
        $base = '/' . implode('/', array_map('rawurlencode', explode('/', $rel)));
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/* ---------- Flash message ---------- */

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_take(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Semua request POST wajib lolos fungsi ini. */
function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa. Kembali ke halaman sebelumnya, muat ulang, lalu coba lagi.');
    }
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Metode tidak diizinkan.');
    }
    csrf_check();
}

/* ---------- Input ---------- */

function post_str(string $key, int $max = 255): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? mb_substr(trim($v), 0, $max) : '';
}

function get_int(string $key, int $default = 0): int
{
    $v = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    return $v === false || $v === null ? $default : $v;
}

function get_str(string $key, int $max = 100): string
{
    $v = $_GET[$key] ?? '';
    return is_string($v) ? mb_substr(trim($v), 0, $max) : '';
}

/* ---------- Tanggal ---------- */

function tgl(?string $ts, bool $jam = false): string
{
    if (!$ts) {
        return '—';
    }
    static $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($ts);
    $s = date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    return $jam ? $s . ', ' . date('H:i', $t) : $s;
}

function jatuh_tempo(string $tanggalPinjam): int
{
    return strtotime($tanggalPinjam . ' +' . LAMA_PINJAM_HARI . ' days');
}

/** Selisih hari (positif = terlambat, negatif = sisa hari). */
function hari_terlambat(string $tanggalPinjam): int
{
    return (int) floor((strtotime('today') - strtotime(date('Y-m-d', jatuh_tempo($tanggalPinjam)))) / 86400);
}

/* ---------- Pagination ---------- */

function paginate(int $total, int $perPage = 10): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page  = min(max(1, get_int('page', 1)), $pages);
    return ['page' => $page, 'pages' => $pages, 'per' => $perPage, 'offset' => ($page - 1) * $perPage, 'total' => $total];
}

function pager(array $p, array $query = []): string
{
    if ($p['pages'] <= 1) {
        return '';
    }
    $href = static function (int $n) use ($query): string {
        $q = array_filter($query + ['page' => $n], static fn($v) => $v !== '' && $v !== null);
        $q['page'] = $n;
        return '?' . http_build_query($q);
    };
    $out = '<nav class="pager" aria-label="Halaman">';
    $out .= $p['page'] > 1
        ? '<a href="' . e($href($p['page'] - 1)) . '" rel="prev">Sebelumnya</a>'
        : '<span class="is-off">Sebelumnya</span>';
    $out .= '<span class="pager__now">Halaman ' . $p['page'] . ' dari ' . $p['pages'] . '</span>';
    $out .= $p['page'] < $p['pages']
        ? '<a href="' . e($href($p['page'] + 1)) . '" rel="next">Berikutnya</a>'
        : '<span class="is-off">Berikutnya</span>';
    return $out . '</nav>';
}

/* ---------- Ikon (SVG inline, 20×20) ---------- */

function icon(string $name): string
{
    static $paths = [
        'home'    => 'M3 10.5 10 4l7 6.5V17a1 1 0 0 1-1 1h-3.5v-5h-5v5H4a1 1 0 0 1-1-1z',
        'book'    => 'M4 4.5A1.5 1.5 0 0 1 5.5 3H16v13H5.5A1.5 1.5 0 0 0 4 17.5zM4 17.5A1.5 1.5 0 0 0 5.5 19H16M8 7h4',
        'users'   => 'M7.5 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM2 17c0-3 2.5-5 5.5-5s5.500 2 5.500 5M13.500 9.500a2.500 2.500 0 1 0-1-4.800M15 12.300c1.800.5 3 2 3 4.200',
        'loan'    => 'M4 7h11l-2.500-2.500M16 13H5l2.500 2.500',
        'plus'    => 'M10 4v12M4 10h12',
        'history' => 'M3.500 10a6.500 6.500 0 1 0 2-4.700M3.500 3.500v3h3M10 6.500V10l2.500 1.500',
        'menu'    => 'M3 6h14M3 10h14M3 14h14',
        'close'   => 'M5 5l10 10M15 5 5 15',
        'out'     => 'M8 3H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h3M12 6.500 16 10l-4 3.500M16 10H8',
        'search'  => 'M9 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12zM17 17l-3.700-3.700',
    ];
    $d = $paths[$name] ?? '';
    return '<svg class="icon" viewBox="0 0 20 20" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
}
