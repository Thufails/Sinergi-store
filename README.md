<p align="center">
  <img src="public/images/logo-sinergi.png" alt="Logo Sinergi Store" width="120">
</p>

<h1 align="center">Sinergi Store</h1>

<p align="center"><strong>Katalog dan pemesanan, dikelola lebih mudah.</strong></p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3 atau lebih baru">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-build-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

<p align="center">
  Halaman publik · Katalog produk · Pemesanan · Dashboard admin · Ekspor XLSX · Testimoni
</p>

<p align="center">
  <a href="#instalasi-setelah-clone">Mulai instalasi</a> ·
  <a href="#menjalankan-aplikasi-lokal">Jalankan lokal</a> ·
  <a href="#catatan-deployment">Catatan deployment</a>
</p>

---

## 🧰 Persyaratan

- PHP **8.3** atau lebih baru sesuai batas versi project (`^8.3`), dengan ekstensi yang diperlukan Laravel dan Composer.
- Composer.
- Node.js dan npm.
- MySQL Server dan client/GUI untuk mengelola database (contoh: MySQL Workbench).

## 🚀 Instalasi setelah clone

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

4. Buat database MySQL kosong, misalnya `sinergi_store`, melalui MySQL Workbench atau client MySQL. Lalu sesuaikan bagian database di `.env`:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sinergi_store
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Ganti username dan password sesuai akun MySQL lokalmu. Template `.env.example` saat ini masih mencontohkan SQLite, jadi pastikan nilai `DB_*` di `.env` sudah diubah ke MySQL sebelum menjalankan migrasi. Jangan commit `.env`.

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

## 💻 Menjalankan aplikasi lokal

Jalankan server Laravel:

```bash
php artisan serve
```

Untuk hot reload frontend, jalankan perintah berikut di terminal lain:

```bash
npm run dev
```

Buka alamat lokal yang ditampilkan Laravel (umumnya `http://127.0.0.1:8000`).

## 🗃️ Catatan database dan data awal

- `php artisan migrate` menerapkan migrasi yang belum dijalankan tanpa menghapus data.
- `php artisan migrate:fresh --seed` menghapus semua tabel lalu membuatnya ulang. Gunakan hanya pada database lokal yang boleh dikosongkan.
- Pastikan server MySQL aktif dan database yang dipilih sudah dibuat serta dapat diakses sebelum migrasi.
- Dependency ekspor XLSX (`maatwebsite/excel`) sudah tercantum di `composer.json` dan akan dipasang oleh `composer install`.

## ☁️ Catatan deployment

- Jangan unggah atau commit `.env`. Atur environment variables dan `APP_KEY` melalui penyedia hosting.
- Untuk produksi, gunakan database persisten dan atur setidaknya `APP_ENV=production`, `APP_DEBUG=false`, serta `APP_URL` ke domain website.
- Seeder admin saat ini menggunakan kredensial tetap. Sebelum go-live, ubah cara pembuatan kredensial atau ganti password bawaan dengan aman; jangan jalankan seeder ini di database publik tanpa menanganinya.
- Upload foto produk menggunakan disk publik Laravel. Hosting perlu menyediakan storage persisten atau object storage; filesystem sementara/serverless bukan tempat permanen untuk upload.
- Cara deploy bergantung pada penyedia hosting. Panduan instalasi lokal ini bukan konfigurasi deploy Vercel.

## 🛠️ Teknologi

- Laravel 13
- PHP 8.3+
- Blade
- Tailwind CSS 4 dan Vite
- Laravel Excel (`maatwebsite/excel`)
