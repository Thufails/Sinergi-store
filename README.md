# Sinergi Store

Aplikasi katalog dan pemesanan Sinergi Store yang dibangun dengan Laravel, Blade, Tailwind CSS, dan Vite. Aplikasi menyediakan halaman publik, pengelolaan produk dan kategori, pesanan, testimoni, serta dashboard admin.

## Persyaratan

- PHP **8.3** atau lebih baru sesuai batas versi project (`^8.3`), dengan ekstensi yang diperlukan Laravel dan Composer.
- Composer.
- Node.js dan npm.
- SQLite untuk setup lokal bawaan, atau database lain yang didukung Laravel.

## Instalasi setelah clone

Jalankan perintah dari root project.

1. Pasang dependency PHP:

   ```bash
   composer install
   ```

2. Salin `.env.example` menjadi `.env`:

   ```bash
   # macOS / Linux / Git Bash
   cp .env.example .env
   ```

   Di PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

   Jika `.env` sudah ada, jangan ditimpa; gunakan konfigurasi lokal yang ada.

3. Buat application key:

   ```bash
   php artisan key:generate
   ```

4. Buat file database SQLite lokal (default pada `.env.example`):

   ```bash
   php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
   ```

   Jika memilih MySQL atau PostgreSQL, buat database lalu isi variabel `DB_*` di `.env`. Jangan commit `.env`.

5. Buat tabel dan data awal:

   ```bash
   php artisan migrate --seed
   ```

   Seeder membuat akun admin lokal:

   - Email: `admin@gmail.com`
   - Password: `1234`

   Kredensial ini hanya untuk pengembangan lokal. Ganti password segera dan **jangan gunakan akun/sandi bawaan ini pada website publik**.

6. Pasang dependency frontend dan buat aset:

   ```bash
   npm ci
   npm run build
   ```

7. Buat symbolic link untuk file pada disk publik lokal:

   ```bash
   php artisan storage:link
   ```

## Menjalankan aplikasi lokal

Jalankan server Laravel:

```bash
php artisan serve
```

Untuk hot reload frontend, jalankan perintah berikut di terminal lain:

```bash
npm run dev
```

Buka alamat lokal yang ditampilkan Laravel (umumnya `http://127.0.0.1:8000`).

## Catatan database dan data awal

- `php artisan migrate` menerapkan migrasi yang belum dijalankan tanpa menghapus data.
- `php artisan migrate:fresh --seed` menghapus semua tabel lalu membuatnya ulang. Gunakan hanya pada database lokal yang boleh dikosongkan.
- Jika mengganti database, pastikan database telah dibuat dan dapat diakses sebelum migrasi.
- Dependency ekspor XLSX (`maatwebsite/excel`) sudah tercantum di `composer.json` dan akan dipasang oleh `composer install`.

## Catatan deployment

- Jangan unggah atau commit `.env`. Atur environment variables dan `APP_KEY` melalui penyedia hosting.
- Untuk produksi, gunakan database persisten dan atur setidaknya `APP_ENV=production`, `APP_DEBUG=false`, serta `APP_URL` ke domain website.
- Seeder admin saat ini menggunakan kredensial tetap. Sebelum go-live, ubah cara pembuatan kredensial atau ganti password bawaan dengan aman; jangan jalankan seeder ini di database publik tanpa menanganinya.
- Upload foto produk menggunakan disk publik Laravel. Hosting perlu menyediakan storage persisten atau object storage; filesystem sementara/serverless bukan tempat permanen untuk upload.
- Cara deploy bergantung pada penyedia hosting. Panduan instalasi lokal ini bukan konfigurasi deploy Vercel.

## Teknologi

- Laravel 13
- PHP 8.3+
- Blade
- Tailwind CSS 4 dan Vite
- Laravel Excel (`maatwebsite/excel`)
