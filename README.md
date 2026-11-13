# SIPEBRI (Sistem Pemberian Kredit)

<p align="center">
    <a href="https://bprbangunarta.co.id">
        <img src="https://bprbangunarta.co.id/api/assets/logo?v=9c3fd6f7d8" width="400" alt="BPR Bangunarta">
    </a>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white" alt="PHP Version">
    <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Node.js-22-339933?logo=nodedotjs&logoColor=white" alt="Node.js Version">
    <img src="https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white" alt="SQLite Version">
    <img src="https://img.shields.io/badge/Redis-8-DC382D?logo=redis&logoColor=white" alt="Redis Version">
</p>

## Tentang
SIPEBRI (Sistem Pemberian Kredit) adalah platform pengelolaan proses pemberian kredit BPR Bangunarta yang dirancang untuk mendukung seluruh tahapan pengajuan hingga persetujuan kredit. Platform ini membantu mengelola data calon debitur, dokumentasi persyaratan, analisis kelayakan, serta proses administrasi kredit agar berjalan lebih terstruktur, efisien, dan terdokumentasi.

## Fitur Utama
- **Pengajuan Kredit:** Pengelolaan dan pencatatan data pengajuan kredit calon debitur.
- **Penjadwalan Survey:** Pengaturan jadwal dan penugasan petugas survey kepada calon debitur.
- **Survey Pengajuan:** Pencatatan hasil survey dan verifikasi informasi calon debitur.
- **Analisa Kredit:** Pengolahan dan analisis kelayakan kredit berdasarkan hasil survey dan data pendukung.
- **Persetujuan Komite:** Pengelolaan proses evaluasi dan pengambilan keputusan kredit oleh komite.

## Dependensi
| **Package** | **Versi** | **Fungsi** | **Dokumentasi** |
| :--- | :--- | :--- | :--- |
| `league/flysystem-aws-s3-v3` | 3.0 | Integrasi penyimpanan berkas dengan Amazon S3 dan layanan kompatibel. | [Klik](https://laravel.com/docs/12.x/filesystem) |
| `league/flysystem-sftp-v3` | 3.0 | Integrasi penyimpanan berkas melalui protokol SFTP. | [Klik](https://laravel.com/docs/12.x/filesystem) |
| `predis/predis` | 3.7 | Komunikasi antara aplikasi PHP dan Redis. | [Klik](https://github.com/predis/predis) |
| `spatie/laravel-permission` | 8.5 | Pengelolaan peran dan hak akses pengguna (Role & Permission). | [Klik](https://spatie.be/docs/laravel-permission/v8/introduction) |
| `spatie/laravel-activitylog` | 5.1 | Pencatatan aktivitas dan perubahan data dalam aplikasi. | [Klik](https://spatie.be/docs/laravel-activitylog/v5/introduction) |
| `laravel-lang/common` | 6.9 | Otomatisasi pesan validasi menggunakan bahasa Indonesia. | [Klik](https://laravel-lang.com/packages-common.html) |
| `inertiajs/inertia-laravel` | 3.7 | Penghubung Laravel dengan halaman React tanpa API terpisah (Inertia). | [Klik](https://inertiajs.com/docs/v3/installation/server-side-setup) |
| `laravel/wayfinder` | 0.1 | Membuat fungsi TypeScript untuk URL dari route Laravel, sehingga frontend tidak menulis URL sebagai string. | [Klik](https://github.com/laravel/wayfinder) |

Frontend memakai React 19, TypeScript, Tailwind CSS 4, dan Vite 8. Komponen, token tema, dan aturan tampilannya berasal dari template react-starter (lihat `.ai/rules/frontend-react.md` dan halaman `/ui-guideline`). Alat pengembangan: `laravel/boost`, Pest, Larastan, Pint, ESLint, Prettier, Vitest (dengan `vitest-axe` untuk aksesibilitas), Playwright (tes ujung ke ujung), dan `tsx` (pembuat halaman error).

## Menjalankan
Prasyarat: PHP 8.3 atau lebih baru (dengan ekstensi SQLite), Composer, dan Node.js (dikembangkan dengan versi 22). Database bawaan SQLite (`database/database.sqlite`); ganti `DB_CONNECTION` di `.env` bila memakai database lain. Redis opsional: `.env.example` memakai `database` untuk sesi, antrean, dan cache.

```bash
composer setup
```

```bash
composer dev
```

`composer setup` memasang dependensi, membuat `.env` dari `.env.example`, membuat kunci aplikasi, menjalankan migrasi, lalu membangun aset. `composer dev` menjalankan server, antrean, log, dan Vite bersamaan.

## Kata sandi
Kata sandi minimal 8 karakter, kedaluwarsa setelah 30 hari (`PASSWORD_MAX_AGE_DAYS`), dan tidak boleh sama dengan 3 kata sandi terakhir. Akun baru dan akun yang kata sandinya diatur ulang admin memakai `DEFAULT_USER_PASSWORD` dari `.env` dan wajib menggantinya saat masuk pertama. Isi nilainya di `.env` asli; `.env.example` sengaja kosong.

## Masuk dan sesi
Halaman `/login` menerima email atau username. Semua halaman lain hanya untuk yang sudah masuk. Percobaan gagal dibatasi 5 per akun dan alamat IP, dengan jeda yang bertambah (1 menit, lalu dua kali lipat sampai 16 menit). Pengaturan sesi di `.env`:

| Variabel | Arti | Bawaan |
| :--- | :--- | :--- |
| `SESSION_LIFETIME` | Menit tanpa aktivitas sebelum sesi berakhir. | 15 |
| `SESSION_ABSOLUTE_LIFETIME` | Umur maksimal sesi dalam menit, seaktif apa pun. | 720 |
| `SESSION_SECURE_COOKIE` | Cookie sesi hanya lewat HTTPS. Wajib `true` di production. | tidak diisi |

## Audit log
Perubahan data pengguna dan kejadian masuk, gagal masuk, terkunci, dan keluar tercatat otomatis dan bisa dilihat di `/audit-logs`. Catatan tidak bisa diubah atau dihapus dari aplikasi. `activitylog:clean` menghapus catatan yang lebih tua dari `ACTIVITYLOG_CLEAN_AFTER_DAYS` (bawaan 1826 hari, 5 tahun) dan berjalan harian lewat scheduler, jadi server memerlukan cron `* * * * * php artisan schedule:run`. Model bisnis baru memakai trait `App\Models\Concerns\Audited`.

## Deploy dan data awal
Deploy production menjalankan `composer install --no-dev`, `php artisan migrate --force`, `php artisan roles:sync-base`, dan `php artisan optimize`. Seeder tidak dijalankan di production; data kantor di sana dimasukkan lewat menu Data Kantor. `roles:sync-base` membuat role dasar dan aman dijalankan berulang kali.

Unggahan lewat pengelola berkas paling besar 10 MB, jadi `upload_max_filesize` dan `post_max_size` PHP, serta batas isi permintaan server web, harus sedikitnya 10 MB.

`APP_URL` harus berisi alamat aplikasi yang sebenarnya: selain di environment `local`, aplikasi hanya menjawab permintaan untuk host itu dan subdomainnya (`trustHosts()`), dan menjawab 400 untuk host lain.

Layar Penyimpanan menyimpan konfigurasi S3 dan SFTP yang menggantikan bawaan `.env`. Host yang diisi di sana harus beralamat publik; host di dalam jaringan (misalnya MinIO) harus didaftarkan di `STORAGE_ALLOWED_HOSTS` (dipisah koma).

Di lokal, `php artisan db:seed` membuat role, satu kantor contoh (`OfficeSeeder`, tidak menimpa kantor yang sudah diubah), dan dua akun demo (`superadmin` dan `guest`) dengan password bawaan `UserFactory`. Akun demo hanya dibuat bila `SEED_DEMO_ACCOUNTS=true`, yang sudah menyala di `.env.example`. Variabel itu tidak boleh diaktifkan di production.

## Menguji dan pemeriksaan kualitas
Test PHP berjalan di SQLite dalam memori (`phpunit.xml`), jadi tidak butuh database terpisah dan database kerja tidak pernah tersentuh. Tes ujung ke ujung (Playwright) memakai berkas SQLite sendiri, `database/e2e.sqlite`.

| Perintah | Isi |
| :--- | :--- |
| `composer test` | Test PHP (Pest). |
| `vendor/bin/pint` | Merapikan kode PHP. |
| `vendor/bin/phpstan analyse` | Analisis statis PHP (Larastan). |
| `npm run check` | Typecheck, ESLint, Prettier, pemeriksa standar, dan test frontend (Vitest). |
| `npm run build` | Membangun aset produksi, lalu memeriksa anggaran ukuran bundel (150 kB gzip). |
| `npm run test:e2e` | Tes ujung ke ujung (Playwright, Chromium) terhadap aplikasi sungguhan dan database uji. Butuh `npm run build` lebih dulu dan `npx playwright install`. `npm run test:e2e:all` menambah Firefox dan WebKit. |
| `npm run error-pages` | Membuat ulang halaman error (`resources/views/errors/*.blade.php`) dari kata, ilustrasi, dan warna aplikasi. |
| `npm run rebrand -- --name "Nama" --color "#rrggbb"` | Mengganti nama dan warna utama (terang dan gelap), warna email OTP, dan halaman error. Logo dan favicon diganti manual. |

## Kontributor
Pengembangan dan pemeliharaan aplikasi ini dilakukan oleh tim Teknologi Informasi BPR Bangunarta.

| Nama | Peran |
|---|---|
| Zulfadli Rizal | Developer |


## Lisensi
Program ini dilisensikan berdasarkan [Commercial License](./LICENSE). Seluruh hak cipta dan kepemilikan atas program ini tetap berada pada pemegang hak cipta. Penggunaan, penyalinan, modifikasi, dan distribusi program diatur berdasarkan ketentuan yang tercantum dalam lisensi tersebut.