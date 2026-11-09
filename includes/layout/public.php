<?php
function render_public_header(
    string $title = 'Beranda - Perpustakaan UIN Walisongo',
    string $description = 'Sistem Informasi Perpustakaan UIN Walisongo Semarang. Jelajahi layanan dan koleksi perpustakaan.'
): void
{
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="icon" type="image/png" href="<?= e(url('assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/base.css?v=' . filemtime(__DIR__ . '/../../assets/css/base.css'))) ?>">
<link rel="stylesheet" href="<?= e(url('assets/css/public.css?v=' . filemtime(__DIR__ . '/../../assets/css/public.css'))) ?>">
</head>
<body class="public-site">
    <?php
}

function render_public_navbar(string $page = 'beranda'): void
{
    $loggedIn = !empty($_SESSION['user']);
    $landingUrl = static function (string $section) use ($page): string {
        return url(($page === 'beranda' ? '' : 'index.php') . '#' . $section);
    };
    ?>
<a class="public-skip-link" href="#main-content">Lewati ke konten utama</a>
<header class="public-header" data-public-header>
  <div class="public-reading-progress" data-reading-progress aria-hidden="true"></div>
  <div class="public-nav-inner">
    <a class="public-brand" href="<?= e(url('index.php#beranda')) ?>" aria-label="Perpustakaan UIN Walisongo, Beranda">
      <img class="public-brand-logo" src="<?= e(url('assets/img/logo.png')) ?>" alt="Logo UIN Walisongo" width="46" height="46">
      <span class="public-brand-divider" aria-hidden="true"></span>
      <span class="public-brand-copy">
        <span class="public-brand-name">Perpustakaan</span>
        <span class="public-brand-campus">UIN WALISONGO SEMARANG</span>
      </span>
    </a>
    <nav class="public-navigation desktop-navigation" aria-label="Navigasi utama">
      <a href="<?= e($landingUrl('beranda')) ?>" data-nav-section="beranda">Beranda</a>
      <a href="<?= e($landingUrl('layanan')) ?>" data-nav-section="layanan">Layanan</a>
      <a href="<?= e($landingUrl('alur')) ?>" data-nav-section="alur">Alur Peminjaman</a>
      <a href="<?= e($landingUrl('katalog')) ?>" data-nav-section="katalog" <?= $page === 'katalog' ? 'class="is-active" aria-current="page"' : '' ?>>Katalog</a>
      <a href="<?= e($landingUrl('kontak')) ?>" data-nav-section="kontak">Kontak</a>
    </nav>
    <button class="public-menu-toggle" type="button" title="Menu" aria-expanded="false" aria-controls="public-menu-panel" data-public-menu aria-label="Buka menu">
      <span class="public-menu-icon" aria-hidden="true"><span></span><span></span><span></span></span>
      <?php if ($loggedIn): ?><span class="public-session-indicator" aria-hidden="true"></span><?php endif; ?>
    </button>
  </div>
  <div class="public-menu-panel" id="public-menu-panel" data-public-menu-panel aria-hidden="true" inert>
    <nav class="public-navigation mobile-navigation" aria-label="Navigasi utama">
      <a href="<?= e($landingUrl('beranda')) ?>" data-nav-section="beranda">Beranda</a>
      <a href="<?= e($landingUrl('layanan')) ?>" data-nav-section="layanan">Layanan</a>
      <a href="<?= e($landingUrl('alur')) ?>" data-nav-section="alur">Alur Peminjaman</a>
      <a href="<?= e($landingUrl('katalog')) ?>" data-nav-section="katalog" <?= $page === 'katalog' ? 'class="is-active" aria-current="page"' : '' ?>>Katalog</a>
      <a href="<?= e($landingUrl('kontak')) ?>" data-nav-section="kontak">Kontak</a>
    </nav>
    <div class="public-menu-actions">
      <span class="public-menu-label">AKSES PETUGAS</span>
      <?php if ($loggedIn): ?>
        <a class="public-menu-item" href="<?= e(url('dashboard.php')) ?>">
          <span class="public-menu-icon-circle"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V4h11v16M15 12h5m-3-3 3 3-3 3M7 8h3m-3 4h3"/></svg></span>
          <span class="public-menu-item-copy"><span class="public-menu-item-title">Buka Dashboard</span></span>
        </a>
        <form method="post" action="<?= e(url('logout.php')) ?>">
          <?= csrf_field() ?>
          <button class="public-menu-item" type="submit">
            <span class="public-menu-icon-circle"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H4v16h6M14 8l4 4-4 4m4-4H9"/></svg></span>
            <span class="public-menu-item-copy"><span class="public-menu-item-title">Keluar</span></span>
          </button>
        </form>
      <?php else: ?>
        <a class="public-menu-item" href="<?= e(url('login.php')) ?>">
          <span class="public-menu-icon-circle"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="15" r="3"/><path d="m10.1 12.9 8-8 3 3-2 2-1.5-1.5-2 2 1.5 1.5-2 2-2-2"/></svg></span>
          <span class="public-menu-item-copy">
            <span class="public-menu-item-title">Masuk Petugas</span>
            <span class="public-menu-item-description">Masuk untuk mengelola perpustakaan</span>
          </span>
        </a>
      <?php endif; ?>
    </div>
  </div>
  <span class="public-menu-caret" aria-hidden="true"></span>
</header>
    <?php
}

function render_public_footer(): void
{
    ?>
<footer class="public-footer">
  <div class="public-footer-inner">
    <a class="public-brand footer-brand" href="<?= e(url('index.php#beranda')) ?>">
      <span class="public-brand-mark"><img src="<?= e(url('assets/img/logo.png')) ?>" alt="" width="36" height="36"></span>
      <span>Perpustakaan UIN Walisongo</span>
    </a>
    <p>Sistem Informasi Manajemen - Tugas Personal 3</p>
    <nav class="public-footer-links" aria-label="Tautan cepat">
      <a href="<?= e(url('index.php#layanan')) ?>">Layanan</a>
      <a href="<?= e(url('index.php#alur')) ?>">Alur peminjaman</a>
      <a href="<?= e(url('katalog.php')) ?>">Katalog</a>
      <a href="<?= e(url('index.php#kontak')) ?>">Kontak</a>
    </nav>
  </div>
</footer>
<script src="<?= e(url('assets/js/public.js?v=' . filemtime(__DIR__ . '/../../assets/js/public.js'))) ?>" defer></script>
</body>
</html>
    <?php
}
