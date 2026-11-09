<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();
$today = date('Y-m-d');

/* Proses pengembalian buku: status -> Dikembalikan, tgl_kembali = hari ini, stok +1 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'kembali') {
    csrf_check();
    $id = $_POST['id'] ?? '';
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("SELECT id_buku, tgl_kembali FROM peminjaman WHERE id_peminjaman = ? AND status = 'Dipinjam' FOR UPDATE");
        $st->execute([$id]); $p = $st->fetch();
        if (!$p) { $pdo->rollBack(); flash('Transaksi tidak ditemukan atau sudah dikembalikan.', 'err'); redirect('modules/peminjaman/index.php'); }
        $pdo->prepare("UPDATE peminjaman SET status='Dikembalikan', tgl_kembali=? WHERE id_peminjaman=?")->execute([$today, $id]);
        $pdo->prepare('UPDATE buku SET stok = stok + 1 WHERE id_buku = ?')->execute([$p['id_buku']]);
        $pdo->commit();
        $telat = hari_selisih($p['tgl_kembali'], $today);
        flash($telat > 0 ? "Buku dikembalikan (terlambat $telat hari). Stok bertambah 1." : 'Buku berhasil dikembalikan. Stok bertambah 1.', $telat > 0 ? 'info' : 'ok');
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('Terjadi kesalahan saat memproses pengembalian.', 'err');
    }
    redirect('modules/peminjaman/index.php');
}

$q = trim($_GET['q'] ?? '');
$sf = $_GET['status'] ?? '';
$where = ' WHERE 1=1'; $params = [];
if ($q !== '') { $where .= ' AND (a.nama LIKE ? OR b.judul LIKE ? OR p.id_peminjaman LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%"); }
if ($sf === 'Dipinjam' || $sf === 'Dikembalikan') { $where .= ' AND p.status = ?'; $params[] = $sf; }
if ($sf === 'Terlambat') { $where .= " AND p.status = 'Dipinjam' AND p.tgl_kembali < ?"; $params[] = $today; }

$join = ' FROM peminjaman p JOIN anggota a ON p.id_anggota=a.id_anggota JOIN buku b ON p.id_buku=b.id_buku JOIN petugas t ON p.id_petugas=t.id_petugas';
$pg = paginate($pdo, 'SELECT COUNT(*)' . $join . $where, $params);
$st = $pdo->prepare('SELECT p.*, a.nama, b.judul, t.nama_petugas' . $join . $where
    . ' ORDER BY p.tgl_pinjam DESC, p.id_peminjaman DESC LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset']);
$st->execute($params); $rows = $st->fetchAll();

render_header('Peminjaman', 'peminjaman');
?>
<div class="head">
  <div><h1>Peminjaman &amp; Pengembalian</h1><p class="sub"><?= $pg['total'] ?> transaksi ditemukan</p></div>
  <a class="btn" href="<?= e(url('modules/peminjaman/baru.php')) ?>">Tambah Peminjaman</a>
</div>
<div class="card">
  <form class="filter" method="get" action="<?= e(url('modules/peminjaman/index.php')) ?>">
    <div class="grow"><label for="q-loan">Cari transaksi</label><input id="q-loan" name="q" value="<?= e($q) ?>" placeholder="Nama anggota, judul buku, atau ID transaksi"></div>
    <div><label for="loan-status-filter">Status</label><select id="loan-status-filter" name="status"><option value="">Semua</option>
      <?php foreach (['Dipinjam', 'Terlambat', 'Dikembalikan'] as $s): ?><option <?= $sf === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <button class="btn">Cari</button><a class="btn ghost" href="<?= e(url('modules/peminjaman/index.php')) ?>">Reset</a>
  </form>
  <div class="table-scroll"><table>
    <tr><th>ID</th><th>Anggota</th><th>Buku</th><th>Tgl pinjam</th><th>Jatuh tempo / kembali</th><th>Status</th><th>Petugas</th><th></th></tr>
    <?php foreach ($rows as $r):
        $late = $r['status'] === 'Dipinjam' && $r['tgl_kembali'] < $today; ?>
      <tr>
        <td><?= e($r['id_peminjaman']) ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['judul']) ?></td>
        <td><?= e(tgl_indo($r['tgl_pinjam'])) ?></td><td class="<?= $late ? 'late-date' : '' ?>"><?= e(tgl_indo($r['tgl_kembali'])) ?></td>
        <td><?php if ($r['status'] === 'Dikembalikan'): ?><span class="badge b-ok">Dikembalikan</span>
            <?php elseif ($late): ?><span class="badge b-red">Terlambat <?= hari_selisih($r['tgl_kembali'], $today) ?> hr</span>
            <?php else: ?><span class="badge b-amb">Dipinjam</span><?php endif; ?></td>
        <td><?= e($r['nama_petugas']) ?></td>
        <td class="r"><?php if ($r['status'] === 'Dipinjam'): ?>
          <form method="post" action="<?= e(url('modules/peminjaman/index.php')) ?>" data-confirm="Buku akan ditandai sudah dikembalikan dan stok diperbarui." data-confirm-title="Proses pengembalian" data-confirm-action="Kembalikan">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="kembali"><input type="hidden" name="id" value="<?= e($r['id_peminjaman']) ?>">
            <button class="btn sm">Kembalikan</button></form><?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="8"><div class="empty-state"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v15H4zM8 3v4M16 3v4M4 9h16"/><path d="m8 14 2 2 5-5"/></svg><p>Tidak ada transaksi yang cocok.</p><a class="btn sm" href="<?= e(url('modules/peminjaman/baru.php')) ?>">Catat peminjaman</a></div></td></tr><?php endif; ?>
  </table></div>
  <?= pager($pg) ?>
</div>
<?php render_footer();
