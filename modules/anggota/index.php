<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    csrf_check();
    $id = $_POST['id'] ?? '';
    $st = $pdo->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ?'); $st->execute([$id]);
    if ($st->fetchColumn() > 0) {
        flash('Anggota tidak dapat dihapus karena memiliki riwayat peminjaman. Ubah statusnya menjadi Nonaktif.', 'err');
    } else {
        $pdo->prepare('DELETE FROM anggota WHERE id_anggota = ?')->execute([$id]);
        flash('Anggota ' . $id . ' berhasil dihapus.');
    }
    redirect('modules/anggota/index.php');
}

$q = trim($_GET['q'] ?? '');
$stt = $_GET['status'] ?? '';
$where = ' WHERE 1=1'; $params = [];
if ($q !== '') { $where .= ' AND (a.nama LIKE ? OR a.email LIKE ? OR a.id_anggota LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%"); }
if (in_array($stt, ['Aktif', 'Nonaktif'], true)) { $where .= ' AND a.status_aktif = ?'; $params[] = $stt; }

$pg = paginate($pdo, 'SELECT COUNT(*) FROM anggota a' . $where, $params);
$st = $pdo->prepare('SELECT a.*, (SELECT COUNT(*) FROM peminjaman p WHERE p.id_anggota=a.id_anggota AND p.status="Dipinjam") AS aktif
    FROM anggota a' . $where . ' ORDER BY a.id_anggota LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset']);
$st->execute($params); $rows = $st->fetchAll();

render_header('Anggota', 'anggota');
?>
<div class="head">
  <div><h1>Anggota</h1><p class="sub"><?= $pg['total'] ?> anggota ditemukan</p></div>
  <a class="btn" href="<?= e(url('modules/anggota/form.php')) ?>">Tambah Anggota</a>
</div>
<div class="card">
  <form class="filter" method="get" action="<?= e(url('modules/anggota/index.php')) ?>">
    <div class="grow"><label for="q-member">Cari anggota</label><input id="q-member" name="q" value="<?= e($q) ?>" placeholder="Nama, email, atau ID anggota"></div>
    <div><label for="member-status-filter">Status</label><select id="member-status-filter" name="status"><option value="">Semua</option>
      <option <?= $stt === 'Aktif' ? 'selected' : '' ?>>Aktif</option><option <?= $stt === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select></div>
    <button class="btn">Cari</button><a class="btn ghost" href="<?= e(url('modules/anggota/index.php')) ?>">Reset</a>
  </form>
  <div class="table-scroll"><table>
    <tr><th>ID</th><th>Nama</th><th>Email</th><th>No. telepon</th><th>Status</th><th class="r">Pinjaman aktif</th><th></th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['id_anggota']) ?></td><td><span class="member-name"><span class="member-avatar"><?= e(mb_strtoupper(mb_substr($r['nama'], 0, 1))) ?></span><b><?= e($r['nama']) ?></b></span></td><td><?= e($r['email']) ?></td><td><?= e($r['no_telepon']) ?></td>
        <td><span class="badge <?= $r['status_aktif'] === 'Aktif' ? 'b-ok' : 'b-red' ?>"><?= e($r['status_aktif']) ?></span></td>
        <td class="quota-cell"><span><?= (int)$r['aktif'] ?> / <?= MAX_PINJAM_AKTIF ?></span><span class="quota-track" aria-label="<?= (int)$r['aktif'] ?> dari <?= MAX_PINJAM_AKTIF ?> pinjaman aktif"><span data-quota="<?= min(100, (int)$r['aktif'] / MAX_PINJAM_AKTIF * 100) ?>"></span></span></td>
        <td class="r"><div class="actions actions-end">
          <a class="btn ghost sm" href="<?= e(url('modules/anggota/form.php?id=' . rawurlencode($r['id_anggota']))) ?>">Ubah</a>
          <form method="post" action="<?= e(url('modules/anggota/index.php')) ?>" data-confirm="Anggota ini akan dihapus. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus anggota" data-confirm-action="Hapus">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= e($r['id_anggota']) ?>">
            <button class="btn danger sm">Hapus</button></form>
        </div></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7"><div class="empty-state"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 5.2a3 3 0 0 1 0 5.6M18 14a5 5 0 0 1 3 4.6V20"/></svg><p>Tidak ada anggota yang cocok.</p><a class="btn sm" href="<?= e(url('modules/anggota/form.php')) ?>">Tambah anggota</a></div></td></tr><?php endif; ?>
  </table></div>
  <?= pager($pg) ?>
</div>
<?php render_footer();
