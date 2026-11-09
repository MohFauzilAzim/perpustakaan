<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();
$today = date('Y-m-d');

/* A. Keterlambatan */
$st = $pdo->prepare("SELECT p.id_peminjaman, a.nama, a.no_telepon, b.judul, p.tgl_pinjam, p.tgl_kembali
    FROM peminjaman p JOIN anggota a ON p.id_anggota=a.id_anggota JOIN buku b ON p.id_buku=b.id_buku
    WHERE p.status='Dipinjam' AND p.tgl_kembali < ? ORDER BY p.tgl_kembali");
$st->execute([$today]); $telat = $st->fetchAll();

/* B. Rekap stok per kategori */
$stok = $pdo->query('SELECT k.nama_kategori, COUNT(b.id_buku) AS total_judul, COALESCE(SUM(b.stok),0) AS total_stok
    FROM kategori k LEFT JOIN buku b ON k.id_kategori=b.id_kategori
    GROUP BY k.id_kategori, k.nama_kategori ORDER BY k.id_kategori')->fetchAll();

/* C. Rekap peminjaman per anggota */
$rekA = $pdo->query("SELECT a.id_anggota, a.nama, COUNT(p.id_peminjaman) AS total,
    COALESCE(SUM(p.status='Dipinjam'),0) AS aktif
    FROM anggota a LEFT JOIN peminjaman p ON a.id_anggota=p.id_anggota
    GROUP BY a.id_anggota, a.nama ORDER BY total DESC, a.id_anggota")->fetchAll();

/* D. Rekap per petugas */
$rekP = $pdo->query('SELECT t.nama_petugas, t.jabatan, COUNT(p.id_peminjaman) AS total
    FROM petugas t LEFT JOIN peminjaman p ON t.id_petugas=p.id_petugas
    GROUP BY t.id_petugas, t.nama_petugas, t.jabatan ORDER BY total DESC, t.id_petugas')->fetchAll();

/* E. Buku paling diminati */
$top = $pdo->query('SELECT b.judul, b.pengarang, COUNT(p.id_peminjaman) AS total
    FROM buku b JOIN peminjaman p ON b.id_buku=p.id_buku
    GROUP BY b.id_buku, b.judul, b.pengarang ORDER BY total DESC, b.judul LIMIT 5')->fetchAll();

render_header('Laporan', 'laporan');
?>
<div class="print-report-header">
  <img src="<?= e(url('assets/img/logo.png')) ?>" alt="Logo UIN Walisongo" width="60" height="60">
  <h1>Laporan &amp; Pemantauan</h1>
  <p>UIN Walisongo Semarang · Dicetak <?= e(tgl_indo($today)) ?></p>
</div>
<div class="head">
  <div><h1>Laporan &amp; Pemantauan</h1><p class="sub">Data untuk pelayanan anggota dan pengadaan koleksi &middot; per <?= e(tgl_indo($today)) ?></p></div>
  <button class="btn ghost noprint" type="button" data-print>Cetak Laporan</button>
</div>

<div class="card" id="telat">
  <h2>1. Keterlambatan pengembalian <span class="badge <?= $telat ? 'b-red' : 'b-ok' ?>"><?= count($telat) ?></span></h2>
  <p class="hint">Hubungi anggota langsung melalui WhatsApp untuk menindaklanjuti keterlambatan.</p>
  <div class="table-scroll"><table>
    <tr><th>ID</th><th>Anggota</th><th>Buku</th><th>Jatuh tempo</th><th>Terlambat</th><th class="noprint"></th></tr>
    <?php foreach ($telat as $r):
        $hari = hari_selisih($r['tgl_kembali'], $today);
        $pesan = 'Halo ' . $r['nama'] . ', kami dari Perpustakaan Digital. Buku "' . $r['judul'] . '" yang Anda pinjam jatuh tempo pada ' . tgl_indo($r['tgl_kembali']) . '. Mohon segera dikembalikan. Terima kasih.'; ?>
      <tr><td><?= e($r['id_peminjaman']) ?></td><td><b><?= e($r['nama']) ?></b><br><small class="text-muted"><?= e($r['no_telepon']) ?></small></td>
        <td><?= e($r['judul']) ?></td><td><?= e(tgl_indo($r['tgl_kembali'])) ?></td>
        <td><span class="badge b-red"><?= $hari ?> hari</span></td>
        <td class="r noprint"><a class="btn sm" target="_blank" rel="noopener" href="<?= e(wa_link($r['no_telepon'], $pesan)) ?>">Ingatkan via WhatsApp</a></td></tr>
    <?php endforeach; ?>
    <?php if (!$telat): ?><tr><td colspan="6" class="empty">Tidak ada keterlambatan saat ini.</td></tr><?php endif; ?>
  </table></div>
</div>

<div class="card">
  <h2>2. Rekap stok per kategori &amp; rekomendasi pengadaan</h2>
  <div class="table-scroll"><table>
    <tr><th>Kategori</th><th class="r">Total judul</th><th class="r">Total stok</th><th>Rekomendasi pengadaan</th></tr>
    <?php foreach ($stok as $r): $n = (int)$r['total_stok']; ?>
      <tr><td><b><?= e($r['nama_kategori']) ?></b></td><td class="r"><?= (int)$r['total_judul'] ?></td><td class="r"><?= $n ?></td>
        <td><?php if ($n === 0): ?><span class="badge b-red">Kosong - prioritas pengadaan</span>
            <?php elseif ($n < 10): ?><span class="badge b-amb">Menipis - pertimbangkan tambah koleksi</span>
            <?php else: ?><span class="badge b-ok">Cukup</span><?php endif; ?></td></tr>
    <?php endforeach; ?>
  </table></div>
  <p class="hint">Kriteria: stok 0 = kosong, stok kurang dari 10 = menipis. Data ini dapat dibagikan kepada penerbit/distributor sebagai dasar permintaan pengadaan.</p>
</div>

<div class="grid g2">
  <div class="card">
    <h2>3. Peminjaman per anggota</h2>
    <div class="table-scroll"><table><tr><th>Anggota</th><th class="r">Total</th><th class="r">Aktif</th></tr>
      <?php foreach ($rekA as $r): ?><tr><td><?= e($r['nama']) ?></td><td class="r"><?= (int)$r['total'] ?></td><td class="r"><?= (int)$r['aktif'] ?></td></tr><?php endforeach; ?>
      <?php if (!$rekA): ?><tr><td colspan="3" class="empty">Belum ada data peminjaman.</td></tr><?php endif; ?></table></div>
  </div>
  <div class="card">
    <h2>4. Transaksi per petugas</h2>
    <div class="table-scroll"><table><tr><th>Petugas</th><th>Jabatan</th><th class="r">Transaksi</th></tr>
      <?php foreach ($rekP as $r): ?><tr><td><?= e($r['nama_petugas']) ?></td><td><?= e($r['jabatan']) ?></td><td class="r"><?= (int)$r['total'] ?></td></tr><?php endforeach; ?>
      <?php if (!$rekP): ?><tr><td colspan="3" class="empty">Belum ada data transaksi.</td></tr><?php endif; ?></table></div>
  </div>
</div>

<div class="card">
  <h2>5. Buku paling diminati</h2>
  <div class="table-scroll"><table><tr><th>Judul</th><th>Pengarang</th><th class="r">Kali dipinjam</th></tr>
    <?php foreach ($top as $r): ?><tr><td><b><?= e($r['judul']) ?></b></td><td><?= e($r['pengarang']) ?></td><td class="r"><?= (int)$r['total'] ?></td></tr><?php endforeach; ?>
    <?php if (!$top): ?><tr><td colspan="3" class="empty">Belum ada data peminjaman.</td></tr><?php endif; ?></table></div>
</div>
<?php render_footer();
