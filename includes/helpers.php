<?php
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['m' => $msg, 't' => $type];
        return null;
    }
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Permintaan tidak valid (token CSRF salah). Muat ulang halaman lalu coba lagi.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        redirect('login.php');
    }
}

function next_id(PDO $pdo, string $table, string $col, string $prefix, int $pad = 3): string
{
    $len = strlen($prefix);
    $st = $pdo->prepare("SELECT MAX(CAST(SUBSTRING($col, " . ($len + 1) . ") AS UNSIGNED)) FROM $table WHERE $col LIKE ?");
    $st->execute([$prefix . '%']);
    $n = (int)$st->fetchColumn() + 1;
    return $prefix . str_pad((string)$n, $pad, '0', STR_PAD_LEFT);
}

function tgl_indo(?string $d): string
{
    if (!$d) {
        return '-';
    }
    $b = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function hari_selisih(string $dari, string $sampai): int
{
    return (int)floor((strtotime($sampai) - strtotime($dari)) / 86400);
}

function wa_link(string $no, string $pesan): string
{
    $n = preg_replace('/\D/', '', $no);
    if (strpos($n, '0') === 0) {
        $n = '62' . substr($n, 1);
    }
    return 'https://wa.me/' . $n . '?text=' . rawurlencode($pesan);
}

function paginate(PDO $pdo, string $countSql, array $params, int $perPage = 8): array
{
    $st = $pdo->prepare($countSql);
    $st->execute($params);
    $total = (int)$st->fetchColumn();
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
    return ['total' => $total, 'pages' => $pages, 'page' => $page, 'per' => $perPage, 'offset' => ($page - 1) * $perPage];
}

function pager(array $pg): string
{
    if ($pg['pages'] <= 1) {
        return '';
    }
    $q = $_GET;
    $out = '<nav class="pager" aria-label="Halaman">';
    for ($i = 1; $i <= $pg['pages']; $i++) {
        $q['page'] = $i;
        $out .= '<a class="' . ($i === $pg['page'] ? 'on' : '') . '" href="?' . e(http_build_query($q)) . '">' . $i . '</a>';
    }
    return $out . '</nav>';
}
