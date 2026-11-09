# Sistem Informasi Perpustakaan

Aplikasi web PHP + MySQL untuk perpustakaan (lanjutan Tugas 1 dan Tugas 2).

## Fitur utama
1. Beranda publik dan katalog koleksi yang dapat dicari tanpa login.
2. Dashboard ringkasan operasional (statistik, stok per kategori, peringatan).
3. Pengelolaan koleksi buku (tambah, ubah, hapus, cari, filter kategori).
4. Pengelolaan anggota (tambah, ubah, hapus, status Aktif/Nonaktif).
5. Peminjaman & pengembalian (stok otomatis, jatuh tempo, batas 3 buku).
6. Laporan & pemantauan (keterlambatan + pengingat WhatsApp, rekap stok & pengadaan, dll.).
Pendukung: login petugas dan ganti password.

## Struktur
- `config/config.php`: konfigurasi database dan `BASE_URL`.
- `includes/`: koneksi, helper, serta layout bersama.
- `index.php`, `katalog.php`, `dashboard.php`: beranda publik, katalog publik, dan dashboard petugas.
- `modules/`: halaman buku, anggota, peminjaman, laporan, dan akun.
- `assets/css/` dan `assets/js/`: tampilan dan interaksi antarmuka.
- `database/`: berkas SQL yang sudah ada.

## Menjalankan di komputer sendiri (XAMPP)
1. Salin SEMUA isi folder ini ke `C:\xampp\htdocs\Perpustakaan`.
2. Nyalakan Apache dan MySQL di XAMPP.
3. Buka phpMyAdmin -> pilih database `db_perpustakaan` -> tab SQL -> tempel isi `database/pengguna.sql` -> Kirim.
4. Buka http://localhost/Perpustakaan/ untuk beranda dan katalog publik. Pilih **Masuk Petugas** untuk login dengan akun awal `admin` / `admin123` jika password belum pernah diganti.
5. Setelah login, dashboard petugas tersedia di `dashboard.php`. Segera ganti password awal.
6. Sesuaikan jam layanan, alamat, email, dan telepon pada konstanta `PUBLIC_CONTACT` di `config/config.php`.
7. Jika alamat instalasi berbeda, atur environment variable `BASE_URL` agar menunjuk ke direktori aplikasi.

`assets/img/logo.png` memuat logo UIN Walisongo yang disediakan, dan `assets/img/favicon.png` adalah favicon yang dibuat dari logo dengan proporsi asli.

## Deploy ke hosting (URL publik)
1. Buat hosting PHP + MySQL (mis. InfinityFree) dan buat sebuah database MySQL dari panel hosting.
2. Di phpMyAdmin hosting: pilih database -> Impor -> pilih `database/setup_lengkap.sql`.
3. Edit `config/config.php`: isi DB_HOST, DB_NAME, DB_USER, DB_PASS sesuai data database dari hosting.
4. Unggah seluruh file (kecuali folder `database` dan README) ke folder `htdocs` hosting.
5. Buka alamat websitenya, login, lalu GANTI PASSWORD awal.
