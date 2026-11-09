<?php
require __DIR__ . '/includes/bootstrap.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $st = $pdo->prepare('SELECT p.id_pengguna, p.username, p.password_hash, t.id_petugas, t.nama_petugas, t.jabatan
                         FROM pengguna p JOIN petugas t ON p.id_petugas = t.id_petugas WHERE p.username = ?');
    $st->execute([$username]);
    $row = $st->fetch();
    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        unset($row['password_hash']);
        $_SESSION['user'] = $row;
        redirect('dashboard.php');
    }
    usleep(400000);
    $error = 'Username atau password salah.';
}
render_header('Masuk Petugas');
?>
<div class="login-card">
  <section class="login-aside" aria-label="Tentang perpustakaan">
    <div class="login-pattern" aria-hidden="true"></div>
    <div class="login-brand-top" data-login-reveal>
      <span class="login-logo-wrap"><img class="login-logo" src="<?= e(url('assets/img/logo.png')) ?>" alt="Logo UIN Walisongo" width="54" height="54"></span>
      <span class="login-kicker">UIN WALISONGO SEMARANG</span>
    </div>
    <div class="login-intro" data-login-reveal>
      <h1>Sistem Informasi<br>Perpustakaan</h1>
      <span class="login-title-rule" aria-hidden="true"></span>
      <p class="login-description">Ruang teratur untuk merawat koleksi dan mendukung perjalanan ilmu.</p>
    </div>
    <div class="login-aside-bottom" data-login-reveal>
      <ul class="login-features">
        <li><span class="login-feature-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8M8 11h8"/></svg></span><span>Kelola koleksi buku dengan rapi</span></li>
        <li><span class="login-feature-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 5.2a3 3 0 0 1 0 5.6M18 14a5 5 0 0 1 3 4.6V20"/></svg></span><span>Layani anggota dengan lebih mudah</span></li>
        <li><span class="login-feature-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 5-6"/></svg></span><span>Pantau aktivitas dan laporan</span></li>
      </ul>
      <div class="login-footer">SISTEM INFORMASI MANAJEMEN - TUGAS PERSONAL 3</div>
    </div>
  </section>
  <section class="login-form-panel">
    <div class="login-form-inner">
      <span class="form-kicker">AKSES PETUGAS</span>
      <h2>Selamat datang</h2>
      <p class="login-subtitle">Masuk untuk melanjutkan ke ruang kerja perpustakaan.</p>
      <?php if ($error): ?><div class="login-error" role="alert" aria-live="assertive"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 22 21H2L12 3Z"/><path d="M12 9v5m0 3h.01"/></svg><span><?= e($error) ?></span></div><?php endif; ?>
      <form class="login-form<?= $error ? ' has-error' : '' ?>" method="post" action="<?= e(url('login.php')) ?>" data-login-form data-login-error="<?= $error ? 'true' : 'false' ?>">
        <?= csrf_field() ?>
        <div class="field">
          <label for="username">Username</label>
          <div class="input-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
            <input id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" autocapitalize="none" spellcheck="false" aria-invalid="<?= $error ? 'true' : 'false' ?>" <?= $error ? '' : 'autofocus' ?> required></div>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-icon has-action"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
            <input id="password" name="password" type="password" autocomplete="current-password" autocapitalize="none" spellcheck="false" aria-invalid="<?= $error ? 'true' : 'false' ?>" <?= $error ? 'autofocus' : '' ?> required>
            <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan password" aria-pressed="false">Tampilkan</button></div>
          <p class="caps-lock-warning" data-caps-lock-warning role="status" aria-live="polite" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 22 21H2L12 3Z"/><path d="M12 9v5m0 3h.01"/></svg>Caps Lock aktif</p>
        </div>
        <button class="btn login-submit" type="submit" data-login-submit><span data-login-submit-label>Masuk</span><span class="login-spinner" data-login-spinner aria-hidden="true" hidden></span><svg data-login-arrow viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
      </form>
      <p class="login-help">Lupa password? Hubungi kepala perpustakaan.</p>
    </div>
    <a class="login-home-link" href="<?= e(url('index.php')) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>Kembali ke Beranda</a>
  </section>
</div>
<?php render_footer();
