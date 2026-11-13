# Catatan keputusan

Keputusan D-0001 sampai D-0010 berasal dari standar boosts-guidelines dan berlaku di proyek ini sejak diadopsi. Jika proyek ini perlu menyimpang, tambahkan entri baru yang menggantikannya.

Tempat mencatat keputusan, risiko yang sengaja diterima, pengecualian aturan, dan utang teknis. Setiap entri menyebut apa yang diputuskan, alasannya, siapa yang memutuskan, kapan, dan kapan ditinjau ulang. Entri tidak dihapus. Jika keputusan berubah, tambahkan entri baru yang merujuk entri lama, lalu ubah status entri lama menjadi "digantikan".

Agent hanya boleh menulis entri berstatus `diusulkan` dengan "Diputuskan oleh: menunggu pemilik". Status `berlaku` ditulis setelah user menyetujuinya di percakapan. Reviewer tidak menganggap entri `diusulkan` sebagai izin menyimpang dari rule.

Format entri:

```markdown
## D-NNNN: Judul singkat

- Tanggal: YYYY-MM-DD
- Status: diusulkan | berlaku | digantikan oleh D-NNNN
- Diputuskan oleh: nama pemilik atau user, atau "menunggu pemilik" untuk entri diusulkan
- Keputusan: ...
- Alasan: ...
- Dampak untuk proyek ini: diisi saat adopsi.
- Tinjau ulang: YYYY-MM-DD
```

## D-0001: Kebijakan password mengikuti NIST SP 800-63B

- Tanggal: 2026-11-11
- Status: digantikan oleh D-0031
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: Minimal 15 karakter jika password satu-satunya faktor (8 jika 2FA diwajibkan), tanpa aturan komposisi, dan dicek terhadap daftar password bocor. Aturan ada di `.ai/rules/auth.md`.
- Alasan: Aturan komposisi (wajib huruf besar, angka, simbol) mendorong password yang mudah ditebak. Panjang password lebih menentukan keamanan.
- Dampak untuk proyek ini: Sejak 2026-11-11 `Password::defaults()` terpasang di `AppServiceProvider` (minimal 15, maksimal 128, cek kebocoran hanya di production) dan dipakai alur ubah kata sandi di Profil. Teks asli: belum ada `Password::defaults()` dan belum ada alur autentikasi. Saat login dibuat, kebijakan ini wajib dipasang di `AppServiceProvider::boot()`. Akun demo di `UserSeeder` memakai password bawaan factory dan hanya dibuat bila `SEED_DEMO_ACCOUNTS` aktif, yang default-nya mati (D-0019).
- Tinjau ulang: 2027-05-11

## D-0002: Respons sama untuk akun yang ada dan tidak ada

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: Login, registrasi, reset password, dan pemulihan akun tidak membocorkan apakah sebuah email terdaftar.
- Alasan: Mencegah pihak luar mengumpulkan daftar akun yang valid.
- Dampak untuk proyek ini: Belum ada alur login, registrasi, atau reset password, jadi belum ada yang perlu disesuaikan. Berlaku untuk alur yang dibuat nanti.
- Tinjau ulang: 2027-05-11

## D-0003: Batas sesi untuk aplikasi berdata sensitif

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: Aplikasi yang menyimpan data sensitif memakai idle timeout 15 menit dan umur sesi maksimal 12 jam. Aplikasi lain boleh melonggarkan dengan entri keputusan tersendiri.
- Alasan: Membatasi dampak sesi yang tertinggal terbuka atau dicuri.
- Dampak untuk proyek ini: `SESSION_LIFETIME=120` (120 menit) di `.env.example`, belum sesuai idle timeout 15 menit, dan umur maksimal 12 jam belum diatur. Aplikasi menyimpan data debitur kredit (sensitif). Penyesuaian dilakukan saat fitur login dibuat.
- Tinjau ulang: 2027-05-11

## D-0004: 2FA wajib untuk akun berhak istimewa

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: 2FA wajib untuk admin dan operator, dan opsional untuk user biasa kecuali proyek menetapkan lain.
- Alasan: Akun berhak istimewa paling berbahaya jika diambil alih, sedangkan mewajibkan 2FA untuk semua user menambah hambatan yang belum tentu perlu.
- Dampak untuk proyek ini: Role yang ada: `Super Admin` dan `Guest` (lewat `RoleSeeder`). 2FA belum ada dan belum diwajibkan untuk Super Admin. Perlu diputuskan bersama pemilik saat login dibuat.
- Tinjau ulang: 2027-05-11

## D-0005: Error API membawa field `code`

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: Setiap body error API memakai format bawaan Laravel (`message`, `errors`) ditambah `code` yang stabil dari satu katalog. Aturan ada di `.ai/rules/api.md`.
- Alasan: Klien (aplikasi mobile, integrasi) butuh kode yang tidak berubah walaupun teks pesan berubah atau diterjemahkan.
- Dampak untuk proyek ini: Belum ada API. Berlaku untuk endpoint JSON yang dibuat nanti. Halaman Inertia memakai pesan validasi bawaan Laravel.
- Tinjau ulang: 2027-05-11

## D-0006: Jejak audit bersifat opsional

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: Jejak audit tidak wajib secara default. Proyek yang membutuhkannya menambahkan entri keputusan beserta alat yang dipakai, lalu mengikuti `.ai/rules/audit.md`.
- Alasan: Jejak audit penuh butuh paket baru atau sistem sendiri, dan kebutuhannya bergantung pada jenis aplikasi.
- Dampak untuk proyek ini: `spatie/laravel-activitylog` sudah terpasang dan `ACTIVITYLOG_ENABLED=true`, jadi jejak audit tersedia, tetapi belum dipakai di model mana pun. Aplikasi kredit kemungkinan membutuhkannya, perlu entri keputusan tersendiri saat fitur pertama dibuat.
- Tinjau ulang: 2027-05-11

## D-0007: Batas ukuran kode sebagai pedoman

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: Batas method 80 baris, nesting 4 tingkat, dan file 400 baris berlaku sebagai pedoman yang diperiksa saat review.
- Alasan: Belum ada alat penegak yang terpasang, dan memasangnya berarti menambah dependensi.
- Dampak untuk proyek ini: Belum ada kode aplikasi yang bisa diukur. Larastan terpasang (`phpstan.neon`, level 5). Batas ukuran tetap pedoman review.
- Tinjau ulang: 2027-05-11

## D-0008: Coverage 80% sebagai target

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: Kode baru menargetkan coverage minimal 80%. Pengukuran di CI diaktifkan belakangan.
- Alasan: Pengukuran coverage butuh pcov atau xdebug di CI.
- Dampak untuk proyek ini: Belum ada pcov atau xdebug terpasang dan belum ada CI. Pengukuran coverage ditunda.
- Tinjau ulang: 2027-05-11

## D-0009: Percabangan berdasarkan environment hanya di service provider

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: `app()->isProduction()` dan sejenisnya hanya dipakai di service provider untuk pengaturan global. Di tempat lain, perbedaan perilaku datang dari config.
- Alasan: Mengikuti konvensi Laravel untuk pengaturan global, sambil menjaga kode aplikasi tetap bisa diuji tanpa bergantung pada nama environment.
- Dampak untuk proyek ini: Satu-satunya pemakaian saat ini adalah `Gate::before` di `AppServiceProvider`, yang tidak bercabang menurut environment. Sudah sesuai.
- Tinjau ulang: 2027-05-11

## D-0010: ID publik tidak bisa ditebak

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: standar boosts-guidelines, diadopsi oleh pemilik proyek
- Keputusan: ID yang terlihat di URL atau API memakai ULID sebagai route key, atau setiap pencarian record selalu dibatasi per pemilik.
- Alasan: ID berurutan memudahkan pihak luar menebak dan mencoba mengakses data orang lain.
- Dampak untuk proyek ini: Tabel `users` memakai ID berurutan. Model domain kredit belum ada. Saat dibuat, pakai ULID sebagai route key atau batasi pencarian per pemilik.
- Tinjau ulang: 2027-05-11

## D-0011: Pest 4 dan PHPUnit 12 selama proyek di Laravel 12

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: Memakai Pest 4.7 dan PHPUnit 12 (`composer.json` memakai `phpunit/phpunit ^12`). Pest 5 tidak bisa dipasang di Laravel 12: semua rilis Pest 5 (v5.0.0 sampai v5.3.5) mensyaratkan `symfony/process ^8.1` dan PHP ^8.4, sedangkan Laravel 12 mensyaratkan `symfony/process ^7.2`. Itu diperiksa lewat metadata Composer dan `composer require --dry-run`. Pest 3.8 dipakai lebih dulu, lalu dinaikkan ke 4.7 karena kompatibel dan lebih baru.
- Alasan: Pest 4 adalah rilis terbaru yang kompatibel dengan Laravel 12. Notice deprecated dari plugin arch di PHP 8.5 (`ReflectionProperty::setAccessible()`) yang muncul di Pest 3.8 hilang di Pest 4.
- Dampak untuk proyek ini: test lolos tanpa notice deprecated. `composer audit` tidak menemukan kerentanan.
- Tinjau ulang: saat proyek naik ke Laravel 13 (Pest 5 menjadi mungkin)

## D-0012: Skill deploying-to-cloud dan integrasi Laravel Cloud dinonaktifkan

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: `cloud` di `boost.json` diset `false` dan skill `deploying-to-cloud` dihapus.
- Alasan: Aplikasi dijalankan di infrastruktur sendiri (MySQL, Redis, S3 atau SFTP), bukan Laravel Cloud.
- Dampak untuk proyek ini: agent tidak memuat panduan Laravel Cloud.
- Tinjau ulang: jika hosting berpindah ke Laravel Cloud

## D-0013: Dependensi Inertia React dan kenaikan mayor Vite 8

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: Memasang `inertiajs/inertia-laravel` 3.7, `@inertiajs/react`, `@inertiajs/vite`, `react` 19, `react-dom`, `typescript`, dan `@vitejs/plugin-react` 6. Menaikkan `vite` dari 7 ke 8 dan `laravel-vite-plugin` dari 2 ke 3 (kenaikan mayor), karena `@vitejs/plugin-react` 6 dan `laravel-vite-plugin` 3 mensyaratkan Vite 8. Memasang `laravel/wayfinder` 0.1 dan `@laravel/vite-plugin-wayfinder` 0.1 (lihat D-0014). Menghapus `axios` karena `bootstrap.js` bawaan skeleton tidak terpakai lagi.
- Alasan: Frontend dipilih memakai Inertia React, dan template react-starter yang akan diadopsi sudah memakai Vite 8, sehingga komponennya tidak perlu disesuaikan ke toolchain lama.
- Dampak untuk proyek ini: `npm run build` dan `npm run typecheck` lolos, `npm audit` bersih (0 temuan). `composer audit` tidak menemukan kerentanan. Dua paket abandoned (`dragon-code/contracts` dan `dragon-code/support`) sudah ada sebelumnya lewat `laravel-lang/common`, bukan dari fase ini. Wayfinder masih beta (0.x), API-nya bisa berubah sebelum 1.0.
- Tinjau ulang: 2027-05-11

## D-0014: Helper URL frontend memakai Laravel Wayfinder

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: URL di frontend dibuat dari fungsi TypeScript hasil generate Wayfinder (`@/routes`, `@/actions`), tidak pernah ditulis sebagai string. Hasil generate (`resources/js/actions`, `resources/js/routes`, `resources/js/wayfinder`) tidak di-commit dan tidak diedit manual.
- Alasan: Rule `frontend.md` mewajibkan URL dari nama route, dan dokumentasi Inertia memakai Wayfinder untuk `Link`, `Form`, dan `router`.
- Dampak untuk proyek ini: menu dan navigasi dari react-starter memakai fungsi Wayfinder, bukan path string. Wayfinder masih beta (0.x).
- Tinjau ulang: saat Wayfinder merilis 1.0

## D-0015: Cakupan adopsi react-starter

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: Dari react-starter dipindahkan `components/ui`, komponen umum (avatar, brand-mark, error-boundary, error-page, illustrations, network-status, auth-layout), `lib` murni (utils, format, mask, password, brand, error-pages), token di `resources/css/app.css`, halaman `ui-guideline`, aturan `STANDARDS.md` (menjadi `.ai/rules/frontend-react.md`), pemeriksa `scripts/check-standards.mjs`, serta konfigurasi ESLint, Prettier, dan Vitest. Dibuat ulang untuk Inertia: layout, menu (Wayfinder), dashboard, dan test penamaan menu. Tidak dipindahkan: `react-router`, `lib/http`, `lib/form`, `lib/resource`, `mock/`, `auth/session`, halaman login, MFA, profil, audit log, dan komponen yang bergantung pada API starter (avatar-editor, idle-timeout, maintenance-gate, notification-bell, reauth-dialog, password-fields). Itu dibuat saat fitur autentikasi dikerjakan dengan controller Laravel.
- Alasan: Komponen UI, token, dan aturan tidak bergantung pada API tiruan, sedangkan layar yang bergantung padanya baru bermakna setelah backend nyata ada, dan menyalinnya sekarang berarti membawa data tiruan ke kode aplikasi (dilarang `production-standards.md`).
- Dampak untuk proyek ini: `npm run check` (typecheck, lint, format, standar, test) menjadi pemeriksaan frontend di Stop gate. Merek masih bawaan starter (huruf "S", warna utama biru `oklch(0.45 0.16 265)`), karena aset merek BPR belum ditetapkan. `public/` sudah memuat `logo.png` dan `logo_full_*.webp`. `dashboard` dan `ui-guideline` masih publik karena belum ada login, dan harus masuk grup `auth` saat login dibuat. Override `minimatch` ^11 di `package.json` menutup temuan `npm audit` (ReDoS, hanya alat lint) di `@typescript-eslint/typescript-estree`.
- Tinjau ulang: saat fitur autentikasi dikerjakan

## D-0016: Pengecualian batas 80 baris per fungsi untuk tiga komponen template

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), dikonfirmasi khusus untuk pengecualian ini di percakapan 2026-11-11 setelah diajukan sebagai rekomendasi
- Keputusan: `Combobox` (149 baris), `DataTable` (123), dan `FileInput` (85) di `resources/js/components/ui` dipakai apa adanya dari react-starter dan dikecualikan dari batas 80 baris per fungsi. Pemeriksa `scripts/check-standards.mjs` menegakkan batas itu untuk semua fungsi lain, dan daftar pengecualiannya tertulis di sana. Batas 400 baris per berkas tetap berlaku untuk semuanya.
- Alasan: Ketiganya komponen bersama yang sudah teruji di starter dan diminta diadopsi utuh. Membongkarnya sekarang berisiko merusak perilaku yang dijaga test tanpa manfaat langsung, sedangkan seluruh kode baru dan halaman contoh sudah mengikuti batas.
- Dampak untuk proyek ini: tiga fungsi itu tetap panjang. Kode baru dan perubahan di luar tiga fungsi itu tidak boleh melewati 80 baris.
- Tinjau ulang: saat salah satu komponen itu diubah secara berarti (pecah saat itu), atau 2027-05-11

## D-0017: Larastan, Pint, dan lockfile npm masuk repositori

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: `larastan/larastan` ^3.14 dipasang sebagai dependensi pengembangan, dengan konfigurasi di `phpstan.neon` (level 5, memeriksa `app`, `database`, dan `routes`). Pint dikonfigurasi di `pint.json` (preset Laravel, folder `lang` dikecualikan karena dibuat ulang oleh `lang:update`). `package-lock.json` di-commit, dan berasal dari `npm install`, bukan diedit tangan.
- Alasan: Stop gate guidelines memerlukan analisis statis dan pemeriksa format, dan `security.md` mewajibkan lockfile di-commit.
- Dampak untuk proyek ini: PHPStan menjadi bagian pemeriksaan sebelum pekerjaan dianggap selesai. Kode lama ikut diperiksa dan bersih pada level 5.
- Tinjau ulang: 2027-05-11

## D-0018: Stop gate tidak membaca vonis reviewer di lingkungan ini

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: Hook Stop gate membaca `VERDICT: PASS|FAIL` dari isi `tool_result` pemanggilan tool Agent (`.claude/hooks/lib.php` dan `boost-quality.php`). Di Claude Code yang dipakai di sini, `boost-reviewer` selalu berjalan async walaupun diminta di latar depan, sehingga `tool_result` hanya berisi pesan "launched" dan vonisnya tidak terbaca. Laporan reviewer yang sebenarnya datang sebagai pesan terpisah. Cara kerja yang dipakai: jalankan reviewer sekali, tunggu laporannya selesai, baca vonis dan temuannya, perbaiki temuan blokir, lalu laporkan hasilnya. Gate melepas sendiri setelah tiga kali menahan.
- Alasan: Review adalah bagian dari standar, jadi tetap dijalankan dan dibaca walaupun gate tidak mengenali hasilnya. Penyebab pastinya (versi Claude Code) belum dipastikan.
- Dampak untuk proyek ini: setiap giliran yang menyentuh kode ditahan hingga tiga kali, tanpa menurunkan kualitas review. Tidak ada pekerjaan di proyek ini. Perbaikan ada di repo boosts-guidelines, misalnya membaca vonis dari laporan subagent yang masuk sebagai pesan.
- Tinjau ulang: saat boosts-guidelines atau Claude Code diperbarui

## D-0019: Role dasar lewat command idempoten, akun demo dijaga config

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui semua rekomendasi agent di percakapan 2026-11-11
- Keputusan: Role dasar (`Super Admin`, `Guest`) didefinisikan satu kali di enum `App\Enums\RoleName` dan dibuat oleh Action `SyncBaseRoles` (idempoten, memakai `Role::findOrCreate`). Action itu dipanggil oleh command `php artisan roles:sync-base`, yang dijalankan di setiap deploy production, dan oleh `RoleSeeder` untuk lokal. `UserSeeder` hanya membuat akun demo bila `app.seed_demo_accounts` (env `SEED_DEMO_ACCOUNTS`) aktif. Default-nya mati, dan `.env.example` menyalakannya untuk lokal. Akun demo memakai password bawaan factory dan email di domain `example.test`.
- Alasan: `factories.md` melarang seeder menjadi satu-satunya jalan untuk data yang dibutuhkan production dan melarang seeder jalan saat deploy. `runtime.md` melarang percabangan menurut nama environment, jadi penjaganya berupa config. `security.md` mewajibkan data seeder berupa data karangan, sedangkan email lama memakai domain perusahaan dan alamat Gmail yang bisa saja nyata.
- Dampak untuk proyek ini: deploy production menjalankan `php artisan roles:sync-base` dan tidak menjalankan `db:seed`. `SEED_DEMO_ACCOUNTS` tidak boleh diaktifkan di production. Pengguna lokal yang sudah ada tidak diubah oleh seeder (dicari lewat username), jadi email lama mereka bertahan sampai database dibuat ulang.
- Tinjau ulang: saat fitur autentikasi dibuat (izin per fitur ikut disinkronkan lewat jalur yang sama)

## D-0020: Skill antislop-code untuk komentar kode

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), memintanya di percakapan 2026-11-11
- Keputusan: Memasang skill `antislop-code` dari https://github.com/miqdadbadjuber/anti-slop (v3.2.28, lisensi MIT, commit `41b85ede0257f39bfcffedf3f58c3c59bcc8530d`, SHA-256 berkas `004933009d352710dcbea7817101d5c188474a442e3ece26a6e0c18d3223898f`) sebagai skill kustom di `.ai/skills/antislop-code/SKILL.md`. Boost menyalinnya ke `.claude/skills`, dan `boost-gate.json` mewajibkan skill itu dimuat sebelum mengedit `app`, `routes`, `database`, `config`, `bootstrap/app.php`, `tests`, `resources/js`, dan `resources/views`. Skill ini hanya mengatur komentar: membuang komentar kosong, pengulangan kode, narasi langkah, separator hias, emoji, TODO tak jelas, dan komentar bertele-tele, serta tidak boleh mengubah kode.
- Tidak dipasang: skill `antislop` (core), `antislop-ui`, `antislop-copywriting`, `antislop-human`, `antislop-layoutmobile`, server MCP `antislop-contrast`, plugin, installer `npx antislop-ai`, dan penunjuk di `AGENTS.md` atau `~/.config`. Alasannya: core dan skill UI mengatur desain dan teks tampilan yang sudah punya standar (`frontend-react.md`) dan meminta agent menanyakan "mode" tiap sesi, sedangkan server MCP dan installer menjalankan kode serta menulis di luar repositori. Skill kode memang menyebut core sebagai pasangannya, tetapi aturan komentarnya lengkap di dalam berkasnya sendiri.
- Alasan: Komentar yang dihasilkan agent cenderung bertele-tele dan mengulang kode, padahal `production-standards.md` sudah meminta komentar yang menjelaskan alasan. Skill ini membuat aturan itu konkret dan terukur.
- Dampak untuk proyek ini: skill hanya memengaruhi komentar. Ia tidak menangkap pola kode lain (misalnya pemecahan berkas berlebihan atau abstraksi yang tidak perlu). Pembaruan dari upstream dilakukan dengan menyalin ulang berkas dan memperbarui hash di sini setelah dibaca.
- Tinjau ulang: 2027-05-11

## D-0021: Login dengan email atau username, sesi 15 menit

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui tinjauan I-0003 di percakapan 2026-11-11
- Keputusan: Satu kolom login menerima email atau username (dianggap email bila memuat `@`, dinormalkan ke huruf kecil). Tidak ada "ingat saya". Pesan gagal sama untuk akun yang ada dan tidak ada (D-0002), dan pengecekan hash tiruan menyamakan waktu respons. Percobaan gagal dibatasi 5 per identitas dan IP, dengan jeda yang bertambah (60 detik, lalu dua kali lipat sampai 16 menit) dan batas kasar 20 per menit per IP. Sesi berakhir setelah 15 menit tidak aktif (`SESSION_LIFETIME`) dan paling lama 12 jam (`SESSION_ABSOLUTE_LIFETIME`, ditegakkan middleware). ID sesi dibuat ulang saat login dan sesi dihapus saat logout. Algoritma hash tetap bcrypt (D-0001 tidak berubah).
- Alasan: `auth.md` dan D-0003 untuk aplikasi berdata sensitif. Mengganti ke argon2id memengaruhi hash pengguna yang sudah ada dan hanya disarankan oleh rule, bukan diwajibkan.
- Dampak untuk proyek ini: `dashboard`, `ui-guideline`, dan `audit-logs` hanya untuk yang sudah login. `.env` lokal yang masih `SESSION_LIFETIME=120` tidak berubah sampai Anda mengubahnya. Kolom `username` boleh kosong, jadi pengguna tanpa username hanya bisa masuk dengan email.
- Tinjau ulang: saat MFA dikerjakan

## D-0022: MFA ditunda sampai sebelum go-live

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui tinjauan I-0003 di percakapan 2026-11-11 (instruksi I-0003: "untuk mfa kita tahan dulu")
- Keputusan: 2FA belum dibuat. Ini menyimpang dari D-0004 (2FA wajib untuk akun berhak istimewa), yang tetap menjadi tujuan.
- Alasan: Pemilik memutuskan fokus ke autentikasi dasar lebih dulu.
- Dampak untuk proyek ini: sampai MFA ada, akun Super Admin hanya dilindungi password. Aplikasi tidak boleh di-deploy ke production sebelum MFA dibuat atau pemilik mencatat keputusan lain.
- Tinjau ulang: sebelum go-live

## D-0023: Desain jejak audit

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui tinjauan I-0003 di percakapan 2026-11-11
- Keputusan: Jejak audit memakai `spatie/laravel-activitylog` (D-0006 kini aktif) dengan model `App\Models\AuditActivity` yang menolak update dan hapus dari kode aplikasi. Perubahan data tercatat otomatis lewat trait `App\Models\Concerns\Audited` (nilai `name`, `email`, dan `password` pengguna hanya ditandai berubah, tidak disimpan). Kejadian yang bukan perubahan data lewat Action `RecordAudit` dengan daftar tetap `AuditEvent`. Kejadian autentikasi dicatat listener untuk event `Login`, `Failed`, `Lockout`, dan `Logout`. Setiap catatan membawa `request_id` (middleware `AssignRequestId`), hasil, IP, metode, path (tanpa query), user agent, dan modul (`log_name`: nama model, atau `Autentikasi` untuk kejadian login). Halaman daftar menampilkan satu kolom per fakta dan mengurutkan lewat daftar kunci tetap (`time`, `host`, `user`, `module`, `event`, `record`, `outcome`); urutan `user` memakai subquery nama pelaku yang tidak terindeks. Percobaan login yang gagal tidak menyimpan teks yang diketik, hanya akun yang ditarget bila ada. Retensi 1826 hari (5 tahun) lewat `activitylog:clean` terjadwal harian. Test arsitektur mewajibkan setiap model bisnis memakai `Audited`, dan namespace `App\Models\Concerns` (trait, bukan model) dikecualikan dari test "model memperluas Model".
- Alasan: `audit.md` dan `logging.md`. Penyimpanan teks login yang gagal berisiko membocorkan password yang salah ditempel di kolom login.
- Dampak untuk proyek ini: model bisnis baru wajib memakai trait `Audited`, dan test arsitektur akan menegakkannya saat ada model bisnis. Catatan audit hanya bisa dihapus oleh job retensi.
- Tinjau ulang: saat model bisnis pertama dibuat

## D-0024: Halaman audit logs terbuka untuk semua yang login

- Tanggal: 2026-11-11
- Status: digantikan oleh D-0029
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui tinjauan I-0003 di percakapan 2026-11-11 (instruksi I-0002: permission ditunda sampai akhir)
- Keputusan: Halaman `/audit-logs` bisa dilihat semua pengguna yang sudah login, tanpa izin khusus. Ini menyimpang dari `audit.md` (membaca log audit dijaga izin tersendiri).
- Alasan: Permission sengaja dikerjakan di tahap akhir.
- Dampak untuk proyek ini: setiap pengguna login melihat aksi, alamat IP, dan perubahan semua orang. Ada TODO di route dan controller.
- Tinjau ulang: saat tahap permission, atau sebelum ada pengguna selain tim pengembang

## D-0025: Ubah kata sandi memakai kata sandi saat ini, bukan `password.confirm`

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), mengikuti rekomendasi agent di percakapan 2026-11-11
- Keputusan: Halaman Profil mengubah kata sandi dengan syarat kata sandi saat ini benar di permintaan yang sama, tanpa middleware `password.confirm`. `auth.md` meminta `password.confirm` untuk aksi sensitif.
- Alasan: Kata sandi saat ini sudah dibuktikan pada permintaan itu sendiri, jadi dialog konfirmasi terpisah tidak menambah jaminan. Dialog konfirmasi ulang (`reauth-dialog`) belum ada.
- Dampak untuk proyek ini: Sesi yang terbuka tidak diminta konfirmasi ulang sebelum membuka halaman Profil. Ubah email, MFA, dan aksi sensitif lain nanti perlu `password.confirm` atau keputusan lain.
- Tinjau ulang: saat MFA atau ubah email dikerjakan

## D-0026: Export audit terbuka untuk semua yang login

- Tanggal: 2026-11-11
- Status: digantikan oleh D-0029
- Diputuskan oleh: Zulfadli Rizal (pemilik), menyetujui di percakapan 2026-11-11
- Keputusan: Semua pengguna yang sudah login bisa meminta dan mengunduh export audit logs (hanya miliknya sendiri), tanpa izin khusus. `audit.md` meminta membaca dan mengekspor log audit dijaga izin tersendiri.
- Alasan: Permission sengaja dikerjakan di tahap akhir (I-0002), sama seperti halaman audit logs (D-0024). Export dibatasi 50.000 baris, 3 export aktif per pengguna, 50 secara keseluruhan, 6 permintaan per menit, dicatat sebagai kejadian `exported`, dan berkasnya dihapus setelah 24 jam.
- Dampak untuk proyek ini: setiap pengguna login bisa memindahkan seluruh isi audit (termasuk alamat IP semua orang) ke berkas. Ada TODO di `routes/web.php`.
- Tinjau ulang: saat tahap permission, atau sebelum ada pengguna selain tim pengembang

## D-0027: Warna utama antarmuka diambil dari logo

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), mengikuti rekomendasi agent di percakapan 2026-11-11
- Keputusan: Warna utama antarmuka adalah biru tua dari `public/logo.png` (`#034871`, `oklch(0.386 0.092 243.4)`), menggantikan biru-ungu bawaan starter. Cokelat (`#948067`) dan hijau (`#84c81b`) logo belum dipakai.
- Alasan: aset merek BPR sudah ada di `public/`. Nilai warna dibaca dari piksel logo, belum ditetapkan resmi oleh pemilik atau bagian pemasaran.
- Dampak untuk proyek ini: tombol, tautan, menu aktif, dan fokus kini biru logo. Bila warna resmi berbeda, cukup ubah tiga token di `resources/css/app.css` dan tabel token di `.ai/rules/frontend-react.md`.
- Tinjau ulang: saat pemilik memastikan warna resmi

## D-0028: Pengguna dan Peranan terbuka untuk semua yang login

- Tanggal: 2026-11-11
- Status: digantikan oleh D-0029
- Diputuskan oleh: Zulfadli Rizal (pemilik), instruksi di percakapan 2026-11-11 ("ga usah mikirin perizinan dulu", I-0002)
- Keputusan: Halaman dan aksi Pengguna dan Peranan tidak dibatasi izin; semua pengguna yang login bisa menambah, mengubah, dan menghapus pengguna dan peranan. Ini menyimpang dari `security.md` (tidak ada yang bisa menaikkan hak aksesnya sendiri) dan `auth.md` (ubah role memakai `password.confirm`).
- Alasan: Permission sengaja dikerjakan di tahap akhir (D-0024, D-0026).
- Dampak untuk proyek ini: setiap pengguna login dapat membuat diri sendiri atau orang lain menjadi Super Admin. Karena itu aplikasi tidak boleh dipakai pengguna selain tim pengembang sebelum tahap permission selesai. TODO ada di `routes/web.php`.
- Tinjau ulang: saat tahap permission, atau sebelum ada pengguna selain tim pengembang

## D-0029: Fitur awal hanya untuk Super Admin, dijaga peran Spatie

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), penjelasan di percakapan 2026-11-11 ("semua fitur yang saya bangun diawal itu yang memang dikerjakan super admin ... middleware route bisa gunakan role dari spatie")
- Keputusan: Audit Logs (termasuk ekspor), Peranan, dan Pengguna hanya bisa dibuka Super Admin, dijaga middleware `role:Super Admin` pada route dan disembunyikan dari menu untuk yang lain. Dashboard, Profil, notifikasi, dan UI Guideline terbuka untuk semua yang login. Izin yang lebih rinci dibahas bersama fitur bisnis.
- Alasan: fitur-fitur awal memang dikerjakan Super Admin, dan peran Spatie sudah terpasang.
- Dampak untuk proyek ini: menggantikan D-0024 (audit logs terbuka), D-0026 (ekspor terbuka), dan D-0028 (pengguna dan peranan terbuka), sehingga celah menaikkan hak akses sendiri tertutup. Test fitur-fitur itu masuk sebagai Super Admin (`User::factory()->superAdmin()`). Kebijakan ekspor yang meminta izin tersendiri (`audit.md`) dipenuhi lewat peran ini sampai izin rinci ada.
- Tinjau ulang: saat fitur bisnis pertama dikerjakan

## D-0030: Aksi sensitif pengguna belum memakai `password.confirm`

- Tanggal: 2026-11-11
- Status: digantikan oleh D-0033
- Diputuskan oleh: menunggu pemilik
- Keputusan: Menambah pengguna, mengubah peran, mengatur ulang kata sandi pengguna lain, dan menghapus pengguna atau peranan hanya dijaga login, peran Super Admin, dan batas 60 permintaan per menit. `auth.md` meminta middleware `password.confirm` (konfirmasi kata sandi, berlaku 15 menit) untuk aksi sensitif seperti ganti role.
- Alasan: konfirmasi ulang butuh alur dan dialog yang belum ada (`reauth-dialog` termasuk komponen starter yang belum dibuat ulang, D-0015), dan Anda meminta perizinan dibahas bersama fitur bisnis.
- Dampak untuk proyek ini: sesi Super Admin yang terbuka atau dicuri bisa langsung membuat akun, mengubah peran, atau mengatur ulang kata sandi akun lain (yang lalu bisa dimasuki dengan kata sandi bawaan) tanpa kata sandi ulang. Alasan perubahan peran sudah wajib dan tercatat di audit.
- Tinjau ulang: saat MFA atau fitur bisnis pertama dikerjakan

## D-0031: Kata sandi minimal 8 karakter

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-11 ("pengguna belum terbiasa, jadikan minimal 8 karakter")
- Keputusan: Kata sandi minimal 8 dan maksimal 128 karakter, tanpa aturan komposisi, dan di production dicek terhadap daftar kata sandi bocor. Ini menggantikan D-0001 yang menetapkan 15 karakter selama kata sandi menjadi satu-satunya faktor.
- Alasan: pengguna belum terbiasa dengan kata sandi panjang.
- Dampak untuk proyek ini: `Password::defaults()` memakai `min(8)`. Selama MFA belum ada (D-0022), kata sandi 8 karakter adalah satu-satunya perlindungan akun, lebih lemah dari yang disarankan NIST SP 800-63B (`auth.md`). Kedaluwarsa 30 hari dan penolakan 3 kata sandi terakhir (D-0032) ikut menjaga.
- Tinjau ulang: saat MFA dikerjakan

## D-0032: Kata sandi bawaan dari .env, wajib ganti, kedaluwarsa 30 hari

- Tanggal: 2026-11-11
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-11 (risiko kata sandi bawaan di .env diterima pemilik)
- Keputusan: Akun baru dan akun yang kata sandinya diatur ulang admin memakai kata sandi bawaan dari `DEFAULT_USER_PASSWORD` di `.env` (nilainya tidak ditulis di repositori, dokumen, atau test; admin tidak mengetik kata sandi). Pemilik akun wajib menggantinya saat masuk pertama, dan sebelum itu hanya bisa membuka halaman ganti kata sandi dan keluar. Setiap kata sandi kedaluwarsa setelah 30 hari (`PASSWORD_MAX_AGE_DAYS`), lalu wajib diganti. Kata sandi baru tidak boleh sama dengan kata sandi sekarang dan 2 sebelumnya (3 terakhir, riwayat hanya berisi hash). Nilai bawaan tidak diperiksa terhadap aturan panjang kata sandi biasa.
- Alasan: kemudahan membuat akun, dan pengguna belum terbiasa.
- Dampak untuk proyek ini: siapa pun yang tahu nilai bawaan bisa masuk ke akun yang baru dibuat atau di-reset sebelum pemiliknya menggantinya; pemilik menerima risiko ini. Aplikasi menolak membuat akun bila `DEFAULT_USER_PASSWORD` kosong. Tabel `password_histories` dan kolom `users.password_changed_at` ditambahkan; akun yang sudah ada diberi tanggal ganti hari migrasi.
- Tinjau ulang: saat MFA dikerjakan

## D-0033: Aksi sensitif pengguna dan peranan memakai konfirmasi kata sandi

- Tanggal: 2026-11-12
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-12 ("boleh dikerjakan", menjawab usulan D-0030)
- Keputusan: Tambah, ubah, hapus pengguna; atur ulang kata sandi pengguna lain; dan tambah, ubah, hapus peranan memakai middleware `password.confirm`. Pemilik akun harus memasukkan kata sandinya lagi, dan konfirmasi berlaku 15 menit (`AUTH_PASSWORD_TIMEOUT=900`). Layar meminta lewat satu modal di bagian atas sebelum aksi dimulai (supaya isian tidak hilang); bila waktunya habis di tengah jalan, server menjawab 423 (`reauth_required`) dan modal yang sama terbuka, lalu pemilik akun mengulang aksinya. Tidak ada halaman terpisah. Percobaan konfirmasi yang salah dicatat di audit (`password_confirm_failed`, tanpa teks yang diketik) dan dibatasi 6 per menit. Ini menggantikan D-0030 dan memenuhi `auth.md`.
- Alasan: aksi ini mengubah siapa yang bisa masuk dan apa haknya; sesi yang terbuka atau dicuri tidak boleh cukup untuk melakukannya.
- Dampak untuk proyek ini: Ekspor audit tidak memakai konfirmasi ini (di luar daftar D-0030). Ubah kata sandi sendiri memakai kata sandi saat ini, bukan konfirmasi (D-0025). Konfirmasi berlaku per sesi.
- Tinjau ulang: saat MFA dikerjakan

## D-0034: Dua berkas dari template tetap bernama `utils` dan `misc`

- Tanggal: 2026-11-12
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-12 ("ikut rekomendasi saja")
- Keputusan: `resources/js/lib/utils.ts` (berisi `cn`) dan `resources/js/components/ui/misc.tsx` (Card, Badge, Alert, PageHeader, EmptyState, dan sejenisnya) tidak diganti nama, walau `production-standards` melarang nama berkas yang hanya kata generik. Berkas baru tidak boleh memakai nama generik; isi baru yang bukan bagian dari kedua berkas itu ditaruh di berkas bernama tepat.
- Alasan: keduanya dipindahkan apa adanya dari react-starter (D-0016) dan dipakai 27 dan 24 berkas. Mengganti nama mengubah puluhan import dan menjauhkan struktur dari template, sedangkan instruksi proyek meminta mengikuti template.
- Dampak untuk proyek ini: reviewer tidak lagi menandai kedua nama ini. Template yang diperbarui dapat dibandingkan langsung dengan berkas yang sama.
- Tinjau ulang: bila template memecah kedua berkas itu

## D-0035: Nama dan email tidak disamarkan di audit logs

- Tanggal: 2026-11-12
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-12 ("karyawan punya kontrak dengan perusahaan dan penggunaan datanya diperbolehkan", dan audit log hanya dilihat Super Admin)
- Keputusan: Nilai sebelum dan sesudah `name` dan `email` pengguna disimpan apa adanya di audit logs. Hanya `password` (kredensial, hash saja) yang tetap ditandai berubah tanpa nilai (`[disembunyikan]`). Ini menyimpang dari `audit.md` ("data pribadi hanya ditandai berubah") dan `security.md` ("data pribadi tidak pernah masuk ke log"), khusus untuk jejak audit; log aplikasi (`Log::`) tetap merujuk orang dengan ID.
- Alasan: audit yang menyamarkan nama dan email tidak bisa menjawab siapa mengubah apa menjadi apa; pemilik menyatakan penggunaan data karyawan diizinkan oleh kontrak, dan hanya Super Admin yang dapat membuka audit logs (D-0029).
- Dampak untuk proyek ini: entri lama yang sudah menulis `[disembunyikan]` untuk nama dan email tetap begitu (log hanya bisa ditambah). Data debitur nanti (fitur bisnis) tidak otomatis ikut keputusan ini; keputusannya perlu dari pemilik per jenis data. Kolom sensitif lain (nomor identitas, data pembayaran) tetap dilindungi sesuai `security.md`. Ekspor audit tidak memuat nilai sebelum dan sesudah, hanya nama pelaku di kolom Pengguna.
- Tinjau ulang: saat data debitur ditambahkan, atau bila akses audit logs dibuka untuk peran selain Super Admin

## D-0036: Label grup menu "Kelola Referensi" (16 karakter)

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-14 ("biarkan saja atau buat lebih longgar jadi 20 karakter agar itu tidak jadi pekerjaan menggantung")
- Keputusan: batas panjang label menu dilonggarkan dari 15 menjadi 20 karakter (aturan di `.ai/rules/frontend-react.md`, Menu, dan pemeriksa `scripts/check-standards.mjs`), sehingga nama grup "Kelola Referensi" (16 karakter) sesuai aturan dan bukan pengecualian. Pemeriksa tetap hanya memeriksa label tautan, bukan label induk submenu.
- Alasan: nama diminta pemilik; batas panjang dibuat agar menu muat di sidebar `w-60`, dan label induk memakai ikon serta tanda panah.
- Dampak untuk proyek ini: belum ada; teks tampil utuh di sidebar. Bila batas dilonggarkan, aturan dan pemeriksa perlu diubah bersama.
- Tinjau ulang: bila sidebar diubah lebar atau menunya bertambah panjang

## D-0037: Kantor dihapus permanen hanya bila tidak dipakai, dan datanya di production lewat menu

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-14 ("biarkan saja seeder itu untuk data contoh saja dan anggap ini selesai")
- Keputusan: (1) Kantor tidak memakai hapus lunak. Menghapus kantor hanya berhasil bila tidak ada pengguna yang memakainya, pengguna terhapus ikut dihitung, dan database menolaknya juga (`users.office_id` RESTRICT). Yang sudah dipakai tidak bisa hilang, jadi riwayat tidak bergantung pada kantor yang hilang; tabel bisnis berikutnya yang menunjuk kantor sebaiknya memakai RESTRICT yang sama. (2) `OfficeSeeder` hanya untuk lokal, sesuai `factories.md` (seeder tidak dijalankan saat deploy production), dan hanya menambah kantor yang belum ada menurut nama tanpa menimpa yang diubah di aplikasi. Tujuh kantor di production dimasukkan lewat menu Data Kantor. Bila pemilik ingin memuatnya dari berkas, perlu command idempoten yang dijalankan sekali, dan keputusan itu perlu dari pemilik.
- Alasan: kantor jarang berubah dan berjumlah sedikit, sehingga hapus lunak dan pemulihan menambah layar tanpa manfaat; perubahan data kantor sudah tercatat di audit logs. Seeder dengan data sungguhan di production melanggar aturan seeder dan bisa menimpa perubahan dari menu.
- Dampak untuk proyek ini: kantor yang salah ketik dapat dihapus selama belum dipakai; sesudah itu hanya bisa diubah. Entri audit kantor terhapus tetap ada.
- Tinjau ulang: saat tabel bisnis pertama yang menunjuk kantor dibuat

## D-0038: Tiga komponen template baru melewati batas panjang fungsi

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-13 ("ikut rekomendasi aja kalau memang tidak bisa ya gak apa-apa pengecualian")
- Keputusan: `DateRangePicker` (81 baris), `TagInput` (85 baris), dan `FileUploader` (239 baris) diadopsi dari react-starter apa adanya dan ditambahkan ke daftar `longFunctionAllowed` di `scripts/check-standards.mjs`, seperti `DataTable` dan `FileInput` yang sudah diizinkan lewat D-0016. `Combobox` keluar dari daftar karena template memecahnya (kini di bawah 80 baris). Usul: setujui, atau minta tiga komponen itu dipecah di proyek ini (FileUploader paling besar).
- Alasan: pemilik meminta seluruh pembaruan template diadopsi dan komponen baru jangan ditulis sendiri; membiarkan berkasnya sama dengan template menjaga pembaruan berikutnya bisa digabung tanpa konflik. Pelonggaran pemeriksa ini awalnya ditulis sebelum persetujuan; pemilik kemudian menyetujuinya.
- Dampak untuk proyek ini: tiga fungsi melebihi 80 baris tidak ditandai pemeriksa. Tes komponen-komponen itu ikut dari template.
- Tinjau ulang: saat template memecah komponen itu, atau bila pemilik memilih memecahnya di sini

## D-0039: Tiga dependensi pengembangan untuk tes dan halaman error

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-13 ("3 poin 'Yang tidak diadopsi' boleh dikerjakan")
- Keputusan: tiga paket `devDependencies` ditambahkan, hanya untuk pengembangan dan CI, tidak ikut ke production: `vitest-axe` 0.1 (pemeriksaan aksesibilitas otomatis di Vitest), `@playwright/test` 1.65 (tes ujung ke ujung), dan `tsx` 4.23 (menjalankan skrip pembuat halaman error).
- Alasan: template react-starter memakainya untuk tes aksesibilitas, tes ujung ke ujung, dan pembuatan halaman error statis, dan pemilik meminta seluruh template diadopsi.
- Pemeriksaan paket (security.md): nama persis ada di npm; lisensi MIT (`vitest-axe`, `tsx`) dan Apache-2.0 (`@playwright/test`, Microsoft); rilis terakhir `vitest-axe` 2025-01-22 (di bawah 24 bulan), `@playwright/test` 2026-11-12, `tsx` 2026-09-20; `npm audit` 0 temuan setelah dipasang. `@playwright/test` tidak mengunduh peramban sendiri; peramban diunduh terpisah lewat `npx playwright install` (ukuran ratusan MB, dijalankan pemilik atau CI).
- Dampak untuk proyek ini: `package.json` dan `package-lock.json` berubah; tes ujung ke ujung memakai database uji `bpr_sipebri_test` (migrasi dan seed akun demo, tidak menghapus apa pun).
- Tinjau ulang: saat ada temuan `npm audit` atau paket tidak dipelihara lagi

## D-0040: Konfigurasi penyimpanan S3 dan SFTP dari layar

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-14 ("ikut rekomendasi perbaikan" atas hasil review 2026-11-14)
- Keputusan: (1) Ada satu konfigurasi per jenis penyimpanan (S3 dan SFTP). Bawaan dari `.env` berlaku sampai ada yang disimpan di layar Penyimpanan; setelah itu yang di layar dipakai, dan "Kembali ke bawaan" menghapusnya. (2) Nilai koneksi disimpan terenkripsi (cast `encrypted:array`, kunci `APP_KEY`), tidak pernah dikirim ke browser (hanya penanda sudah terisi dan empat karakter terakhir access key), dan di audit hanya ditandai berubah. Rahasia yang dikosongkan di form berarti tetap memakai yang tersimpan. (3) Uji koneksi memanggil sistem luar dengan batas 3 detik sambung dan 10 detik total, tanpa pengulangan, dan pesan galatnya dibersihkan dari nilai rahasia. (4) Karena host dan endpoint diisi dari layar, host harus menunjuk ke alamat publik; alamat internal (10.x, 192.168.x, 127.x, dan sejenisnya) ditolak kecuali host didaftarkan di `STORAGE_ALLOWED_HOSTS`, dan alamat metadata cloud (169.254.x) tidak pernah boleh. Nilai `.env` dianggap milik operator dan tidak diperiksa. (5) Semua perubahan memakai konfirmasi kata sandi (D-0033); uji koneksi dibatasi 10 per menit.
- Alasan: permintaan pemilik (bawaan `.env`, layar menggantikan bila diisi, uji koneksi) dan aturan `security.md` untuk nilai yang diisi pengguna.
- Dampak untuk proyek ini: MinIO atau SFTP di dalam jaringan bank perlu didaftarkan di `STORAGE_ALLOWED_HOSTS` agar bisa diisi dari layar (atau dipasang lewat `.env`). Pemeriksaan host memakai DNS saat disimpan dan saat diuji, jadi nama yang berubah arah sesudahnya (DNS rebinding) tidak tertangkap; risiko ini diterima untuk akses Super Admin saja. Setelah review 2026-11-14, rentang NAT64 (`64:ff9b::/96`) ditolak seperti alamat metadata, karena bisa memuat alamat IPv4 mana pun dalam bentuk IPv6. Berkas yang sudah tersimpan di penyimpanan lama tidak dipindahkan saat konfigurasi berganti.
- Tinjau ulang: saat pengelola berkas sungguhan dibuat

## D-0041: Aturan pengelola berkas penyimpanan

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-13 ("lanjut tahap 2 pakai rekomendasi")
- Keputusan: (1) Pengelola berkas membaca dan menulis berkas sungguhan di S3 atau SFTP yang dipilih, hanya untuk Super Admin; membaca (daftar, unduh) tanpa konfirmasi kata sandi, menulis (folder, unggah, ubah nama, hapus) dengan konfirmasi kata sandi (D-0033). (2) Unggah paling besar 10 MB per berkas dan hanya jenis pdf, doc, docx, xls, xlsx, csv, txt, zip, jpg, jpeg, png, webp, gif (`config/storage_connections.php`); berkas tidak pernah menimpa berkas bernama sama. (3) Folder hanya dihapus bila kosong, dan hanya berkas yang bisa diubah namanya (di S3 folder tidak punya nama sendiri). (4) Berkas diunduh sebagai lampiran; hanya jenis yang aman yang boleh dilihat di halaman (diubah oleh D-0042). (5) Unggah, buat folder, ubah nama, hapus, dan unduh tercatat di audit (modul "Penyimpanan"). (6) Satu folder dibaca sampai 1.000 entri; yang lebih besar dipotong dan halaman memberi tahu. (7) Panggilan pengelola berkas boleh sampai 60 detik (batas bawaan 10 detik terlalu pendek untuk berkas 10 MB); sambungan tetap 3 detik. (8) Disk lokal bawaan Laravel tidak lagi menyajikan berkas lewat `storage/{path}` (`serve` dimatikan), karena rute itu menjawab semua alamat di bawah `/storage`.
- Alasan: rekomendasi agent yang disetujui pemilik; batas 10 MB sesuai production-standards.
- Dampak untuk proyek ini: PHP dan web server harus mengizinkan unggahan sedikitnya 10 MB (`upload_max_filesize`, `post_max_size`, dan batas isi permintaan server web), kalau tidak berkas besar tidak sampai ke pemeriksaan. Hapus dan ubah nama tidak bisa dibatalkan dan tidak atomik di S3 (salin lalu hapus). Tidak ada tempat sampah.
- Tinjau ulang: bila pengguna selain Super Admin perlu mengelola berkas, atau bila ada folder yang lebih dari 1.000 entri

## D-0042: Pratinjau berkas di pengelola berkas

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-13 ("ya mulai, pakai rekomendasi")
- Keputusan: (1) Berkas boleh dilihat di laci rincian lewat rute `storage.files.preview`: gambar (jpg, jpeg, png, webp, gif), PDF, dan teks (txt, csv, json, log); jenis lain hanya unduh. (2) Jenis isi ditentukan dari ekstensi pada daftar `preview_types` di `config/storage_connections.php`, tidak pernah dari isi berkas. SVG dan HTML sengaja tidak ada karena bisa menjalankan skrip. (3) Paling besar 5 MB (`preview_max_kb`); yang lebih besar hanya unduh. (4) Respons memakai `nosniff`, `Cache-Control: private, no-store`, dan CSP `default-src 'none'` dengan `sandbox` (PDF tanpa `sandbox` karena penampilnya tidak terbuka di dalamnya), dan hanya boleh dibingkai dari situs sendiri. (5) Berkas dialirkan lewat aplikasi, bukan tautan bertanda tangan S3, supaya S3 dan SFTP sama dan alamat penyimpanan tidak sampai ke browser. (6) Melihat berkas dibatasi 60 permintaan per menit dan tercatat di audit sebagai "Berkas dilihat" (modul Penyimpanan). Tidak ada dependensi baru.
- Alasan: rekomendasi agent yang disetujui pemilik; hanya membaca, jadi aman untuk koneksi nyata.
- Dampak untuk proyek ini: setiap PDF yang dilihat dibaca dari penyimpanan lewat server aplikasi (sampai 5 MB per permintaan). Pratinjau Word dan Excel tidak ada; itu butuh layanan luar yang menerima berkasnya.
- Tinjau ulang: bila ada kebutuhan melihat jenis lain (Office), atau bila ukuran 5 MB terlalu kecil atau terlalu besar

## D-0043: Penyimpanan Lokal, penyimpanan aktif, dan pengaturan proyek yang dinamis

- Tanggal: 2026-11-13
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-13 ("Ya, ikut rekomendasi" atas tinjauan I-0039)
- Keputusan: (1) Menu Penyimpanan menampilkan jenis Lokal, yaitu disk `local` Laravel (`storage/app/private`, tidak bisa dibuka dari web); disk `public` tidak ditampilkan. Lokal tidak punya pengaturan koneksi (tidak bisa diubah atau dikembalikan, route `PATCH` dan `DELETE /storage/{type}` hanya menerima S3 dan SFTP), tetapi bisa diberi nama, diuji, dan dibuka di pengelola berkas. (2) Tepat satu penyimpanan aktif tempat berkas proyek disimpan, dipilih di menu Pengaturan; bawaannya Lokal. Hanya penyimpanan yang lengkap dan lulus uji koneksi terakhir yang bisa dipilih (Lokal selalu bisa). (3) Pengaturan proyek disimpan di tabel `settings` (kunci dan nilai, kunci dari enum `SettingKey`), dibaca lewat cache satu peta (`ProjectSettings`) yang dihapus saat disimpan; nilai kosong berarti bawaan dari config atau berkas, jadi aplikasi jalan dengan tabel kosong. (4) Pengaturan: nama aplikasi, meta deskripsi, penyimpanan aktif, logo, dan favicon; nama dan deskripsi dipakai judul tab, tag `meta description`, dan header aplikasi. Logo (png, jpg, jpeg, webp, paling besar 512 KB) dan favicon (png, ico, paling besar 128 KB) diunggah ke penyimpanan aktif di folder `branding`, dengan nama acak baru tiap unggahan; SVG tidak diizinkan karena bisa menjalankan skrip. Gambar disajikan lewat rute publik `/brand/{logo|favicon}` (halaman masuk memakainya) dengan jenis isi dari ekstensi, `nosniff`, CSP `sandbox`, dan cache setahun karena alamatnya membawa versi; gambar yang tak bisa dibaca dijawab 404 dan halaman memakai gambar bawaan. Mengunggah atau mengembalikan gambar memakai konfirmasi kata sandi dan file lama dihapus. Halaman error statis dan email memakai nama bawaan dari build, karena dibuat tanpa server. (5) Menyimpan pengaturan memakai konfirmasi kata sandi (D-0033) dan tercatat di audit. (6) Lokal boleh menjadi penyimpanan aktif di production, tetapi aplikasi di lebih dari satu server butuh disk bersama (`runtime.md`); itu tanggung jawab operator.
- Alasan: rekomendasi agent yang disetujui pemilik.
- Dampak untuk proyek ini: berkas bank di disk lokal ikut di server aplikasi, jadi perlu dicadangkan bersama aplikasi. Setiap halaman membaca satu entri cache untuk nama aplikasi. Gambar yang diunggah ikut hilang bila penyimpanan aktifnya hilang; mengembalikan ke gambar bawaan selalu bisa.
- Tinjau ulang: saat aplikasi dipasang di lebih dari satu server, atau saat fitur bisnis pertama mulai menyimpan berkas

## D-0044: Header keamanan dipasang oleh aplikasi lewat satu middleware

- Tanggal: 2026-11-14
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-14 ("kalau memang bisa dikerjakan dari sisi kode boleh dikerjakan"). Ini menggantikan usulan awal (header dari server web saja); ucapan 2026-11-13 "nginx dan header keamanan tidak perlu" (I-0033) tetap berlaku untuk konfigurasi nginx dari template.
- Keputusan: middleware `SetSecurityHeaders` (global, `bootstrap/app.php`) memasang pada setiap jawaban aplikasi, halaman error termasuk: `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, CSP yang hanya menyatakan `frame-ancestors 'self'`, `base-uri 'self'`, `form-action 'self'`, dan `object-src 'none'`, serta `Cache-Control: no-store` untuk jawaban kepada orang yang sudah masuk (yang ditampilkan bisa memuat data pribadi), kecuali jawaban yang memilih `public` (logo). Halaman untuk tamu (login, error) tidak diberi `no-store` karena tidak memuat data pribadi. Header yang sudah dipasang pengendali untuk alasannya sendiri tidak ditimpa (CSP `sandbox` pada aliran berkas). CSP tidak memuat `script-src` dan `style-src`, karena halaman memuat skrip dan gaya sendiri (termasuk Vite saat pengembangan); itu bisa ditambah bila diminta. Server web tetap boleh menambah header yang sama.
- Alasan: `security.md` meminta header dari satu middleware untuk semua respons; ditemukan review 2026-11-14 (Auth) dan bisa dikerjakan dari sisi kode tanpa dependensi baru.
- Dampak untuk proyek ini: halaman tidak bisa dibingkai situs lain, jenis isi tidak ditebak peramban, dan halaman untuk orang yang sudah masuk tidak disimpan cache. Tombol Kembali di peramban memuat ulang halaman itu dari server.
- Tinjau ulang: bila `script-src` ingin ditambahkan

## D-0045: Starterkit memakai SQLite dan Laravel 13

- Tanggal: 2026-11-14
- Status: berlaku
- Diputuskan oleh: Zulfadli Rizal (pemilik), di percakapan 2026-11-14 ("untuk database gunakan saja sqlite tidak apa-apa ... semuanya ikut rekomendasi kamu")
- Keputusan: salinan SIPEBRI ini adalah starterkit di atas Laravel 13. Database kerja SQLite (`database/database.sqlite`), tes PHP di SQLite dalam memori, dan tes ujung ke ujung di berkas `database/e2e.sqlite`. Ini menggantikan I-0015 (tes memakai MySQL) untuk starterkit; catatan lama tentang MySQL di dokumen lain adalah riwayat SIPEBRI. Tes yang bergantung pada MySQL (`information_schema`) ditulis ulang lewat Schema builder agar jalan di keduanya.
- Alasan: permintaan pemilik; SQLite tidak butuh layanan terpisah untuk titik awal proyek baru.
- Dampak untuk proyek ini: SQLite tidak menegakkan semua yang ditegakkan MySQL (misalnya kunci baris `lockForUpdate` hanya berarti di MySQL), jadi tes konkurensi tidak membuktikan perilaku MySQL. Proyek turunan yang memakai MySQL mengganti `DB_CONNECTION` dan `phpunit.xml`.
- Tinjau ulang: bila proyek turunan memilih database lain
