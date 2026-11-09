<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'db_perpustakaan');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Di Vercel atau hosting root domain, otomatis menggunakan '/' jika BASE_URL tidak diatur
$defaultBaseUrl = (getenv('VERCEL') || getenv('VERCEL_ENV')) ? '/' : '/Perpustakaan/';
define('BASE_URL', rtrim(getenv('BASE_URL') ?: $defaultBaseUrl, '/') . '/');

define('APP_NAME', 'Sistem Informasi Perpustakaan');
define('LAMA_PINJAM_DEFAULT', 7);
define('MAX_PINJAM_AKTIF', 3);
define('STOK_RENDAH', 5);

define('PUBLIC_CONTACT', [
    'address' => 'Jl. Prof. Dr. Hamka, Ngaliyan, Semarang',
    'hours' => 'Pagi sampai sore',
    'email' => 'mohammadfauzilazim11@gmail.com',
    'phone' => '088975642070',
]);
