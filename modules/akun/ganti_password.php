<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $lama = $_POST['lama'] ?? ''; $baru = $_POST['baru'] ?? ''; $ulang = $_POST['ulang'] ?? '';
    $st = $pdo->prepare('SELECT password_hash FROM pengguna WHERE id_pengguna = ?');
    $st->execute([$_SESSION['user']['id_pengguna']]);
    $hash = $st->fetchColumn();
    if (!$hash || !password_verify($lama, $hash)) $err['lama'] = 'Password lama salah.';
    if (strlen($baru) < 8) $err['baru'] = 'Password baru minimal 8 karakter.';
    if ($baru !== $ulang) $err['ulang'] = 'Konfirmasi password tidak sama.';
    if (!$err) {
        $pdo->prepare('UPDATE pengguna SET password_hash = ? WHERE id_pengguna = ?')
            ->execute([password_hash($baru, PASSWORD_DEFAULT), $_SESSION['user']['id_pengguna']]);
        flash('Password berhasil diganti.');
        redirect('dashboard.php');
    }
}
render_header('Ganti Password');
?>
<div class="head"><div><h1>Ganti Password</h1><p class="sub">Disarankan segera mengganti password awal.</p></div></div>
<div class="card form-card narrow">
<form method="post" action="<?= e(url('modules/akun/ganti_password.php')) ?>">
  <?= csrf_field() ?>
  <div class="field"><label for="current-password">Password lama</label><input id="current-password" type="password" name="lama" required>
    <?php if (isset($err['lama'])): ?><div class="err-t"><?= e($err['lama']) ?></div><?php endif; ?></div>
  <div class="field"><label for="new-password">Password baru</label><input id="new-password" type="password" name="baru" required>
    <?php if (isset($err['baru'])): ?><div class="err-t"><?= e($err['baru']) ?></div><?php endif; ?></div>
  <div class="field"><label for="confirm-password">Ulangi password baru</label><input id="confirm-password" type="password" name="ulang" required>
    <?php if (isset($err['ulang'])): ?><div class="err-t"><?= e($err['ulang']) ?></div><?php endif; ?></div>
  <button class="btn">Simpan</button> <a class="btn ghost" href="<?= e(url('dashboard.php')) ?>">Batal</a>
</form>
</div>
<?php render_footer();
