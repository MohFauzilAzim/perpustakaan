<?php
// Entry point / Router untuk Vercel Serverless Function
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Bersihkan slash awal dan akhir
$path = trim($path, '/');

// Direktori root aplikasi (satu tingkat di atas /api)
$baseDir = dirname(__DIR__);

// Jika root atau index.php
if ($path === '' || $path === 'index.php') {
    require $baseDir . '/index.php';
    exit;
}

// Path file tujuan
$targetFile = $baseDir . '/' . $path;

// Jika file PHP langsung ditemukan
if (is_file($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    require $targetFile;
    exit;
}

// Jika merujuk ke folder dan ada index.php di dalamnya (misal modules/anggota)
if (is_dir($targetFile) && is_file($targetFile . '/index.php')) {
    require $targetFile . '/index.php';
    exit;
}

// Jika static asset ter-route ke sini (fallback)
if (is_file($targetFile)) {
    $ext = pathinfo($targetFile, PATHINFO_EXTENSION);
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
    ];
    $contentType = $mimes[$ext] ?? 'application/octet-stream';
    header('Content-Type: ' . $contentType);
    readfile($targetFile);
    exit;
}

// 404 Not Found
http_response_code(404);
echo "<h3>404 Not Found</h3><p>Halaman tidak ditemukan: " . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . "</p>";

