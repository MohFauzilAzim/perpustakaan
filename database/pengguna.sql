-- Tabel pendukung login petugas (untuk database lokal db_perpustakaan yang sudah ada dari Tugas 1)
CREATE TABLE IF NOT EXISTS pengguna (
  id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  id_petugas VARCHAR(10) NOT NULL,
  FOREIGN KEY (id_petugas) REFERENCES petugas(id_petugas) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun awal: username admin / password admin123 (SEGERA GANTI setelah login)
INSERT INTO pengguna (username, password_hash, id_petugas)
SELECT 'admin', '$2y$10$O6H5rc3ZlCchnpnaYvE89.BEPTxjYTiC032bDN8mKCYBgIa7P9W.a', 'PT001' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM pengguna WHERE username='admin');

UPDATE pengguna
SET password_hash = '$2y$10$O6H5rc3ZlCchnpnaYvE89.BEPTxjYTiC032bDN8mKCYBgIa7P9W.a'
WHERE username = 'admin' AND password_hash = 'admin123';
