# Sistem Manajemen Kost Multi-Cabang

Aplikasi manajemen kost berbasis web untuk pengelolaan multi-cabang. Dibangun dengan Laravel 12, Filament V3, dan Tailwind CSS.

## Fitur Utama

- **Multi-Cabang:** Isolasi data antar cabang untuk Admin Cabang.
- **Manajemen Kamar:** Pemantauan status kamar (Tersedia, Terisi, Perbaikan).
- **Manajemen Sewa:** Pencatatan kontrak sewa penyewa.
- **Tagihan Otomatis:** Pembuatan invoice bulanan secara otomatis.
- **Verifikasi Pembayaran:** Konfirmasi manual bukti transfer oleh admin.
- **Layanan Komplain:** Manajemen laporan kerusakan dari penyewa.
- **Dashboard Statistik:** Ringkasan pendapatan dan ketersediaan kamar.

## Role Pengguna

1. **Developer:** Akses penuh ke seluruh sistem dan data (tersembunyi dari user lain).
2. **Owner:** Melihat semua data di seluruh cabang.
3. **Admin Cabang:** Mengelola data hanya pada cabang yang ditugaskan.
4. **Penyewa (Tenant):** Melihat tagihan dan status sewa mereka sendiri.

## Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL >= 8.0

## Instalasi Lokal

1. **Clone repositori:**
   ```bash
   git clone <repository-url>
   cd <repository-name>
   ```

2. **Instal dependensi PHP:**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Pastikan konfigurasi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai dengan server MySQL lokal Anda.*

4. **Persiapan Database:**
   Buat database baru di MySQL (misal: `kost_db`), lalu jalankan:
   ```bash
   php artisan migrate --seed
   ```

5. **Instal dependensi Frontend:**
   ```bash
   npm install
   npm run dev
   ```

6. **Akses Aplikasi:**
   - Frontend: `http://localhost:8000`
   - Admin Panel: `http://localhost:8000/admin`
   - Akun Default:
     - Developer: `dev@admin.com` / `password`
     - Owner: `owner@admin.com` / `password`

## Instalasi di Server (Produksi)

1. **Upload file ke server** (via Git atau FTP).
2. **Jalankan perintah instalasi** seperti di lokal (tanpa `--seed` jika tidak ingin data dummy).
3. **Konfigurasi Nginx/Apache** untuk mengarah ke direktori `public/`.
4. **Optimasi Aplikasi:**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. **Setup Cron Job untuk Tagihan Otomatis:**
   Tambahkan baris berikut di crontab server Anda:
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```
   *Catatan: Pastikan untuk mendaftarkan command `kost:generate-invoices` di `routes/console.php` agar berjalan harian.*

## Alur Kerja Utama

### 1. Pendaftaran Penghuni
- Admin membuat data **Pengguna** dengan role `Penyewa`.
- Admin membuat data **Sewa (Lease)**, menghubungkan penyewa dengan kamar dan cabang.
- **Sistem Otomatis:** Saat Sewa dibuat, status kamar berubah menjadi `Terisi` dan **Invoice Pertama** otomatis terbit.

### 2. Pembayaran Tagihan
- Penyewa login ke dashboard, masuk ke menu **Tagihan**.
- Penyewa menekan tombol **Bayar** dan mengunggah bukti transfer.
- Admin mengecek menu **Pembayaran**, lalu menekan tombol **Verifikasi**.
- **Sistem Otomatis:** Saat Pembayaran diverifikasi, status Invoice berubah menjadi `Lunas`.

### 3. Penagihan Bulanan
- Setiap bulan (sesuai tanggal tagihan di kontrak), sistem menjalankan perintah `kost:generate-invoices` untuk menerbitkan tagihan baru.
- Tagihan yang belum dibayar melewati jatuh tempo akan otomatis ditandai sebagai `Overdue` via perintah `kost:mark-overdue`.

## Perintah Khusus
- Generate tagihan bulanan secara manual:
  ```bash
  php artisan kost:generate-invoices
  ```
- Tandai tagihan terlambat secara manual:
  ```bash
  php artisan kost:mark-overdue
  ```

---
Dibuat dengan ❤️ oleh Jules.
