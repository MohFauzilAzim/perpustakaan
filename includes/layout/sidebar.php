<?php
function render_sidebar(string $active): void
{
    $menu = [
        'dashboard' => ['Dashboard', 'dashboard.php', '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>'],
        'buku' => ['Koleksi Buku', 'modules/buku/index.php', '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8M8 11h8"/>'],
        'anggota' => ['Anggota', 'modules/anggota/index.php', '<circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 5.2a3 3 0 0 1 0 5.6M18 14a5 5 0 0 1 3 4.6V20"/>'],
        'peminjaman' => ['Peminjaman', 'modules/peminjaman/index.php', '<path d="M4 5h16v15H4zM8 3v4M16 3v4M4 9h16"/><path d="m8 14 2 2 5-5"/>'],
        'laporan' => ['Laporan', 'modules/laporan/index.php', '<path d="M5 3h10l4 4v14H5zM15 3v5h4M8 12h8M8 16h8"/>'],
    ];
    ?>
<nav class="menu" id="main-menu" aria-label="Navigasi utama">
  <?php foreach ($menu as $key => [$label, $path, $icon]): ?>
    <a href="<?= e(url($path)) ?>" class="<?= $active === $key ? 'on' : '' ?>" <?= $active === $key ? 'aria-current="page"' : '' ?>>
      <svg viewBox="0 0 24 24" aria-hidden="true"><?= $icon ?></svg><span><?= e($label) ?></span>
    </a>
  <?php endforeach; ?>
</nav>
<div class="sidebar-account">
  <span class="account-label">PETUGAS</span>
  <strong><?= e($_SESSION['user']['nama_petugas']) ?></strong>
  <small><?= e($_SESSION['user']['jabatan']) ?></small>
  <a href="<?= e(url('modules/akun/pengaturan.php')) ?>">Pengaturan</a>
  <form method="post" action="<?= e(url('logout.php')) ?>">
    <?= csrf_field() ?>
    <button type="submit">Keluar</button>
  </form>
</div>
<?php
}
