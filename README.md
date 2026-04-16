# Sistem Manajemen Kost Multi-Cabang

Aplikasi manajemen kost berbasis web untuk pengelolaan multi-cabang. Dibangun dengan Laravel 12, Filament V3, dan Tailwind CSS.

## Fitur Utama

- **Multi-Cabang:** Isolasi data antar cabang untuk Admin Cabang.
- **Manajemen Kamar:** Pemantauan status kamar (Tersedia, Terisi, Perbaikan).
- **Manajemen Sewa & Deposit:** Pencatatan kontrak sewa penyewa lengkap dengan pengelolaan uang jaminan (deposit).
- **Layanan Tambahan (Add-ons):** Pengelolaan layanan ekstra per kamar (Wifi, Parkir, dll) yang dapat ditagihkan secara berulang.
- **Tagihan Itemized & Otomatis:** Pembuatan invoice bulanan otomatis dengan rincian item (Sewa, Deposit, Layanan, Denda).
- **Denda Otomatis:** Sistem denda (Flat atau Harian) yang dikonfigurasi per cabang dengan masa tenggang.
- **Verifikasi Pembayaran:** Konfirmasi manual bukti transfer oleh admin.
- **Download Invoice PDF:** Fitur unduh invoice dalam format PDF yang aman dan profesional.
- **Proses Check-out:** Penghitungan otomatis penyelesaian deposit dan status kamar saat penyewa keluar.
- **Modul Sewa & Tagihan (Financial Core):** Pengelolaan kontrak sewa yang terintegrasi dengan invoice otomatis, denda keterlambatan, layanan tambahan, dan sistem deposit yang akurat.
- **Modul Komplain & Perawatan Profesional:** Manajemen laporan kerusakan lengkap dengan lampiran foto (sebelum/sesudah), penugasan teknisi, pelacakan waktu kerja, dan integrasi biaya perbaikan ke deposit.
- **Dashboard & Statistik (Reporting):** Visualisasi data real-time untuk owner dan admin, termasuk grafik pendapatan bulanan, diagram okupansi kamar, dan widget daftar tunggakan.
- **Dynamic RBAC (Role-Based Access Control):** Pengelolaan role dan izin yang fleksibel menggunakan Filament Shield. Owner dan Developer dapat membuat role baru dan mengatur hak akses spesifik per menu dan aksi.

## Instruksi Pembaruan Sistem (PENTING)
Jika Anda baru saja menarik (pull) perubahan terbaru, jalankan perintah berikut untuk memperbarui struktur database dan sistem keamanan:
```bash
php artisan migrate
php artisan shield:generate --all --panel=admin
php artisan db:seed --class=RolePermissionSeeder
```

## Fitur Utama Modul Sewa & Tagihan
- **Itemized Invoicing:** Setiap tagihan merinci biaya sewa, deposit, layanan tambahan, dan denda secara transparan.
- **Otomasi Tagihan:** Sistem secara otomatis menerbitkan invoice bulanan berdasarkan tanggal tagihan yang ditentukan di kontrak sewa.
- **Sistem Denda Fleksibel:** Mendukung denda Flat (sekali bayar) atau Harian (akumulatif) dengan pengaturan masa tenggang (grace period) per cabang.
- **Manajemen Deposit:** Deposit dicatat saat mulai sewa dan dikelola secara otomatis sebagai pemotong tagihan/biaya perbaikan saat check-out.
- **Invoice PDF & Bukti Bayar:** Penyewa dapat mengunduh invoice profesional dalam format PDF dan mengunggah bukti transfer langsung dari dashboard.

## Role Pengguna

1. **Developer:** Akses penuh ke seluruh sistem dan data (tersembunyi dari user lain).
2. **Owner:** Melihat semua data di seluruh cabang.
3. **Admin Cabang:** Mengelola data hanya pada cabang yang ditugaskan.
4. **Teknisi (Technician):** Melihat daftar komplain yang ditugaskan, mencatat waktu mulai/selesai kerja, dan mengunggah foto bukti perbaikan.
5. **Penyewa (Tenant):** Melihat tagihan, mengunggah bukti bayar, melaporkan komplain kerusakan, dan mengunduh invoice PDF mereka sendiri.

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
   *Catatan: Pastikan untuk mendaftarkan command harian di `routes/console.php`.*

## Alur Kerja Utama

### 1. Pendaftaran & Sewa Baru
- Admin membuat data **Penyewa**.
- Admin membuat data **Sewa (Lease)**, menentukan kamar, jumlah deposit, dan layanan tambahan (Wifi, dll).
- **Sistem Otomatis:** Saat Sewa dibuat, status kamar berubah menjadi `Terisi` dan **Invoice Pertama** terbit mencakup biaya sewa bulan pertama, deposit, dan layanan yang dipilih.

### 2. Pembayaran & Invoice
- Penyewa melihat rincian tagihan dan mengunduh **Invoice PDF**.
- Penyewa mengunggah bukti transfer via dashboard.
- Admin memverifikasi pembayaran. Status Invoice berubah menjadi `Lunas` setelah diverifikasi.

### 3. Penagihan Bulanan & Denda
- Sistem menjalankan `kost:generate-invoices` setiap hari untuk mengecek siapa yang masuk tanggal tagihan.
- Sistem menjalankan `kost:mark-overdue` untuk menandai tagihan terlambat dan **menerapkan denda** sesuai konfigurasi cabang (Flat atau akumulasi Harian).

### 4. Modul Komplain & Perbaikan
- **Penyewa** melaporkan kerusakan melalui menu Komplain dan mengunggah foto awal.
- **Admin** menugaskan laporan tersebut ke **Teknisi**.
- **Teknisi** menekan tombol "Mulai Kerja" dan setelah selesai menekan "Selesaikan" sambil mengunggah foto hasil perbaikan serta rincian biaya.
- **Biaya Perbaikan:** Jika ditandai sebagai tanggung jawab penyewa, biaya ini akan otomatis terkumpul di data sewa.

### 5. Proses Check-out
- Saat penyewa akan keluar, Admin menggunakan tombol **Check-out** di menu Sewa.
- Sistem menghitung sisa deposit yang harus dikembalikan secara akurat:
  - `Sisa Deposit = (Deposit Awal) - (Tagihan Belum Lunas) - (Biaya Perbaikan yang dibebankan ke penyewa)`.
- Status kamar otomatis kembali menjadi `Tersedia`.

## Perintah Khusus
- Generate tagihan bulanan secara manual:
  ```bash
  php artisan kost:generate-invoices
  ```
- Tandai tagihan terlambat & hitung denda secara manual:
  ```bash
  php artisan kost:mark-overdue
  ```

---
Dibuat dengan ❤️ oleh Jules.
