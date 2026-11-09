<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();

$id = $_GET['id'] ?? '';
$edit = $id !== '';
$data = ['judul' => '', 'pengarang' => '', 'id_kategori' => '', 'stok' => ''];
if ($edit) {
    $st = $pdo->prepare('SELECT * FROM buku WHERE id_buku = ?'); $st->execute([$id]);
    $row = $st->fetch();
    if (!$row) { flash('Buku tidak ditemukan.', 'err'); redirect('modules/buku/index.php'); }
    $data = $row;
}
$kategori = $pdo->query('SELECT * FROM kategori ORDER BY id_kategori')->fetchAll();
$validKat = array_column($kategori, 'id_kategori');
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['judul'] = trim($_POST['judul'] ?? '');
    $data['pengarang'] = trim($_POST['pengarang'] ?? '');
    $data['id_kategori'] = $_POST['id_kategori'] ?? '';
    $data['stok'] = trim($_POST['stok'] ?? '');
    if ($data['judul'] === '' || mb_strlen($data['judul']) > 100) $err['judul'] = 'Judul wajib diisi (maksimal 100 karakter).';
    if ($data['pengarang'] === '' || mb_strlen($data['pengarang']) > 100) $err['pengarang'] = 'Pengarang wajib diisi (maksimal 100 karakter).';
    if (!in_array($data['id_kategori'], $validKat, true)) $err['id_kategori'] = 'Pilih kategori yang valid.';
    if (!preg_match('/^\d{1,5}$/', $data['stok'])) $err['stok'] = 'Stok harus berupa angka 0 atau lebih.';
    if (!$err) {
        if ($edit) {
            $pdo->prepare('UPDATE buku SET judul=?, pengarang=?, id_kategori=?, stok=? WHERE id_buku=?')
                ->execute([$data['judul'], $data['pengarang'], $data['id_kategori'], (int)$data['stok'], $id]);
            flash('Data buku ' . $id . ' berhasil diperbarui.');
        } else {
            $new = next_id($pdo, 'buku', 'id_buku', 'BK');
            $pdo->prepare('INSERT INTO buku (id_buku, judul, pengarang, id_kategori, stok) VALUES (?,?,?,?,?)')
                ->execute([$new, $data['judul'], $data['pengarang'], $data['id_kategori'], (int)$data['stok']]);
            flash('Buku ' . $new . ' berhasil ditambahkan.');
        }
        redirect('modules/buku/index.php');
    }
}
render_header($edit ? 'Ubah Buku' : 'Tambah Buku', 'buku');
?>
<div class="head"><div><h1><?= $edit ? 'Ubah Buku ' . e($id) : 'Tambah Buku' ?></h1><p class="sub">Lengkapi data koleksi buku.</p></div></div>
<div class="card form-card">
<form method="post" action="<?= e(url('modules/buku/form.php' . ($edit ? '?id=' . rawurlencode($id) : ''))) ?>">
  <?= csrf_field() ?>
  <div class="field"><label for="book-title">Judul buku</label><input id="book-title" name="judul" value="<?= e($data['judul']) ?>" maxlength="100">
    <?php if (isset($err['judul'])): ?><div class="err-t"><?= e($err['judul']) ?></div><?php endif; ?></div>
  <div class="field"><label for="book-author">Pengarang</label><input id="book-author" name="pengarang" value="<?= e($data['pengarang']) ?>" maxlength="100">
    <?php if (isset($err['pengarang'])): ?><div class="err-t"><?= e($err['pengarang']) ?></div><?php endif; ?></div>
  <div class="row">
    <div class="field"><label for="book-category">Kategori</label>
      <select id="book-category" name="id_kategori"><option value="">- pilih kategori -</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= e($k['id_kategori']) ?>" <?= $data['id_kategori'] === $k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
        <?php endforeach; ?></select>
      <?php if (isset($err['id_kategori'])): ?><div class="err-t"><?= e($err['id_kategori']) ?></div><?php endif; ?></div>
    <div class="field"><label for="book-stock">Stok (eksemplar)</label><input id="book-stock" name="stok" value="<?= e($data['stok']) ?>" inputmode="numeric">
      <?php if (isset($err['stok'])): ?><div class="err-t"><?= e($err['stok']) ?></div><?php endif; ?></div>
  </div>
  <button class="btn">Simpan</button> <a class="btn ghost" href="<?= e(url('modules/buku/index.php')) ?>">Batal</a>
</form>
</div>
<?php render_footer();
