<?php
require_once __DIR__ . '/sidebar.php';

function render_footer(): void
{
    ?>
</main>
<footer class="foot">Sistem Informasi Perpustakaan - UIN Walisongo Semarang</footer>
<?php if (!empty($_SESSION['user'])): ?>
</div>
<div class="sidebar-backdrop" data-menu-close></div>
<?php endif; ?>
<dialog class="confirm-dialog" data-confirm-dialog aria-labelledby="confirm-title" aria-describedby="confirm-message">
  <h2 id="confirm-title">Konfirmasi tindakan</h2>
  <p id="confirm-message" data-confirm-message>Apakah Anda yakin ingin melanjutkan?</p>
  <div class="dialog-actions">
    <button class="btn ghost" type="button" data-confirm-cancel autofocus>Batal</button>
    <button class="btn danger" type="button" data-confirm-accept>Hapus</button>
  </div>
</dialog>
<script src="<?= e(url('assets/js/app.js?v=' . filemtime(__DIR__ . '/../../assets/js/app.js'))) ?>" defer></script>
</body>
</html>
<?php
}
