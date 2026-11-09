<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();

/* Hapus buku (hanya jika belum pernah dipinjam) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    csrf_check();
    $id = $_POST['id'] ?? '';
    $st = $pdo->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_buku = ?'); $st->execute([$id]);
    if ($st->fetchColumn() > 0) {
        flash('Buku tidak dapat dihapus karena sudah memiliki riwayat peminjaman. Ubah stok menjadi 0 jika buku tidak lagi tersedia.', 'err');
    } else {
        $pdo->prepare('DELETE FROM buku WHERE id_buku = ?')->execute([$id]);
        flash('Buku ' . $id . ' berhasil dihapus.');
    }
    redirect('modules/buku/index.php');
}

$q = trim($_GET['q'] ?? '');
$kat = $_GET['kategori'] ?? '';
$where = ' WHERE 1=1'; $params = [];
if ($q !== '') { $where .= ' AND (b.judul LIKE ? OR b.pengarang LIKE ? OR b.id_buku LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%"); }
if ($kat !== '') { $where .= ' AND b.id_kategori = ?'; $params[] = $kat; }

$pg = paginate($pdo, 'SELECT COUNT(*) FROM buku b' . $where, $params);
$st = $pdo->prepare('SELECT b.*, k.nama_kategori FROM buku b JOIN kategori k ON b.id_kategori=k.id_kategori'
    . $where . ' ORDER BY b.id_buku LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset']);
$st->execute($params); $rows = $st->fetchAll();
$kategori = $pdo->query('SELECT * FROM kategori ORDER BY id_kategori')->fetchAll();

render_header('Koleksi Buku', 'buku');
?>
<div class="head">
  <div><h1>Koleksi Buku</h1><p class="sub"><?= $pg['total'] ?> judul ditemukan</p></div>
  <a class="btn" href="<?= e(url('modules/buku/form.php')) ?>">Tambah Buku</a>
</div>
<div class="card">
  <form class="filter" method="get" action="<?= e(url('modules/buku/index.php')) ?>">
    <div class="grow"><label for="q-book">Cari buku</label><input id="q-book" name="q" value="<?= e($q) ?>" placeholder="Judul, pengarang, atau ID buku"></div>
    <div><label for="category-filter">Kategori</label>
      <select id="category-filter" name="kategori"><option value="">Semua kategori</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= e($k['id_kategori']) ?>" <?= $kat === $k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
        <?php endforeach; ?></select></div>
    <button class="btn">Cari</button>
    <a class="btn ghost" href="<?= e(url('modules/buku/index.php')) ?>">Reset</a>
  </form>
  <div class="collection-toolbar">
    <div class="category-chips" aria-label="Filter kategori">
      <a class="<?= $kat === '' ? 'selected' : '' ?>" href="<?= e(url('modules/buku/index.php' . ($q !== '' ? '?q=' . rawurlencode($q) : ''))) ?>">Semua</a>
      <?php foreach ($kategori as $k): ?>
        <a class="<?= $kat === $k['id_kategori'] ? 'selected' : '' ?>" href="<?= e(url('modules/buku/index.php?' . http_build_query(array_filter(['q' => $q, 'kategori' => $k['id_kategori']], static fn($v) => $v !== '')))) ?>"><?= e($k['nama_kategori']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="view-switch" role="group" aria-label="Tampilan koleksi">
      <button type="button" class="selected" data-view-select="table" aria-pressed="true">Tabel</button>
      <button type="button" data-view-select="gallery" aria-pressed="false">Galeri</button>
    </div>
  </div>
  <div class="collection-table" data-collection-view="table"><div class="table-scroll"><table>
    <tr><th>ID</th><th>Judul</th><th>Pengarang</th><th>Kategori</th><th class="r">Stok</th><th></th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['id_buku']) ?></td>
        <td><b><?= e($r['judul']) ?></b></td>
        <td><?= e($r['pengarang']) ?></td>
        <td><?= e($r['nama_kategori']) ?></td>
        <td class="r"><span class="badge <?= $r['stok'] == 0 ? 'b-red' : ($r['stok'] <= STOK_RENDAH ? 'b-amb' : 'b-ok') ?>"><?= (int)$r['stok'] ?></span></td>
        <td class="r"><div class="actions actions-end">
          <a class="btn ghost sm" href="<?= e(url('modules/buku/form.php?id=' . rawurlencode($r['id_buku']))) ?>">Ubah</a>
          <form method="post" action="<?= e(url('modules/buku/index.php')) ?>" data-confirm="Buku ini akan dihapus. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus buku" data-confirm-action="Hapus">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= e($r['id_buku']) ?>">
            <button class="btn danger sm">Hapus</button></form>
        </div></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6"><div class="empty-state"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8"/></svg><p>Tidak ada buku yang cocok.</p><a class="btn sm" href="<?= e(url('modules/buku/form.php')) ?>">Tambah buku</a></div></td></tr><?php endif; ?>
  </table></div></div>
  <div class="book-gallery" data-collection-view="gallery" hidden>
    <?php foreach ($rows as $r): ?>
      <article class="book-tile">
        <div class="book-cover"><span><?= e($r['nama_kategori']) ?></span><strong><?= e($r['judul']) ?></strong><i></i></div>
        <div class="book-tile-info"><small><?= e($r['id_buku']) ?> · <?= e($r['nama_kategori']) ?></small><h3><?= e($r['judul']) ?></h3><p><?= e($r['pengarang']) ?></p>
          <div class="book-tile-bottom"><span class="badge <?= $r['stok'] == 0 ? 'b-red' : ($r['stok'] <= STOK_RENDAH ? 'b-amb' : 'b-ok') ?>">Stok <?= (int)$r['stok'] ?></span>
            <div class="actions"><a class="btn ghost sm" href="<?= e(url('modules/buku/form.php?id=' . rawurlencode($r['id_buku']))) ?>">Ubah</a>
              <form method="post" action="<?= e(url('modules/buku/index.php')) ?>" data-confirm="Buku ini akan dihapus. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus buku" data-confirm-action="Hapus"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= e($r['id_buku']) ?>"><button class="btn danger sm">Hapus</button></form>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="empty-state"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8"/></svg><p>Tidak ada buku yang cocok.</p><a class="btn" href="<?= e(url('modules/buku/form.php')) ?>">Tambah buku pertama</a></div><?php endif; ?>
  </div>
  <?= pager($pg) ?>
</div>
<?php render_footer();
