<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();
$today = date('Y-m-d');

$anggota = $pdo->query("SELECT id_anggota, nama FROM anggota WHERE status_aktif='Aktif' ORDER BY nama")->fetchAll();
$buku = $pdo->query('SELECT id_buku, judul, stok FROM buku WHERE stok > 0 ORDER BY judul')->fetchAll();
$d = ['id_anggota' => '', 'id_buku' => '', 'tgl_pinjam' => $today, 'lama' => (string)LAMA_PINJAM_DEFAULT];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($d as $k => $_) $d[$k] = trim($_POST[$k] ?? '');
    if (!in_array($d['lama'], ['3', '7', '14'], true)) $err['lama'] = 'Lama pinjam tidak valid.';
    $dt = DateTime::createFromFormat('Y-m-d', $d['tgl_pinjam']);
    if (!$dt || $dt->format('Y-m-d') !== $d['tgl_pinjam']) $err['tgl_pinjam'] = 'Tanggal tidak valid.';
    elseif ($d['tgl_pinjam'] > $today) $err['tgl_pinjam'] = 'Tanggal pinjam tidak boleh di masa depan.';
    if ($d['id_anggota'] === '') $err['id_anggota'] = 'Pilih anggota.';
    if ($d['id_buku'] === '') $err['id_buku'] = 'Pilih buku.';

    if (!$err) {
        try {
            $pdo->beginTransaction();
            $a = $pdo->prepare("SELECT status_aktif FROM anggota WHERE id_anggota=?"); $a->execute([$d['id_anggota']]); $a = $a->fetch();
            $b = $pdo->prepare('SELECT stok, judul FROM buku WHERE id_buku=? FOR UPDATE'); $b->execute([$d['id_buku']]); $b = $b->fetch();
            $c = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_anggota=? AND status='Dipinjam'"); $c->execute([$d['id_anggota']]); $aktif = (int)$c->fetchColumn();
            $s2 = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_anggota=? AND id_buku=? AND status='Dipinjam'"); $s2->execute([$d['id_anggota'], $d['id_buku']]); $sama = (int)$s2->fetchColumn();

            if (!$a || $a['status_aktif'] !== 'Aktif') $err['id_anggota'] = 'Anggota tidak ditemukan atau berstatus Nonaktif.';
            elseif ($aktif >= MAX_PINJAM_AKTIF) $err['id_anggota'] = 'Anggota sudah mencapai batas ' . MAX_PINJAM_AKTIF . ' buku yang sedang dipinjam.';
            elseif ($sama > 0) $err['id_buku'] = 'Anggota ini masih meminjam buku yang sama.';
            if (!$b || (int)$b['stok'] < 1) $err['id_buku'] = 'Stok buku habis.';

            if ($err) { $pdo->rollBack(); }
            else {
                $jatuhTempo = date('Y-m-d', strtotime($d['tgl_pinjam'] . ' +' . (int)$d['lama'] . ' days'));
                $new = next_id($pdo, 'peminjaman', 'id_peminjaman', 'PJ');
                $pdo->prepare("INSERT INTO peminjaman (id_peminjaman,id_anggota,id_buku,id_petugas,tgl_pinjam,tgl_kembali,status) VALUES (?,?,?,?,?,?, 'Dipinjam')")
                    ->execute([$new, $d['id_anggota'], $d['id_buku'], $_SESSION['user']['id_petugas'], $d['tgl_pinjam'], $jatuhTempo]);
                $pdo->prepare('UPDATE buku SET stok = stok - 1 WHERE id_buku = ?')->execute([$d['id_buku']]);
                $pdo->commit();
                flash("Peminjaman $new berhasil dicatat. Jatuh tempo: " . tgl_indo($jatuhTempo) . '. Stok buku berkurang 1.');
                redirect('modules/peminjaman/index.php');
            }
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $err['umum'] = 'Terjadi kesalahan saat menyimpan transaksi.';
        }
    }
}
render_header('Peminjaman Baru', 'peminjaman');
?>
<div class="head"><div><h1>Peminjaman Baru</h1><p class="sub">Petugas: <?= e($_SESSION['user']['nama_petugas']) ?> &middot; stok buku otomatis berkurang saat transaksi disimpan.</p></div></div>
<div class="loan-layout">
<div class="card form-card loan-form-card">
<?php if (isset($err['umum'])): ?><div class="alert err"><?= e($err['umum']) ?></div><?php endif; ?>
<form method="post" action="<?= e(url('modules/peminjaman/baru.php')) ?>" data-loan-form>
  <?= csrf_field() ?>
  <div class="row">
    <div class="field"><label for="loan-member">Anggota (aktif)</label>
      <select id="loan-member" name="id_anggota"><option value="">- pilih anggota -</option>
        <?php foreach ($anggota as $a): ?><option value="<?= e($a['id_anggota']) ?>" <?= $d['id_anggota'] === $a['id_anggota'] ? 'selected' : '' ?>><?= e($a['id_anggota'] . ' - ' . $a['nama']) ?></option><?php endforeach; ?></select>
      <?php if (isset($err['id_anggota'])): ?><div class="err-t"><?= e($err['id_anggota']) ?></div><?php endif; ?></div>
    <div class="field"><label for="loan-book">Buku (stok tersedia)</label>
      <select id="loan-book" name="id_buku"><option value="">- pilih buku -</option>
        <?php foreach ($buku as $b): ?><option value="<?= e($b['id_buku']) ?>" <?= $d['id_buku'] === $b['id_buku'] ? 'selected' : '' ?>><?= e($b['judul'] . ' (stok ' . $b['stok'] . ')') ?></option><?php endforeach; ?></select>
      <?php if (isset($err['id_buku'])): ?><div class="err-t"><?= e($err['id_buku']) ?></div><?php endif; ?></div>
  </div>
  <div class="row">
    <div class="field"><label for="loan-date">Tanggal pinjam</label><input id="loan-date" type="date" name="tgl_pinjam" value="<?= e($d['tgl_pinjam']) ?>" max="<?= e($today) ?>">
      <?php if (isset($err['tgl_pinjam'])): ?><div class="err-t"><?= e($err['tgl_pinjam']) ?></div><?php endif; ?></div>
    <div class="field"><label for="loan-duration">Lama pinjam</label>
      <select id="loan-duration" name="lama"><?php foreach (['3' => '3 hari', '7' => '7 hari', '14' => '14 hari'] as $v => $l): ?><option value="<?= $v ?>" <?= $d['lama'] === (string)$v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
      <?php if (isset($err['lama'])): ?><div class="err-t"><?= e($err['lama']) ?></div><?php endif; ?></div>
  </div>
  <div class="note">Aturan: maksimal <?= MAX_PINJAM_AKTIF ?> buku aktif per anggota, anggota harus berstatus Aktif, dan stok buku harus tersedia.</div>
  <div class="form-actions"><button class="btn" type="submit">Simpan Peminjaman</button><a class="btn ghost" href="<?= e(url('modules/peminjaman/index.php')) ?>">Batal</a></div>
</form>
</div>
<aside class="card loan-summary" aria-live="polite">
  <span class="eyebrow">RINGKASAN TRANSAKSI</span><h2>Rincian peminjaman</h2>
  <div class="summary-book-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8M8 11h8"/></svg></div>
  <dl>
    <div><dt>Anggota</dt><dd data-summary-member>Belum dipilih</dd></div>
    <div><dt>Buku</dt><dd data-summary-book>Belum dipilih</dd></div>
    <div><dt>Stok tersedia</dt><dd data-summary-stock>—</dd></div>
    <div><dt>Jatuh tempo</dt><dd data-summary-due>—</dd></div>
  </dl>
  <p class="hint">Tanggal jatuh tempo diperbarui otomatis sesuai tanggal dan lama pinjam.</p>
</aside>
</div>
<?php render_footer();
