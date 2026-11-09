<?php
function render_header(string $title, string $active = ''): void
{
    $u = $_SESSION['user'] ?? null;
    $f = flash();
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - Perpustakaan UIN Walisongo</title>
<link rel="icon" type="image/png" href="<?= e(url('assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/base.css?v=' . filemtime(__DIR__ . '/../../assets/css/base.css'))) ?>">
<link rel="stylesheet" href="<?= e(url('assets/css/layout.css?v=' . filemtime(__DIR__ . '/../../assets/css/layout.css'))) ?>">
<link rel="stylesheet" href="<?= e(url('assets/css/components.css?v=' . filemtime(__DIR__ . '/../../assets/css/components.css'))) ?>">
</head>
<body>
<?php if ($u): ?>
<div class="app-shell">
  <aside class="sidebar" id="app-sidebar">
    <a class="brand" href="<?= e(url('dashboard.php')) ?>">
      <span class="brand-mark"><img src="<?= e(url('assets/img/logo.png')) ?>" alt="Logo UIN Walisongo" width="44" height="44"></span>
      <span><b>Perpustakaan</b><small>UIN Walisongo Semarang</small></span>
    </a>
    <?php render_sidebar($active); ?>
  </aside>
  <div class="app-main">
    <header class="topbar">
      <div class="topbar-inner">
        <div class="topbar-context"><span class="eyebrow">SISTEM INFORMASI PERPUSTAKAAN</span><strong><?= e($title) ?></strong></div>
        <a class="brand-mobile" href="<?= e(url('dashboard.php')) ?>">
          <span class="brand-mark small"><img src="<?= e(url('assets/img/logo.png')) ?>" alt="Logo UIN Walisongo" width="30" height="30"></span>
          <span>Sistem Informasi Perpustakaan</span>
        </a>
        <div class="topbar-tools">
          <time class="today-date" datetime="<?= e(date('Y-m-d')) ?>"><?= e(tgl_indo(date('Y-m-d'))) ?></time>
          <button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" title="Mode gelap">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15.1Z"/></svg>
          </button>
        </div>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="app-sidebar" data-menu-toggle aria-label="Buka menu">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <div class="user">
          <span class="user-avatar"><?= e(mb_strtoupper(mb_substr($u['nama_petugas'], 0, 1))) ?></span>
          <span><?= e($u['nama_petugas']) ?><small><?= e($u['jabatan']) ?></small></span>
          <a class="btn ghost sm" href="<?= e(url('modules/akun/pengaturan.php')) ?>">Pengaturan</a>
          <form method="post" action="<?= e(url('logout.php')) ?>">
            <?= csrf_field() ?>
            <button class="btn ghost sm" type="submit">Keluar</button>
          </form>
        </div>
      </div>
    </header>
    <main class="content">
<?php else: ?>
<main class="login-page">
<?php endif; ?>
<?php if ($f): ?><div class="alert <?= e($f['t']) ?>" role="status"><?= e($f['m']) ?></div><?php endif; ?>
<?php
}
