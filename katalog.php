<?php
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout/public.php';

$q = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$selectedCategory = is_string($_GET['kategori'] ?? null) ? trim($_GET['kategori']) : '';
if (isset($_GET['page']) && (!is_string($_GET['page']) || !ctype_digit($_GET['page']))) {
    $_GET['page'] = '1';
}

$where = ' WHERE 1=1';
$params = [];
if ($q !== '') {
    $where .= ' AND (b.judul LIKE ? OR b.pengarang LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($selectedCategory !== '') {
    $where .= ' AND b.id_kategori = ?';
    $params[] = $selectedCategory;
}

$pg = paginate($pdo, 'SELECT COUNT(*) FROM buku b' . $where, $params, 12);
$books = $pdo->prepare('SELECT b.judul, b.pengarang, k.nama_kategori, b.stok
    FROM buku b JOIN kategori k ON b.id_kategori = k.id_kategori'
    . $where . ' ORDER BY b.judul ASC LIMIT ? OFFSET ?');
foreach ($params as $index => $value) {
    $books->bindValue($index + 1, $value);
}
$books->bindValue(count($params) + 1, $pg['per'], PDO::PARAM_INT);
$books->bindValue(count($params) + 2, $pg['offset'], PDO::PARAM_INT);
$books->execute();
$rows = $books->fetchAll();

$categoryQuery = $pdo->prepare('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori');
$categoryQuery->execute();
$categories = $categoryQuery->fetchAll();

render_public_header(
    'Katalog - Perpustakaan UIN Walisongo',
    'Telusuri buku, pengarang, kategori, dan ketersediaan koleksi Perpustakaan UIN Walisongo.'
);
render_public_navbar('katalog');
?>
<main id="main-content" class="public-catalog-page">
  <section class="public-catalog-intro">
    <div class="public-catalog-inner">
      <span class="public-section-kicker">KOLEKSI PERPUSTAKAAN</span>
      <h1>Katalog Buku</h1>
      <p>Telusuri koleksi berdasarkan judul, pengarang, atau kategori.</p>
    </div>
  </section>
  <section class="public-catalog-content" aria-label="Daftar buku">
    <div class="public-catalog-inner">
      <form class="public-catalog-filter" method="get" action="<?= e(url('katalog.php')) ?>" role="search">
        <div class="public-catalog-search">
          <label for="catalog-search">Cari judul atau pengarang</label>
          <input id="catalog-search" name="q" type="search" value="<?= e($q) ?>" placeholder="Contoh: Python">
        </div>
        <div class="public-catalog-category">
          <label for="catalog-category">Kategori</label>
          <select id="catalog-category" name="kategori">
            <option value="">Semua kategori</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= e($category['id_kategori']) ?>" <?= $selectedCategory === $category['id_kategori'] ? 'selected' : '' ?>><?= e($category['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="public-button primary" type="submit">Cari buku</button>
        <?php if ($q !== '' || $selectedCategory !== ''): ?>
          <a class="public-catalog-reset" href="<?= e(url('katalog.php')) ?>">Hapus filter</a>
        <?php endif; ?>
      </form>
      <div class="public-catalog-results" aria-live="polite">
        <p class="public-catalog-count"><?= e(number_format((int)$pg['total'], 0, ',', '.')) ?> judul ditemukan</p>
        <?php if ($rows): ?>
          <div class="public-book-grid">
            <?php foreach ($rows as $book): ?>
              <article class="public-book-card">
                <span class="public-book-category"><?= e($book['nama_kategori']) ?></span>
                <h2><?= e($book['judul']) ?></h2>
                <p class="public-book-author"><?= e($book['pengarang']) ?></p>
                <span class="public-book-status <?= (int)$book['stok'] > 0 ? 'available' : 'unavailable' ?>">
                  <?= (int)$book['stok'] > 0 ? 'Tersedia' : 'Habis' ?>
                </span>
              </article>
            <?php endforeach; ?>
          </div>
          <?= pager($pg) ?>
        <?php else: ?>
          <div class="public-catalog-empty">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"/><path d="M4 5.5v15.2M8 7h8"/></svg>
            <p>Buku tidak ditemukan.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>
<?php render_public_footer(); ?>
