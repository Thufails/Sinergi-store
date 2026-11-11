<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

</laravel-boost-guidelines>

## Catatan transisi Sinergi Store

Diperbarui: 2026-11-11

### Alur kerja yang disepakati

- Sebelum mengubah atau menambah fitur, cek dulu apakah skema database atau model perlu diubah.
- Jika perlu migrasi, siapkan migrasi/model terlebih dahulu lalu berhenti agar pemilik proyek bisa menjalankan `php artisan migrate`. Lanjutkan controller/CRUD dan route setelah pemilik mengonfirmasi migrasi selesai.
- Setelah controller dan route selesai, berhenti agar pemilik bisa mengecek perubahan di VS Code sebelum mengubah Blade. Jika suatu pekerjaan hanya memerlukan Blade, jelaskan hal itu.
- Jangan menjalankan tes otomatis kecuali diminta secara eksplisit. Pemilik proyek akan mengecek perilaku fitur secara manual.
- Kerjakan bertahap dan jelaskan file yang diubah beserta alasannya.

### Status chat pembeli dan FAQ

- Percakapan dan pesan produk tersimpan di tabel `product_conversations` dan `product_conversation_messages`. Pemilik proyek sudah menjalankan migrasi chat yang dibuat pada 2026-11-05; jangan membuat ulang migrasi tersebut.
- Chat produk memiliki inbox admin, riwayat percakapan pembeli, balasan otomatis tentang stok/varian, indikator pesan belum dibaca, serta alur menyelesaikan dan membuka kembali percakapan.
- Stok tersedia dihitung oleh `ProductAvailabilityService`, termasuk dengan memperhitungkan stok yang dipesan oleh pesanan yang masih menunggu pembayaran.
- Inbox pembeli serta route untuk memulai, membaca, dan mengirim chat produk mewajibkan guard `customer`. Chat tamu yang dibuat sebelum login akan ditautkan ke akun saat login/daftar jika token tamunya masih ada di session browser.
- Saat percakapan dimulai, baris produk dikunci selama pemeriksaan/pembuatan chat aktif untuk mengurangi percakapan ganda akibat pembukaan yang bersamaan.
- Setelah login/daftar, pembeli diarahkan kembali ke halaman produk dan dialog chat dibuka otomatis. Dialog juga menampilkan status jika polling pesan mengalami gangguan koneksi.
- FAQ masih menggunakan aturan keyword yang deterministik; integrasi AI/LLM belum diperlukan. FAQ dapat menampilkan stok produk aktif yang namanya disebut pembeli serta daftar produk aktif dalam kategori yang disebut.

### Perubahan terbaru yang sudah di-commit

- 2026-11-10 (`d5c89be`): mewajibkan login untuk percakapan; menjaga tujuan kembali yang aman; menautkan chat tamu lama ke akun; mencegah pembuatan chat ganda saat pembukaan bersamaan.
- 2026-11-10 (`58943f8`): menampilkan nama admin dan keterangan `Admin Sinergi` pada balasan di chat pembeli.
- 2026-11-10 (`1239cf6`, `a58c016`): membuat jawaban FAQ stok/kategori mengambil data produk dan kategori terkini dari database.
- 2026-11-06 (`4eaca20`, `ac1a146`): membangun chat produk dan merapikan jawaban FAQ.

### Pengecekan dan pengembangan berikutnya

- Pemilik proyek perlu mengecek manual alur login dan daftar: setelah masuk, pembeli kembali ke produk semula dan dialog chat terbuka. Akses langsung ke inbox pembeli juga harus mengarahkan pengunjung ke login.
- Cek chat tamu lama muncul di akun setelah login, serta membuka ulang chat produk memakai percakapan aktif yang sama.
- Pengembangan opsional berikutnya: pencarian/filter inbox admin dan penanganan sesi kedaluwarsa yang lebih jelas. Tunda sampai ada kendala nyata dalam pemakaian.
- AI/RAG tetap menjadi opsi masa depan, bukan kebutuhan saat ini.
