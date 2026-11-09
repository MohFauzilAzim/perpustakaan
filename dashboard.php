<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$today = date('Y-m-d');

$s = [];
$s['judul']   = (int)$pdo->query('SELECT COUNT(*) FROM buku')->fetchColumn();
$s['stok']    = (int)$pdo->query('SELECT COALESCE(SUM(stok),0) FROM buku')->fetchColumn();
$s['anggota'] = (int)$pdo->query("SELECT COUNT(*) FROM anggota WHERE status_aktif='Aktif'")->fetchColumn();
$s['pinjam']  = (int)$pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status='Dipinjam'")->fetchColumn();
$st = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE status='Dipinjam' AND tgl_kembali < ?");
$st->execute([$today]); $s['telat'] = (int)$st->fetchColumn();

$kat = $pdo->query('SELECT k.nama_kategori, COUNT(b.id_buku) AS judul, COALESCE(SUM(b.stok),0) AS stok
                    FROM kategori k LEFT JOIN buku b ON k.id_kategori=b.id_kategori
                    GROUP BY k.id_kategori, k.nama_kategori ORDER BY k.id_kategori')->fetchAll();
$maxStok = max(array_merge([1], array_map(fn($r) => (int)$r["stok"], $kat)));

$low = $pdo->prepare('SELECT b.id_buku, b.judul, b.stok FROM buku b WHERE b.stok <= ? ORDER BY b.stok');
$low->execute([STOK_RENDAH]); $low = $low->fetchAll();

$late = $pdo->prepare("SELECT p.id_peminjaman, a.nama, b.judul, p.tgl_kembali FROM peminjaman p
        JOIN anggota a ON p.id_anggota=a.id_anggota JOIN buku b ON p.id_buku=b.id_buku
        WHERE p.status='Dipinjam' AND p.tgl_kembali < ? ORDER BY p.tgl_kembali LIMIT 5");
$late->execute([$today]); $late = $late->fetchAll();

$recent = $pdo->query('SELECT p.id_peminjaman, a.nama, b.judul, p.tgl_pinjam, p.status FROM peminjaman p
        JOIN anggota a ON p.id_anggota=a.id_anggota JOIN buku b ON p.id_buku=b.id_buku
        ORDER BY p.tgl_pinjam DESC, p.id_peminjaman DESC LIMIT 5')->fetchAll();
$recentStatus = ['Dipinjam' => 0, 'Dikembalikan' => 0];
foreach ($recent as $transaction) {
    if (isset($recentStatus[$transaction['status']])) $recentStatus[$transaction['status']]++;
}
$donutCircumference = 119.38;
$recentActiveArc = round($donutCircumference * $recentStatus['Dipinjam'] / max(1, count($recent)), 2);
$recentReturnedArc = round($donutCircumference * $recentStatus['Dikembalikan'] / max(1, count($recent)), 2);
$recentDonutGap = round($donutCircumference - $recentActiveArc, 2);

render_header('Dashboard', 'dashboard');
$hour = (int)date('G');
$greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
?>
<section class="welcome-banner">
  <div class="welcome-copy">
    <span class="eyebrow">RINGKASAN HARIAN · <?= e(tgl_indo($today)) ?></span>
    <h1><?= e($greeting) ?>, <?= e($_SESSION['user']['nama_petugas']) ?></h1>
    <p><?= $s['telat'] > 0 ? e($s['telat'] . ' peminjaman terlambat perlu ditindaklanjuti hari ini.') : 'Semua aktivitas perpustakaan dalam pantauan. Mari lanjutkan pelayanan hari ini.' ?></p>
    <a class="btn banner-action" href="<?= e(url('modules/peminjaman/baru.php')) ?>">Catat peminjaman <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
  </div>
  <div class="banner-index" aria-hidden="true"><span>Ruang</span><strong>01</strong><i></i></div>
</section>

<div class="grid g5 stat-grid">
  <a class="stat" href="<?= e(url('modules/buku/index.php')) ?>"><span class="stat-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8"/></svg></span><span class="n" data-count="<?= $s['judul'] ?>">0</span><span class="l">Judul buku</span><span class="stat-foot">Lihat koleksi <b>↗</b></span></a>
  <a class="stat gold" href="<?= e(url('modules/buku/index.php')) ?>"><span class="stat-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 5-6"/></svg></span><span class="n" data-count="<?= $s['stok'] ?>">0</span><span class="l">Total stok tersedia</span><span class="stat-foot">Eksemplar tersedia <b>↗</b></span></a>
  <a class="stat" href="<?= e(url('modules/anggota/index.php')) ?>"><span class="stat-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 5.2a3 3 0 0 1 0 5.6M18 14a5 5 0 0 1 3 4.6V20"/></svg></span><span class="n" data-count="<?= $s['anggota'] ?>">0</span><span class="l">Anggota aktif</span><span class="stat-foot">Lihat anggota <b>↗</b></span></a>
  <a class="stat" href="<?= e(url('modules/peminjaman/index.php')) ?>"><span class="stat-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v15H4zM8 3v4M16 3v4M4 9h16"/><path d="m8 14 2 2 5-5"/></svg></span><span class="n" data-count="<?= $s['pinjam'] ?>">0</span><span class="l">Sedang dipinjam</span><span class="stat-foot">Lihat transaksi <b>↗</b></span></a>
  <a class="stat warn" href="<?= e(url('modules/laporan/index.php#telat')) ?>"><span class="stat-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="n" data-count="<?= $s['telat'] ?>">0</span><span class="l">Terlambat dikembalikan</span><span class="stat-foot">Perlu perhatian <b>↗</b></span></a>
</div>

<div class="grid g2">
  <div class="card bars">
    <div class="card-heading"><div><span class="eyebrow">KOLEKSI</span><h2>Stok buku per kategori</h2></div><a class="text-link" href="<?= e(url('modules/buku/index.php')) ?>">Semua buku <span>→</span></a></div>
    <?php foreach ($kat as $r): $w = round($r['stok'] / $maxStok * 100); ?>
      <div class="bar-row" title="<?= e($r['nama_kategori'] . ': ' . (int)$r['stok'] . ' eksemplar') ?>">
        <div class="bar-label"><?= e($r['nama_kategori']) ?></div>
        <div class="track"><div class="fill <?= $r['stok'] == 0 ? 'zero' : '' ?>" data-bar-width="<?= $w ?>"></div></div>
        <div class="r"><b><?= (int)$r['stok'] ?></b><small>eks.</small></div>
      </div>
    <?php endforeach; ?>
    <p class="hint">Kategori dengan stok 0 menjadi prioritas pengadaan koleksi.</p>
  </div>

  <div class="card attention-card">
    <div class="card-heading"><div><span class="eyebrow">TINDAK LANJUT</span><h2>Perlu perhatian</h2></div><span class="attention-count"><?= count($late) + count($low) ?></span></div>
    <?php if ($late): ?>
      <p class="alert-heading"><span class="badge b-red">Terlambat</span> <?= $s['telat'] ?> peminjaman melewati jatuh tempo</p>
      <div class="table-scroll"><table><?php foreach ($late as $r): ?>
        <tr><td><?= e($r['nama']) ?><br><small class="text-muted"><?= e($r['judul']) ?></small></td>
            <td class="r"><span class="badge b-red"><?= hari_selisih($r['tgl_kembali'], $today) ?> hari</span></td></tr>
      <?php endforeach; ?></table></div>
      <a href="<?= e(url('modules/laporan/index.php#telat')) ?>" class="btn ghost sm top-gap">Lihat &amp; ingatkan anggota</a>
    <?php else: ?><p class="hint">Tidak ada keterlambatan.</p><?php endif; ?>
    <?php if ($low): ?>
      <p class="section-heading"><span class="badge b-amb">Stok rendah</span> buku dengan stok &le; <?= STOK_RENDAH ?></p>
      <div class="table-scroll"><table><?php foreach ($low as $r): ?>
        <tr><td><?= e($r['judul']) ?></td><td class="r"><b><?= (int)$r['stok'] ?></b> eks.</td></tr>
      <?php endforeach; ?></table></div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="recent-heading">
    <div class="card-heading"><div><span class="eyebrow">AKTIVITAS</span><h2>Transaksi terbaru</h2><a class="text-link" href="<?= e(url('modules/peminjaman/index.php')) ?>">Semua transaksi <span>→</span></a></div></div>
    <div class="recent-status">
      <svg class="status-donut" viewBox="0 0 52 52" role="img" aria-label="Status lima transaksi terbaru: <?= $recentStatus['Dipinjam'] ?> Dipinjam, <?= $recentStatus['Dikembalikan'] ?> Dikembalikan">
        <circle class="donut-track" cx="26" cy="26" r="19"></circle>
        <circle class="donut-active" cx="26" cy="26" r="19" stroke-dasharray="<?= e($recentActiveArc . ' ' . $recentDonutGap) ?>"></circle>
        <circle class="donut-returned" cx="26" cy="26" r="19" stroke-dasharray="<?= e($recentReturnedArc . ' ' . round($donutCircumference - $recentReturnedArc, 2)) ?>" stroke-dashoffset="<?= e((string)-$recentActiveArc) ?>"></circle>
        <text class="donut-total" x="26" y="29" text-anchor="middle"><?= count($recent) ?></text>
      </svg>
      <div class="donut-legend"><span><i class="legend-active"></i>Dipinjam <b><?= $recentStatus['Dipinjam'] ?></b></span><span><i class="legend-returned"></i>Dikembalikan <b><?= $recentStatus['Dikembalikan'] ?></b></span><small>5 transaksi terakhir</small></div>
    </div>
  </div>
  <div class="table-scroll"><table>
    <tr><th>ID</th><th>Anggota</th><th>Buku</th><th>Tgl pinjam</th><th>Status</th></tr>
    <?php foreach ($recent as $r): ?>
      <tr><td><?= e($r['id_peminjaman']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['judul']) ?></td>
          <td><?= e(tgl_indo($r['tgl_pinjam'])) ?></td>
          <td><span class="badge <?= $r['status'] === 'Dipinjam' ? 'b-amb' : 'b-ok' ?>"><?= e($r['status']) ?></span></td></tr>
    <?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="5"><div class="empty-state"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v15H4zM8 3v4M16 3v4M4 9h16"/><path d="m8 14 2 2 5-5"/></svg><p>Belum ada data peminjaman.</p><a class="btn sm" href="<?= e(url('modules/peminjaman/baru.php')) ?>">Catat peminjaman pertama</a></div></td></tr><?php endif; ?>
  </table></div>
</div>
<?php render_footer();
