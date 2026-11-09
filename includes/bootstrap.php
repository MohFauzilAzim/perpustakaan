<?php
// Sembunyikan notifikasi deprecated agar tidak merusak headers HTTP
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

try {
    $dsn = 'mysql:host=' . DB_HOST;
    if (defined('DB_PORT') && DB_PORT) {
        $dsn .= ';port=' . DB_PORT;
    }
    $dsn .= ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Dukungan atribut SSL untuk PHP 8.5+ dan versi sebelumnya
    $sslCaAttr = defined('Pdo\Mysql::ATTR_SSL_CA')
        ? constant('Pdo\Mysql::ATTR_SSL_CA')
        : (defined('PDO::MYSQL_ATTR_SSL_CA') ? PDO::MYSQL_ATTR_SSL_CA : 1002);

    $sslVerifyAttr = defined('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
        ? constant('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
        : (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT') ? PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT : 1014);

    // Aktifkan koneksi SSL/TLS (wajib untuk TiDB Cloud / port 4000)
    $isTidb = (defined('DB_HOST') && strpos(DB_HOST, 'tidbcloud.com') !== false);
    $isPort4000 = (defined('DB_PORT') && (string)DB_PORT === '4000');
    $isSslEnv = in_array(strtolower((string)getenv('DB_SSL')), ['1', 'true', 'yes'], true);

    if ($isTidb || $isPort4000 || $isSslEnv) {
        $caPath = realpath(__DIR__ . '/../config/ca.pem');
        if (!$caPath || !file_exists($caPath)) {
            $caPath = __DIR__ . '/../config/ca.pem';
        }
        $pdoOptions[$sslCaAttr] = $caPath;
        $pdoOptions[$sslVerifyAttr] = false;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
} catch (PDOException $ex) {
    http_response_code(500);
    $errMsg = htmlspecialchars($ex->getMessage());
    exit('<h3>Koneksi database gagal.</h3><p>Periksa pengaturan pada <code>config/config.php</code> atau Environment Variables hosting Anda.</p><p><small style="color:red;">Error: ' . $errMsg . '</small></p>');
}

require_once __DIR__ . '/layout/sidebar.php';
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/layout/footer.php';
