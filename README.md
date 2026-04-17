# Sistem Manajemen Kost Multi-Cabang

Aplikasi manajemen kost berbasis web untuk pengelolaan multi-cabang yang profesional. Dibangun dengan Laravel 12, Filament V3, dan Tailwind CSS.

## Fitur Utama

- **Multi-Cabang:** Isolasi data antar cabang untuk Admin Cabang dengan pengawasan penuh dari Owner.
- **Manajemen Kamar:** Pemantauan status kamar (Tersedia, Terisi, Perbaikan), lengkap dengan visualisasi okupansi.
- **Manajemen Sewa & Deposit:** Pengelolaan kontrak sewa terintegrasi dengan sistem uang jaminan (deposit).
- **Master Data Layanan & Aset:**
    - **Layanan:** Biaya tambahan (Wifi, Laundry, dll) yang fleksibel per kamar.
    - **Aset (Inventaris):** Katalog barang terpusat dan pemantauan inventaris detail di tiap kamar lengkap dengan galeri foto.
- **Sistem Keuangan Profesional:**
    - **Tagihan Itemized:** Invoice otomatis dengan rincian biaya transparan.
    - **Denda Otomatis:** Perhitungan denda (Flat/Harian) dengan masa tenggang per cabang.
    - **Manajemen Pengeluaran:** Pencatatan biaya operasional (Gaji, Utilitas, dll) yang terintegrasi dengan modul perbaikan.
    - **Metode Pembayaran:** Pengaturan rekening bank/metode bayar yang otomatis muncul sebagai instruksi di invoice PDF.
- **Manajemen Komplain & Perbaikan:** Alur kerja lengkap dari laporan penyewa, penugasan teknisi, hingga otomatisasi pencatatan biaya perbaikan.
- **Dynamic RBAC:** Keamanan tingkat lanjut dengan izin akses (permissions) yang dapat diatur secara dinamis via UI.
- **Visual Reporting:** Dashboard interaktif dengan grafik pendapatan vs pengeluaran, tren okupansi, dan daftar tunggakan.

## Role Pengguna

1. **Developer:** Akses sistem level rendah (Super Admin).
2. **Owner:** Pemilik bisnis dengan akses penuh ke seluruh cabang dan laporan finansial.
3. **Admin Cabang:** Pengelola operasional harian yang terbatas pada cabang tugasnya.
4. **Teknisi (Technician):** Fokus pada penanganan komplain, dokumentasi foto (sebelum/sesudah), dan pelaporan biaya perbaikan.
5. **Penyewa (Tenant):** Akses mandiri untuk melihat tagihan, mengunduh invoice PDF, mengunggah bukti bayar, dan melaporkan keluhan.

## Persyaratan Sistem

- PHP >= 8.2
- MySQL >= 8.0
- Composer
- Node.js & NPM

## Instruksi Instalasi

1. **Clone Repositori:**
   ```bash
   git clone <repository-url>
   cd <repository-name>
   ```

2. **Instal Dependensi:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit file `.env` dan atur koneksi database MySQL Anda.*

4. **Inisialisasi Database & Keamanan:**
   ```bash
   php artisan migrate
   php artisan shield:generate --all --panel=admin
   php artisan db:seed --class=RolePermissionSeeder
   ```

5. **Akses Admin:**
   - URL: `http://localhost:8000/admin`
   - Default: `dev@admin.com` / `password`

## Alur Kerja Utama

### 1. Setup Properti (Admin/Owner)
- Daftarkan **Cabang** dan atur kebijakan denda.
- Buat **Kategori Komplain**, **Kategori Pengeluaran**, dan **Metode Pembayaran**.
- Input **Aset** (AC, Kasur, dll) dan daftarkan **Kamar**.

### 2. Siklus Sewa
- **Sewa Baru:** Admin membuat data Sewa. Sistem otomatis menerbitkan Invoice pertama (Sewa + Deposit) dan mengubah status kamar menjadi `Terisi`.
- **Penagihan Bulanan:** Sistem (via Scheduler) menerbitkan invoice otomatis setiap bulan.
- **Pembayaran:** Penyewa melihat instruksi bayar di PDF, membayar, dan unggah bukti. Admin memverifikasi bukti bayar tersebut.

### 3. Operasional & Pemeliharaan
- **Komplain:** Penyewa melapor -> Admin menugaskan Teknisi -> Teknisi bekerja (Foto Before/After) -> Teknisi melapor biaya.
- **Otomatisasi Biaya:** Jika perbaikan disebabkan penyewa, biaya otomatis dikaitkan ke data sewa untuk dipotong dari deposit saat check-out. Selain itu, setiap biaya perbaikan otomatis tercatat di modul Pengeluaran.

### 4. Penutupan Sewa (Check-out)
- Admin menjalankan proses **Check-out**.
- Sistem menghitung pengembalian deposit: `Sisa = Deposit - Hutang Tagihan - Biaya Perbaikan`.
- Kamar otomatis kembali `Tersedia`.

## Pemeliharaan Sistem

### Scheduler (Sangat Penting)
Agar tagihan otomatis dan sistem denda berjalan, aktifkan cron job di server Anda:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Perintah Manual
- Generate tagihan: `php artisan kost:generate-invoices`
- Proses denda: `php artisan kost:mark-overdue`
- Update Izin Akses: `php artisan shield:generate --all --panel=admin`

---
Dibuat dengan ❤️ untuk operasional Kost yang lebih profesional.
