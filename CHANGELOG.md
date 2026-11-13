# Changelog

Seluruh perubahan penting pada aplikasi ini didokumentasikan dalam file ini.

Format changelog mengikuti prinsip [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), dengan penomoran versi berdasarkan [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Changed
- Aksi di semua tabel (Kelola Pengguna, Data Jabatan, Data Kantor, Penyimpanan, Audit Logs) kini satu tombol titik tiga yang membuka menu aksi, bukan deretan ikon. Hapus berwarna merah di bawah pemisah, dan aksi yang tidak tersedia tampil redup dengan alasannya. Tombol "Buka berkas" di Penyimpanan tetap ikon tersendiri.
- Pengelola berkas kembali punya pilihan tampilan Kotak (bawaan) dan Daftar. Penyimpanan S3 dan SFTP bisa diberi nama sendiri lewat aksi "Ubah nama" (kosong berarti memakai nama jenisnya).
- Filter tanggal audit logs memakai satu pemilih rentang tanggal. Penghitung karakter Textarea tampil di kanan label.
- Paginasi tabel: pilihan "baris per halaman" kini di kiri teks jumlah baris (urutannya ditukar), di semua tabel.
- Penghitung karakter pada isian teks panjang (Textarea) pindah ke kanan atas, sejajar label, agar jarak di bawah isian sama dengan isian lain.
- Menu Peranan kini bernama Data Jabatan dan Pengguna menjadi Kelola Pengguna, lengkap dengan judul halaman dan teksnya; alamat halaman tidak berubah. Di semua layar, "peran" kini ditulis "jabatan" (form dan filter Pengguna, audit logs). Entri audit lama tetap bertuliskan "Peranan".

### Fixed
- Detail audit untuk permintaan ekspor menampilkan jumlah baris dan filternya; peranan yang dihapus mencatat ID peranannya.
- Audit logs menyimpan nilai nama dan email pengguna apa adanya (D-0035); hanya kata sandi yang tetap disamarkan. Subjek peranan tampil sebagai "Peranan #id".
- Detail audit untuk peranan (dibuat, diubah, dihapus) dan perubahan peran pengguna kini menampilkan nilai sebelum dan sesudah serta alasannya, bukan "Tidak ada perubahan data". Entri lama yang sudah tertulis tetap kosong.
- Isian alasan saat menambah pengguna berperan berlabel "Alasan pemberian peran".

### Added
- Salinan SIPEBRI sebagai starterkit di Laravel 13 dengan SQLite (D-0045). Tes `DatabaseIntegrityTest` dan `LoginTest` disesuaikan untuk SQLite dan Laravel 13.
- Semua jawaban aplikasi, halaman error termasuk, membawa header keamanan (`nosniff`, `Referrer-Policy`, CSP `frame-ancestors`); jawaban untuk orang yang sudah masuk juga `no-store`. Aplikasi hanya menjawab host dari `APP_URL`, jadi `APP_URL` harus alamat sebenarnya. Batas label menu jadi 20 karakter.
- Penyimpanan Lokal (folder `storage/app/private` proyek) tampil di menu Penyimpanan, bisa dilihat di pengelola berkas, diberi nama, dan diuji. Menu Pengaturan kini berisi nama aplikasi, meta deskripsi, pilihan penyimpanan aktif (hanya yang lulus uji koneksi), serta logo dan favicon yang bisa diunggah dan dikembalikan ke bawaan; nama, deskripsi, logo, dan favicon dipakai judul tab, header, dan halaman masuk. Penyimpanan aktif diberi lencana "Aktif" di daftar.
- Menu Pengaturan (`/settings`) di bawah Penyimpanan, untuk Super Admin; halamannya masih placeholder.
- Pengelola berkas untuk S3 dan SFTP (tombol Buka di menu Penyimpanan): daftar folder dan berkas dengan cari, urut, dan paginasi; unduh; folder baru; unggah (10 MB, jenis tertentu) dengan progres; ubah nama; hapus berkas atau folder kosong; semuanya tercatat di audit. Halaman contoh File Manage dihapus. Server web dan PHP harus mengizinkan unggahan 10 MB.
- Menu Penyimpanan berisi daftar Amazon S3 dan SFTP dengan konfigurasi (bawaan dari `.env`, diganti yang disimpan di layar, terenkripsi), uji koneksi, dan kembali ke bawaan. Variabel baru `STORAGE_ALLOWED_HOSTS` untuk host internal. Menu File Manage dilepas.
- Peringatan sesi akan berakhir (satu menit sebelum batas diam, dengan pilihan tetap masuk atau keluar) dan halaman pemeliharaan saat server menjawab 503.
- Halaman File Manage (contoh pengelola berkas), halaman error bergaya untuk 401, 403, 404, 419, 429, 500, dan 503 (view Blade), pratinjau error dan email OTP di UI Guideline, tes aksesibilitas dan tes ujung ke ujung (Playwright), anggaran ukuran bundel, dan skrip `rebrand` serta `error-pages`.
- Pembaruan template react-starter diadopsi: tema terang, gelap, atau ikut sistem (tombol tema di header), animasi halus (halaman, dialog, laci), komponen baru (Accordion, Breadcrumb, DateRangePicker, Drawer, FileUploader, Progress, Slider, Stepper, TagInput, TimePicker, MultiCombobox versi template), dan pelaporan galat di peramban.
- Menu Penyimpanan (hanya Super Admin) dengan halaman `/storage` yang masih kosong; pengaturan dan uji koneksi S3 dan SFTP menyusul.
- Data Kantor: tambah, ubah, hapus kantor (nama wajib dan unik; alamat dan koordinat boleh kosong, koordinat unik) dengan pencarian, urutan, dan paginasi; hanya Super Admin, dengan konfirmasi kata sandi dan catatan audit. Pengguna dapat ditempatkan di satu kantor atau tanpa kantor (isian, kolom, dan filter Kantor di Kelola Pengguna). Kantor yang masih dipakai pengguna tidak bisa dihapus.
- `OfficeSeeder` dengan satu kantor contoh untuk lokal; data tujuh kantor sungguhan diisi pemilik.
- Menu Administrator disusun ulang: grup Kelola Referensi (Data Kantor, Data Jabatan), Kelola Pengguna, Audit Logs, dan UI Guideline. 
- Pengguna: filter Status (Aktif, Dihapus, Semua), kolom Status, dan aksi Pulihkan untuk akun yang dihapus (memakai konfirmasi kata sandi dan tercatat di audit). Peranan kini punya pencarian, urutan, dan paginasi dari server.
- Konfirmasi kata sandi (15 menit) untuk aksi sensitif: tambah, ubah, hapus pengguna; atur ulang kata sandi; tambah, ubah, hapus peranan. Satu modal di bagian atas meminta kata sandi sebelum aksi (dan saat server menjawab 423). Percobaan salah tercatat di audit.
- Kata sandi bawaan dari `.env` (`DEFAULT_USER_PASSWORD`) untuk akun baru, wajib diganti saat masuk pertama; tombol atur ulang kata sandi per pengguna; kedaluwarsa 30 hari dan penolakan 3 kata sandi terakhir; halaman ganti kata sandi `password-expired`.
- Komponen pilihan ganda `MultiCombobox` untuk peran pengguna.
- Menu Peranan dan Pengguna (hanya Super Admin): kelola peranan, serta pengguna beserta perannya, dengan pencarian, filter peran, urutan, dan paginasi dari server; perubahan tercatat di audit logs.
- Login dan logout dengan email atau username, throttling dengan jeda bertambah, dan batas umur sesi 12 jam.
- Audit logs: pencatatan otomatis perubahan data pengguna dan kejadian autentikasi, ID request di setiap respons (`X-Request-Id`), halaman `/audit-logs` dengan kolom Waktu, Host, Pengguna, Modul, Kejadian, Data, dan Hasil (masing-masing bisa diurutkan), filter tanggal, modul, dan hasil, serta detail entri (IP, permintaan, peramban), dengan retensi 5 tahun.
- Kerangka frontend Inertia React dengan TypeScript dan halaman awal `home`.
- Laravel Wayfinder untuk membuat URL frontend dari route.
- Komponen UI, token tema, layout, dan halaman `ui-guideline` dari react-starter, beserta ESLint, Prettier, Vitest, dan pemeriksa standar frontend.
- Header aplikasi dengan lonceng notifikasi, menu akun (email, Profil, Keluar), dan pengingat verifikasi dua langkah, serta tombol Export di audit logs.
- Export audit logs ke CSV (maksimal 50.000 baris, mengikuti filter, sel berawalan `=`, `+`, `-`, `@` dinetralkan) yang disusun di antrean; selesai atau gagal, pengguna diberi tahu di lonceng notifikasi dengan tautan unduh yang berlaku 24 jam. Production butuh worker antrean.
- Lonceng notifikasi yang berfungsi (notifikasi database, tandai dibaca).
- Halaman `/profile` dengan data akun, ubah kata sandi (kebijakan 15 karakter, sesi perangkat lain berakhir, perubahan dan percobaan gagal tercatat di audit), dan kartu verifikasi dua langkah yang belum berfungsi.
- Halaman `dashboard` sebagai halaman awal.
- Skill `antislop-code` (dari anti-slop, MIT) untuk menjaga komentar kode tetap singkat dan bermakna.
- Command `roles:sync-base` (idempoten) untuk membuat role dasar saat deploy, dan enum `RoleName`.

### Changed
- Semua dialog (`Modal` dan `ConfirmDialog`) tampil di bagian atas layar, tidak lagi di tengah.
- `AUTH_PASSWORD_TIMEOUT` bawaan 900 detik (sebelumnya 10800), sesuai `auth.md`.
- Kata sandi minimal 8 karakter (sebelumnya 15). Judul kartu dan modal diawali huruf kapital. Tombol Export dan Tambah memakai warna primary, dan Export menjelaskan bahwa semua baris sesuai filter yang diekspor.
- Peranan yang masih dipegang pengguna kini ditolak penghapusannya juga oleh database (migrasi `model_has_roles.role_id` menjadi RESTRICT); pengguna baru tidak lagi diberi kata sandi lewat formulir.
- Audit Logs dan ekspornya kini hanya untuk Super Admin (sebelumnya semua yang login); menu itu disembunyikan dari yang lain.
- Test PHP berjalan di MySQL (`bpr_sipebri_test`), bukan SQLite.
- Antarmuka memakai logo bundar BPR dan warna biru logo sebagai warna utama, menggantikan huruf "S" dan biru-ungu bawaan starter.
- `dashboard` dan `ui-guideline` kini hanya untuk pengguna yang sudah masuk.
- Umur sesi tidak aktif bawaan turun dari 120 ke 15 menit.
- Vite naik ke 8 dan `laravel-vite-plugin` ke 3.
- Pest naik ke 4.7 dan PHPUnit ke 12.
- `UserSeeder` hanya membuat akun demo bila `SEED_DEMO_ACCOUNTS` aktif (default mati), dengan email di domain `example.test` dan idempoten. `RoleSeeder` memakai Action yang sama dengan `roles:sync-base`.

### Removed
- `yajra/laravel-datatables-oracle`, beserta provider dan `config/datatables.php`: tidak dipakai (tabel memakai `DataTable` React dan paginasi server). Pasang lagi dengan `composer require` bila perlu.
- `axios` dan `resources/js/bootstrap.js` bawaan skeleton.

