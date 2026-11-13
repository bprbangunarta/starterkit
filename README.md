# STARTERKIT

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white" alt="PHP Version">
    <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Node.js-22-339933?logo=nodedotjs&logoColor=white" alt="Node.js Version">
    <img src="https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white" alt="SQLite Version">
    <img src="https://img.shields.io/badge/Redis-opsional-DC382D?logo=redis&logoColor=white" alt="Redis">
</p>

## Tentang
STARTERKIT adalah titik awal proyek Laravel 13 dengan Inertia dan React yang sudah memuat dasar aplikasi internal: masuk dan sesi yang aman, kelola pengguna dan jabatan, catatan audit, penyimpanan berkas, pengaturan proyek, dan sistem tampilan yang seragam. Aturan penulisan kode, pemeriksaan otomatis, dan tesnya ikut disertakan, jadi proyek turunan mulai dari standar yang sama.

## Fitur Bawaan
- **Masuk dan sesi:** email atau username, batas percobaan bertingkat, sesi berakhir saat diam atau mencapai umur maksimal, dan konfirmasi kata sandi untuk aksi sensitif.
- **Kelola Pengguna dan Data Jabatan:** tambah, ubah, hapus, dan pulihkan pengguna; jabatan (role) dikelola Super Admin.
- **Data Kantor:** contoh data referensi lengkap dengan datatable, filter, dan relasi ke pengguna.
- **Audit Logs:** perubahan data dan kejadian keamanan tercatat otomatis, bisa disaring, dilihat detailnya, dan diekspor ke CSV lewat antrean.
- **Penyimpanan:** daftar penyimpanan Lokal, Amazon S3, dan SFTP dengan konfigurasi dan uji koneksi dari layar, serta pengelola berkas (daftar, pratinjau, unduh, unggah, ubah nama, hapus).
- **Pengaturan:** nama aplikasi, meta deskripsi, logo, favicon, dan penyimpanan aktif yang bisa diubah tanpa mengubah kode.
- **Profil:** data akun dan ubah kata sandi sendiri.
- **UI Guideline** (`/ui-guideline`): acuan hidup semua komponen, token tema (terang dan gelap), dan contoh CRUD.
- Halaman error dan email kode masuk yang bermerek, peringatan sesi habis, dan halaman pemeliharaan.

## Dependensi
| **Package** | **Versi** | **Fungsi** | **Dokumentasi** |
| :--- | :--- | :--- | :--- |
| `laravel/framework` | 13 | Kerangka aplikasi. | [Klik](https://laravel.com/docs/13.x) |
| `inertiajs/inertia-laravel` | 3.7 | Penghubung Laravel dengan halaman React tanpa API terpisah (Inertia). | [Klik](https://inertiajs.com/docs/v3/installation/server-side-setup) |
| `laravel/wayfinder` | 0.1 | Membuat fungsi TypeScript untuk URL dari route Laravel, sehingga frontend tidak menulis URL sebagai string. | [Klik](https://github.com/laravel/wayfinder) |
| `spatie/laravel-permission` | 8.5 | Pengelolaan peran dan hak akses pengguna (Role & Permission). | [Klik](https://spatie.be/docs/laravel-permission/v8/introduction) |
| `spatie/laravel-activitylog` | 5.1 | Pencatatan aktivitas dan perubahan data dalam aplikasi. | [Klik](https://spatie.be/docs/laravel-activitylog/v5/introduction) |
| `league/flysystem-aws-s3-v3` | 3.35 | Integrasi penyimpanan berkas dengan Amazon S3 dan layanan kompatibel. | [Klik](https://laravel.com/docs/13.x/filesystem) |
| `league/flysystem-sftp-v3` | 3.36 | Integrasi penyimpanan berkas melalui protokol SFTP. | [Klik](https://laravel.com/docs/13.x/filesystem) |
| `laravel-lang/common` | 6.9 | Pesan validasi dan terjemahan bahasa Indonesia. | [Klik](https://laravel-lang.com/packages-common.html) |
| `predis/predis` | 3.7 | Komunikasi antara aplikasi PHP dan Redis. | [Klik](https://github.com/predis/predis) |

Frontend memakai React 19, TypeScript, Tailwind CSS 4, dan Vite 8. Komponen, token tema, dan aturan tampilannya ada di `.ai/rules/frontend-react.md` dan halaman `/ui-guideline`. Alat pengembangan: `laravel/boost`, Pest, Larastan, Pint, ESLint, Prettier, Vitest (dengan `vitest-axe` untuk aksesibilitas), Playwright (tes ujung ke ujung), dan `tsx` (pembuat halaman error).

## Memulai
Prasyarat: PHP 8.3 atau lebih baru (dengan ekstensi SQLite), Composer, dan Node.js (dikembangkan dengan versi 22). Database bawaan SQLite (`database/database.sqlite`); ganti `DB_CONNECTION` di `.env` bila memakai database lain. Redis opsional: `.env.example` memakai `database` untuk sesi, antrean, dan cache.

```bash
composer setup
```

```bash
composer dev
```

`composer setup` memasang dependensi, membuat `.env` dari `.env.example`, membuat kunci aplikasi, menjalankan migrasi, lalu membangun aset. `composer dev` menjalankan server, antrean, log, dan Vite bersamaan.

Isi data awal untuk lokal dengan `php artisan db:seed`: role dasar, satu kantor contoh, dan dua akun demo (`superadmin` dan `guest`, kata sandi bawaan `UserFactory`). Akun demo hanya dibuat bila `SEED_DEMO_ACCOUNTS=true` (menyala di `.env.example`) dan hanya untuk lokal. Role dasar bisa dibuat ulang kapan saja dengan `php artisan roles:sync-base`, aman diulang.

### Menjadikannya proyek sendiri
1. Ganti merek: `npm run rebrand -- --name "Nama Proyek" --color "#rrggbb"` (nama, warna utama terang dan gelap, warna email, halaman error, dan `package.json`). Samakan `APP_NAME` di `.env`.
2. Ganti `public/logo.png` dan `public/favicon.ico` (atau unggah dari menu Pengaturan), lalu jalankan `npm run error-pages`.
3. Ubah nama paket di `composer.json`, tulis ulang `memory/PRD.md` untuk produknya, dan kosongkan `docs/` yang bukan milik proyek barumu.
4. Isi `DEFAULT_USER_PASSWORD` di `.env` agar akun baru bisa dibuat.

## Kata sandi
Kata sandi minimal 8 karakter, kedaluwarsa setelah 30 hari (`PASSWORD_MAX_AGE_DAYS`), dan tidak boleh sama dengan 3 kata sandi terakhir. Akun baru dan akun yang kata sandinya diatur ulang admin memakai `DEFAULT_USER_PASSWORD` dari `.env` dan wajib menggantinya saat masuk pertama. Isi nilainya di `.env`; `.env.example` sengaja kosong. Tanpa nilai itu, membuat akun baru dari layar gagal.

## Masuk dan sesi
Halaman `/login` menerima email atau username. Semua halaman lain hanya untuk yang sudah masuk. Percobaan gagal dibatasi 5 per akun dan alamat IP, dengan jeda yang bertambah (1 menit, lalu dua kali lipat sampai 16 menit). Pengaturan sesi di `.env`:

| Variabel | Arti | Bawaan |
| :--- | :--- | :--- |
| `SESSION_LIFETIME` | Menit tanpa aktivitas sebelum sesi berakhir. | 15 |
| `SESSION_ABSOLUTE_LIFETIME` | Umur maksimal sesi dalam menit, seaktif apa pun. | 720 |
| `SESSION_SECURE_COOKIE` | Cookie sesi hanya lewat HTTPS. | tidak diisi |

## Audit log
Perubahan data pengguna dan kejadian masuk, gagal masuk, terkunci, dan keluar tercatat otomatis dan bisa dilihat di `/audit-logs`. Catatan tidak bisa diubah atau dihapus dari aplikasi. `activitylog:clean` menghapus catatan yang lebih tua dari `ACTIVITYLOG_CLEAN_AFTER_DAYS` (bawaan 1826 hari, 5 tahun) dan berjalan harian lewat scheduler (`* * * * * php artisan schedule:run`). Model baru memakai trait `App\Models\Concerns\Audited`.

## Konfigurasi penting
- `APP_URL` harus berisi alamat aplikasi yang sebenarnya: selain di environment `local`, aplikasi hanya menjawab permintaan untuk host itu dan subdomainnya (`trustHosts()`), dan menjawab 400 untuk host lain.
- Unggahan lewat pengelola berkas paling besar 10 MB, jadi `upload_max_filesize` dan `post_max_size` PHP, serta batas isi permintaan server web, harus sedikitnya 10 MB.
- Layar Penyimpanan menyimpan konfigurasi S3 dan SFTP yang menggantikan bawaan `.env`. Host yang diisi di sana harus beralamat publik; host di jaringan internal (misalnya MinIO) harus didaftarkan di `STORAGE_ALLOWED_HOSTS` (dipisah koma).
- Semua jawaban aplikasi membawa header keamanan (`SetSecurityHeaders`), dan jawaban untuk orang yang sudah masuk tidak boleh disimpan cache.

## Menguji dan pemeriksaan kualitas
Test PHP berjalan di SQLite dalam memori (`phpunit.xml`), jadi tidak butuh database terpisah dan database kerja tidak pernah tersentuh. Tes ujung ke ujung (Playwright) memakai berkas SQLite sendiri, `database/e2e.sqlite`.

| Perintah | Isi |
| :--- | :--- |
| `composer test` | Test PHP (Pest). |
| `vendor/bin/pint` | Merapikan kode PHP. |
| `vendor/bin/phpstan analyse` | Analisis statis PHP (Larastan). |
| `npm run check` | Typecheck, ESLint, Prettier, pemeriksa standar, dan test frontend (Vitest). |
| `npm run build` | Membangun aset, lalu memeriksa anggaran ukuran bundel (150 kB gzip). Test PHP butuh hasil build ini. |
| `npm run test:e2e` | Tes ujung ke ujung (Playwright, Chromium) terhadap aplikasi sungguhan dan database uji. Butuh `npm run build` lebih dulu dan `npx playwright install`. `npm run test:e2e:all` menambah Firefox dan WebKit. |
| `npm run error-pages` | Membuat ulang halaman error (`resources/views/errors/*.blade.php`) dari kata, ilustrasi, dan warna aplikasi. |
| `npm run rebrand -- --name "Nama" --color "#rrggbb"` | Mengganti nama dan warna utama (terang dan gelap), warna email OTP, dan halaman error. Logo dan favicon diganti manual. |

Aturan penulisan kode ada di `.ai/rules` (diindeks `.ai/rules/index.md`), keputusan dan pengecualiannya di `docs/decisions.md`, dan status review fitur di `docs/review-status.md`.

## Kontributor
| Nama | Peran |
|---|---|
| Zulfadli Rizal | Developer |
