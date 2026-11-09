<?php
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout/public.php';

$stats = $pdo->query('SELECT
    (SELECT COUNT(*) FROM buku) AS judul,
    (SELECT COALESCE(SUM(stok), 0) FROM buku) AS eksemplar,
    (SELECT COUNT(*) FROM kategori) AS kategori,
    (SELECT COUNT(*) FROM peminjaman) AS peminjaman')->fetch();
$categories = $pdo->query('SELECT k.id_kategori, k.nama_kategori, COUNT(b.id_buku) AS jumlah_judul
    FROM kategori k LEFT JOIN buku b ON b.id_kategori = k.id_kategori
    GROUP BY k.id_kategori, k.nama_kategori ORDER BY k.nama_kategori')->fetchAll();

render_public_header();
render_public_navbar();
?>
<main id="main-content">
  <section class="public-hero" id="beranda">
    <div class="public-hero-inner">
      <div class="public-hero-copy">
        <span class="public-kicker">UIN WALISONGO SEMARANG</span>
        <h1>Sistem Informasi Perpustakaan</h1>
        <p class="public-hero-description">Temukan koleksi dan layanan perpustakaan untuk mendukung perjalanan ilmu.</p>
        <div class="public-hero-actions">
          <a class="public-button primary" href="<?= e(url('katalog.php')) ?>">Lihat Katalog</a>
        </div>
        <form class="public-search" method="get" action="<?= e(url('katalog.php')) ?>" role="search">
          <label for="quick-search">Pencarian koleksi</label>
          <div class="public-search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
            <input id="quick-search" name="q" type="search" placeholder="Cari judul atau pengarang buku">
            <button type="submit">Cari</button>
          </div>
        </form>
      </div>
    </div>
    <p class="public-hero-caption">General Library &amp; ICT Center - UIN Walisongo Semarang</p>
  </section>
  <section class="public-stats-section" aria-label="Ringkasan koleksi">
    <div class="public-stats-grid">
      <article class="public-stat-card public-reveal">
        <span class="public-stat-value" data-public-count="<?= e($stats['judul']) ?>"><?= e(number_format((int)$stats['judul'], 0, ',', '.')) ?></span>
        <span class="public-stat-label">Judul buku</span>
      </article>
      <article class="public-stat-card public-reveal">
        <span class="public-stat-value" data-public-count="<?= e($stats['eksemplar']) ?>"><?= e(number_format((int)$stats['eksemplar'], 0, ',', '.')) ?></span>
        <span class="public-stat-label">Total eksemplar</span>
      </article>
      <article class="public-stat-card public-reveal">
        <span class="public-stat-value" data-public-count="<?= e($stats['kategori']) ?>"><?= e(number_format((int)$stats['kategori'], 0, ',', '.')) ?></span>
        <span class="public-stat-label">Kategori koleksi</span>
      </article>
      <article class="public-stat-card public-reveal">
        <span class="public-stat-value" data-public-count="<?= e($stats['peminjaman']) ?>"><?= e(number_format((int)$stats['peminjaman'], 0, ',', '.')) ?></span>
        <span class="public-stat-label">Transaksi peminjaman</span>
      </article>
    </div>
  </section>
  <section class="public-section" id="layanan" aria-labelledby="services-title">
    <div class="public-section-heading public-reveal">
      <span class="public-section-kicker">LAYANAN PERPUSTAKAAN</span>
      <h2 id="services-title">Layanan untuk mendukung kegiatan belajar</h2>
    </div>
    <div class="public-services-grid">
      <article class="public-service-card public-reveal">
        <span class="public-service-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v15H4zM8 3v4M16 3v4M4 9h16"/><path d="m8 14 2 2 5-5"/></svg></span>
        <h3>Peminjaman &amp; Pengembalian</h3>
        <p>Petugas mencatat peminjaman dan pengembalian koleksi sesuai ketentuan perpustakaan.</p>
      </article>
      <article class="public-service-card public-reveal">
        <span class="public-service-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8M8 11h8"/></svg></span>
        <h3>Katalog Koleksi</h3>
        <p>Telusuri judul, pengarang, kategori, dan status ketersediaan buku melalui katalog.</p>
      </article>
      <article class="public-service-card public-reveal">
        <span class="public-service-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></span>
        <h3>Pengingat Jatuh Tempo</h3>
        <p>Informasi jatuh tempo membantu pemustaka mengembalikan buku sesuai batas peminjaman.</p>
      </article>
    </div>
  </section>
  <section class="public-process-section" id="alur" aria-labelledby="process-title">
    <div class="public-section public-process-inner">
      <div class="public-section-heading public-reveal">
        <span class="public-section-kicker">ALUR PEMINJAMAN</span>
        <h2 id="process-title">Empat langkah peminjaman buku</h2>
      </div>
      <ol class="public-process-grid">
        <li class="public-process-step public-reveal"><span>01</span><h3>Cari buku</h3><p>Periksa judul dan ketersediaan melalui katalog.</p></li>
        <li class="public-process-step public-reveal"><span>02</span><h3>Datang &amp; verifikasi anggota</h3><p>Bawa kartu identitas anggota untuk diverifikasi petugas.</p></li>
        <li class="public-process-step public-reveal"><span>03</span><h3>Petugas mencatat peminjaman</h3><p>Petugas mencatat buku dan tanggal pengembalian.</p></li>
        <li class="public-process-step public-reveal"><span>04</span><h3>Kembalikan tepat waktu</h3><p>Kembalikan koleksi sebelum tanggal jatuh tempo.</p></li>
      </ol>
      <div class="public-loan-rules public-reveal">
        <strong>Ketentuan peminjaman</strong>
        <span>Maksimal 3 buku per anggota</span>
        <span>Lama pinjam 3, 7, atau 14 hari</span>
      </div>
    </div>
  </section>
  <section class="public-section" id="katalog" aria-labelledby="catalog-title">
    <div class="public-section-heading public-section-heading-row public-reveal">
      <div>
        <span class="public-section-kicker">KOLEKSI PER KATEGORI</span>
        <h2 id="catalog-title">Jelajahi koleksi perpustakaan</h2>
      </div>
      <a class="public-text-link" href="<?= e(url('katalog.php')) ?>">Buka katalog <span aria-hidden="true">→</span></a>
    </div>
    <?php if ($categories): ?>
      <div class="public-category-grid">
        <?php foreach ($categories as $category): ?>
          <a class="public-category-card public-reveal" href="<?= e(url('katalog.php?' . http_build_query(['kategori' => $category['id_kategori']]))) ?>">
            <span class="public-category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8"/></svg></span>
            <strong><?= e($category['nama_kategori']) ?></strong>
            <span><?= e(number_format((int)$category['jumlah_judul'], 0, ',', '.')) ?> judul</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="public-empty-message">Kategori koleksi belum tersedia.</p>
    <?php endif; ?>
  </section>
  <section class="public-contact-section" id="kontak" aria-labelledby="contact-title">
    <div class="public-section public-contact-inner">
      <div class="public-contact-grid">
        <div class="public-contact-copy public-reveal">
          <div class="public-contact-intro">
            <span class="public-section-kicker">KONTAK &amp; INFORMASI</span>
            <h2 id="contact-title">Kunjungi perpustakaan</h2>
            <p>Datang langsung atau hubungi kami melalui kontak berikut.</p>
          </div>
          <div class="public-contact-details">
            <div class="public-contact-item">
              <span class="public-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/></svg></span>
              <div><h3>Jam layanan</h3><p><?= e(PUBLIC_CONTACT['hours']) ?></p></div>
            </div>
            <div class="public-contact-item">
              <span class="public-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <div><h3>Lokasi</h3><p><?= e(PUBLIC_CONTACT['address']) ?></p></div>
            </div>
            <div class="public-contact-item">
              <span class="public-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg></span>
              <div><h3>Email</h3><p><a href="mailto:<?= e(PUBLIC_CONTACT['email']) ?>"><?= e(PUBLIC_CONTACT['email']) ?></a></p></div>
            </div>
            <div class="public-contact-item">
              <span class="public-contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h3l2 5-2 1.5a15 15 0 0 0 4.5 4.5L16 12l5 2v3c0 1.1-.9 2-2 2C10.7 19 5 13.3 5 6c0-1.7.7-3 2-3Z"/></svg></span>
              <div><h3>Telepon</h3><p><a href="tel:<?= e(PUBLIC_CONTACT['phone']) ?>"><?= e(PUBLIC_CONTACT['phone']) ?></a></p></div>
            </div>
          </div>
          <a class="public-contact-map" href="https://www.google.com/maps/search/?api=1&amp;query=UIN+Walisongo+Semarang" target="_blank" rel="noopener noreferrer">
            Lihat di Google Maps
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9"/><path d="M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"/></svg>
          </a>
        </div>
        <figure class="public-location-card public-reveal">
          <figcaption class="public-location-heading"><span>LOKASI</span><strong>General Library &amp; ICT Center</strong></figcaption>
          <img src="<?= e(url('assets/img/gedung-halus.png')) ?>" alt="Gedung General Library &amp; ICT Center UIN Walisongo" width="1348" height="740" loading="lazy" decoding="async">
        </figure>
      </div>
    </div>
  </section>
</main>
<?php render_public_footer(); ?>
