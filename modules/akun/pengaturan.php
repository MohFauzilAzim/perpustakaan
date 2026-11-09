<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();

$err_nama = '';
$err_pass = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';

    // 1. Ubah Nama Petugas
    if ($aksi === 'ubah_nama') {
        $nama = trim($_POST['nama_petugas'] ?? '');
        if ($nama === '') {
            $err_nama = 'Nama petugas tidak boleh kosong.';
        } elseif (mb_strlen($nama) < 3) {
            $err_nama = 'Nama petugas minimal 3 karakter.';
        } else {
            $st = $pdo->prepare('UPDATE petugas SET nama_petugas = ? WHERE id_petugas = ?');
            $st->execute([$nama, $_SESSION['user']['id_petugas']]);
            $_SESSION['user']['nama_petugas'] = $nama;
            flash('Nama profil petugas berhasil diperbarui.');
            redirect('modules/akun/pengaturan.php');
        }
    }

    // 2. Ganti Password
    if ($aksi === 'ganti_password') {
        $lama = $_POST['lama'] ?? '';
        $baru = $_POST['baru'] ?? '';
        $ulang = $_POST['ulang'] ?? '';

        $st = $pdo->prepare('SELECT password_hash FROM pengguna WHERE id_pengguna = ?');
        $st->execute([$_SESSION['user']['id_pengguna']]);
        $hash = $st->fetchColumn();

        if (!$hash || !password_verify($lama, $hash)) {
            $err_pass['lama'] = 'Password lama tidak sesuai.';
        }
        if (strlen($baru) < 8) {
            $err_pass['baru'] = 'Password baru minimal 8 karakter.';
        }
        if ($baru !== $ulang) {
            $err_pass['ulang'] = 'Konfirmasi password baru tidak cocok.';
        }

        if (!$err_pass) {
            $newHash = password_hash($baru, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE pengguna SET password_hash = ? WHERE id_pengguna = ?')
                ->execute([$newHash, $_SESSION['user']['id_pengguna']]);
            flash('Password berhasil diperbarui.');
            redirect('modules/akun/pengaturan.php');
        }
    }
}

render_header('Pengaturan Akun');
?>
<div class="head">
  <div>
    <h1>Pengaturan Akun</h1>
    <p class="sub">Kelola identitas nama petugas dan keamanan kata sandi akun Anda.</p>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; align-items: start; max-width: 1000px;">
  <!-- Bagian 1: Ubah Nama Petugas -->
  <div class="card form-card" style="margin: 0; max-width: none;">
    <h2 style="font-size: 1.15rem; margin-bottom: 8px;">Ubah Profil Petugas</h2>
    <p class="sub" style="margin-bottom: 20px;">Sesuaikan nama tampilan petugas perpustakaan.</p>

    <form method="post" action="<?= e(url('modules/akun/pengaturan.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="aksi" value="ubah_nama">

      <div class="field">
        <label for="username">Username</label>
        <input id="username" type="text" value="<?= e($_SESSION['user']['username']) ?>" disabled style="opacity: 0.7; cursor: not-allowed; background: var(--surface-2, #f5f5f5);">
      </div>

      <div class="field">
        <label for="jabatan">Jabatan</label>
        <input id="jabatan" type="text" value="<?= e($_SESSION['user']['jabatan']) ?>" disabled style="opacity: 0.7; cursor: not-allowed; background: var(--surface-2, #f5f5f5);">
      </div>

      <div class="field">
        <label for="nama_petugas">Nama Petugas</label>
        <input id="nama_petugas" type="text" name="nama_petugas" value="<?= e($_SESSION['user']['nama_petugas']) ?>" required>
        <?php if ($err_nama): ?>
          <div class="err-t"><?= e($err_nama) ?></div>
        <?php endif; ?>
      </div>

      <div style="margin-top: 24px;">
        <button class="btn" type="submit">Simpan Nama</button>
      </div>
    </form>
  </div>

  <!-- Bagian 2: Ganti Password -->
  <div class="card form-card" style="margin: 0; max-width: none;">
    <h2 style="font-size: 1.15rem; margin-bottom: 8px;">Ganti Password</h2>
    <p class="sub" style="margin-bottom: 20px;">Gunakan minimal 8 karakter kombinasi yang kuat.</p>

    <form method="post" action="<?= e(url('modules/akun/pengaturan.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="aksi" value="ganti_password">

      <div class="field">
        <label for="current-password">Password lama</label>
        <input id="current-password" type="password" name="lama" placeholder="Masukkan password saat ini" required>
        <?php if (isset($err_pass['lama'])): ?>
          <div class="err-t"><?= e($err_pass['lama']) ?></div>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="new-password">Password baru</label>
        <input id="new-password" type="password" name="baru" placeholder="Minimal 8 karakter" required>
        <?php if (isset($err_pass['baru'])): ?>
          <div class="err-t"><?= e($err_pass['baru']) ?></div>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="confirm-password">Ulangi password baru</label>
        <input id="confirm-password" type="password" name="ulang" placeholder="Ketik ulang password baru" required>
        <?php if (isset($err_pass['ulang'])): ?>
          <div class="err-t"><?= e($err_pass['ulang']) ?></div>
        <?php endif; ?>
      </div>

      <div style="margin-top: 24px;">
        <button class="btn" type="submit">Ganti Password</button>
        <a class="btn ghost" href="<?= e(url('dashboard.php')) ?>">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php render_footer();
