-- ============================================================
-- SETUP LENGKAP untuk HOSTING (impor lewat phpMyAdmin pada database kosong)
-- Berisi 5 tabel Tugas 1 + tabel pengguna + data simulasi.
-- Tidak memakai CREATE DATABASE (database dibuat dari panel hosting).
-- ============================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS kategori (
  id_kategori VARCHAR(10) PRIMARY KEY,
  nama_kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS buku (
  id_buku VARCHAR(10) PRIMARY KEY,
  judul VARCHAR(100) NOT NULL,
  pengarang VARCHAR(100) NOT NULL,
  id_kategori VARCHAR(10) NOT NULL,
  stok INT NOT NULL,
  FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS anggota (
  id_anggota VARCHAR(10) PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  no_telepon VARCHAR(15) NOT NULL,
  status_aktif VARCHAR(10) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS petugas (
  id_petugas VARCHAR(10) PRIMARY KEY,
  nama_petugas VARCHAR(100) NOT NULL,
  jabatan VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS peminjaman (
  id_peminjaman VARCHAR(10) PRIMARY KEY,
  id_anggota VARCHAR(10) NOT NULL,
  id_buku VARCHAR(10) NOT NULL,
  id_petugas VARCHAR(10) NOT NULL,
  tgl_pinjam DATE NOT NULL,
  tgl_kembali DATE NOT NULL,
  status VARCHAR(20) NOT NULL,
  FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
  FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
  FOREIGN KEY (id_petugas) REFERENCES petugas(id_petugas) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS pengguna (
  id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  id_petugas VARCHAR(10) NOT NULL,
  FOREIGN KEY (id_petugas) REFERENCES petugas(id_petugas) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO kategori VALUES
('KAT01','Teknologi Informasi'),('KAT02','Sains & Matematika'),
('KAT03','Ekonomi & Bisnis'),('KAT04','Sastra & Bahasa'),('KAT05','Sejarah & Budaya');
INSERT INTO buku VALUES
('BK001','Basis Data Terdistribusi','Budi Raharjo','KAT01',12),
('BK002','Pemrograman Python Dasar','Eko Kurniawan','KAT01',15),
('BK003','Kalkulus Lanjut','Siti Nurhaliza','KAT02',8),
('BK004','Manajemen Keuangan','Bambang Tri','KAT03',12),
('BK005','Sejarah Dunia','Ahmad Dahlan','KAT05',5);
INSERT INTO anggota VALUES
('AG001','Andi Wijaya','andi@gmail.com','081234567890','Aktif'),
('AG002','Beringin Putra','beringin@gmail.com','081234567891','Aktif'),
('AG003','Citra Dewi','citra@gmail.com','081234567892','Aktif'),
('AG005','Eka Rahmawati','eka@gmail.com','081234567894','Aktif'),
('AG006','Fajar Nugroho','fajar@gmail.com','081234567895','Aktif');
INSERT INTO petugas VALUES
('PT001','Rudi Hartono','Kepala Perpustakaan'),
('PT002','Siska Amelia','Pustakawan'),
('PT003','Dedi Santoso','Pustakawan'),
('PT004','Lina Marlina','Staf Administrasi'),
('PT005','Vina Pandu','Pustakawan');
INSERT INTO peminjaman VALUES
('PJ001','AG001','BK001','PT002','2026-10-01','2026-10-08','Dikembalikan'),
('PJ002','AG002','BK002','PT002','2026-10-02','2026-10-09','Dikembalikan'),
('PJ003','AG003','BK003','PT005','2026-10-05','2026-10-12','Dipinjam'),
('PJ004','AG001','BK004','PT002','2026-10-06','2026-10-13','Dipinjam'),
('PJ005','AG005','BK005','PT005','2026-10-07','2026-10-14','Dikembalikan');

-- Akun awal: username admin / password admin123 (SEGERA GANTI setelah login)
INSERT INTO pengguna (username, password_hash, id_petugas) VALUES ('admin', '$2y$10$O6H5rc3ZlCchnpnaYvE89.BEPTxjYTiC032bDN8mKCYBgIa7P9W.a', 'PT001');
