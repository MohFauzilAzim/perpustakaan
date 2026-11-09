<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();

$id = $_GET['id'] ?? '';
$edit = $id !== '';
$data = ['nama' => '', 'email' => '', 'no_telepon' => '', 'status_aktif' => 'Aktif'];
if ($edit) {
    $st = $pdo->prepare('SELECT * FROM anggota WHERE id_anggota = ?'); $st->execute([$id]);
    $row = $st->fetch();
    if (!$row) { flash('Anggota tidak ditemukan.', 'err'); redirect('modules/anggota/index.php'); }
    $data = $row;
}
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['nama', 'email', 'no_telepon'] as $f) $data[$f] = trim($_POST[$f] ?? '');
    $data['status_aktif'] = $_POST['status_aktif'] ?? '';
    if ($data['nama'] === '' || mb_strlen($data['nama']) > 100) $err['nama'] = 'Nama wajib diisi (maksimal 100 karakter).';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 100) $err['email'] = 'Format email tidak valid.';
    if (!preg_match('/^[0-9]{8,15}$/', $data['no_telepon'])) $err['no_telepon'] = 'No. telepon harus berupa angka 8-15 digit.';
    if (!in_array($data['status_aktif'], ['Aktif', 'Nonaktif'], true)) $err['status_aktif'] = 'Status tidak valid.';
    if (!$err) {
        if ($edit) {
            $pdo->prepare('UPDATE anggota SET nama=?, email=?, no_telepon=?, status_aktif=? WHERE id_anggota=?')
                ->execute([$data['nama'], $data['email'], $data['no_telepon'], $data['status_aktif'], $id]);
            flash('Data anggota ' . $id . ' berhasil diperbarui.');
        } else {
            $new = next_id($pdo, 'anggota', 'id_anggota', 'AG');
            $pdo->prepare('INSERT INTO anggota (id_anggota, nama, email, no_telepon, status_aktif) VALUES (?,?,?,?,?)')
                ->execute([$new, $data['nama'], $data['email'], $data['no_telepon'], $data['status_aktif']]);
            flash('Anggota ' . $new . ' berhasil ditambahkan.');
        }
        redirect('modules/anggota/index.php');
    }
}
render_header($edit ? 'Ubah Anggota' : 'Tambah Anggota', 'anggota');
?>
<div class="head"><div><h1><?= $edit ? 'Ubah Anggota ' . e($id) : 'Tambah Anggota' ?></h1><p class="sub">Data identitas dan kontak anggota perpustakaan.</p></div></div>
<div class="card form-card">
<form method="post" action="<?= e(url('modules/anggota/form.php' . ($edit ? '?id=' . rawurlencode($id) : ''))) ?>">
  <?= csrf_field() ?>
  <div class="field"><label for="member-name">Nama lengkap</label><input id="member-name" name="nama" value="<?= e($data['nama']) ?>" maxlength="100">
    <?php if (isset($err['nama'])): ?><div class="err-t"><?= e($err['nama']) ?></div><?php endif; ?></div>
  <div class="row">
    <div class="field"><label for="member-email">Email</label><input id="member-email" name="email" value="<?= e($data['email']) ?>" maxlength="100">
      <?php if (isset($err['email'])): ?><div class="err-t"><?= e($err['email']) ?></div><?php endif; ?></div>
    <div class="field"><label for="member-phone">No. telepon / WhatsApp</label><input id="member-phone" name="no_telepon" value="<?= e($data['no_telepon']) ?>" inputmode="numeric" placeholder="08xxxxxxxxxx">
      <?php if (isset($err['no_telepon'])): ?><div class="err-t"><?= e($err['no_telepon']) ?></div><?php endif; ?></div>
  </div>
  <div class="field field-short"><label for="member-status">Status keanggotaan</label>
    <select id="member-status" name="status_aktif"><?php foreach (['Aktif', 'Nonaktif'] as $s): ?>
      <option <?= $data['status_aktif'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
  <button class="btn">Simpan</button> <a class="btn ghost" href="<?= e(url('modules/anggota/index.php')) ?>">Batal</a>
</form>
</div>
<?php render_footer();
