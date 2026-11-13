# PRD: SIPEBRI (Sistem Pemberian Kredit)

Memori bersama proyek untuk semua agent. File ini ditulis ulang di tempat, bukan ditambah seperti log. Standar penulisan kode tidak dicatat di sini, tempatnya di `.ai/rules`.

Terakhir diperbarui: 2026-11-14

Catatan: folder ini adalah salinan SIPEBRI yang dijadikan starterkit di atas Laravel 13 dan SQLite (D-0045, I-0041). Isi PRD di bawah adalah riwayat produk SIPEBRI; proyek turunan menulis ulang bagian Visi, Pengguna, dan Fitur untuk produknya sendiri, dan mengganti merek dengan `npm run rebrand`.

## Visi

SIPEBRI adalah platform pengelolaan proses pemberian kredit BPR Bangunarta, dari pengajuan sampai persetujuan komite. Tujuannya membuat prosesnya terstruktur, efisien, dan terdokumentasi. Dikembangkan oleh tim Teknologi Informasi BPR Bangunarta.

## Pengguna dan peran

| Peran | Yang boleh dilakukan |
| --- | --- |
| Super Admin | Semua akses (`Gate::before` mengizinkan semuanya) |
| Guest | Belum ditentukan |

Peran lain di alur kredit belum didefinisikan. Lihat pertanyaan terbuka.

## Fitur

| Fitur | Status | Catatan |
| --- | --- | --- |
| Pengajuan Kredit | direncanakan | Pencatatan data pengajuan calon debitur |
| Penjadwalan Survey | direncanakan | Jadwal dan penugasan petugas survey |
| Survey Pengajuan | direncanakan | Hasil survey dan verifikasi informasi calon debitur |
| Analisa Kredit | direncanakan | Analisis kelayakan dari hasil survey dan data pendukung |
| Persetujuan Komite | direncanakan | Evaluasi dan keputusan kredit oleh komite |
| Login dan logout | selesai | Email atau username, sesi 15 menit tidak aktif dan 12 jam maksimal, throttling (D-0021) |
| Audit logs | selesai | Perubahan data pengguna dan kejadian autentikasi, tabel satu baris per entri dengan kolom Modul dan Host, filter, urutan, dan detail (D-0023). Export CSV berjalan di antrean dan memberi tahu lewat lonceng (I-0011). Periksa integritas belum ada |
| Profil | selesai | Data akun, ubah kata sandi (D-0001, D-0025), kartu MFA tampil tanpa fungsi; foto profil belum ada (I-0009) |
| Data Kantor | dikerjakan | Model `Office` (nama wajib dan unik; alamat dan koordinat boleh kosong, koordinat unik), CRUD untuk Super Admin, relasi `users.office_id` (kosong atau satu kantor), kolom, isian, dan filter Kantor di Kelola Pengguna, `OfficeSeeder` berisi satu contoh bertanda TODO; tujuh kantor sungguhan menunggu pemilik (2026-11-13, I-0028, D-0037 diusulkan) |
| Template UI | diadopsi sampai react-starter `06d9442` (2026-11-13, I-0033) | Komponen baru, tema gelap, gerak, aturan komentar; yang tidak diadopsi dan alasannya ada di I-0033. Penggabungan berikutnya memakai commit itu sebagai acuan |
| Penyimpanan | selesai | Daftar Lokal (folder proyek, hanya dilihat dan diuji), S3, dan SFTP dengan konfigurasi dari layar (menimpa `.env`, terenkripsi), uji koneksi, kembali ke bawaan (D-0040, diusulkan), dan pengelola berkas sungguhan lewat tombol "Buka": daftar (tampilan kotak atau daftar), unduh, pratinjau gambar, PDF, dan teks (D-0042), folder baru, unggah, ubah nama, hapus (D-0041). Pemilik: agent hanya boleh membaca isi penyimpanan nyata; tes tulis memakai disk tiruan (2026-11-13, I-0034) |
| Menu Administrator | dikerjakan | Grup Kelola Referensi (Data Kantor, Data Jabatan = halaman Peranan yang diganti nama), Kelola Pengguna, Audit Logs, UI Guideline (2026-11-13, I-0027). Di layar, peran selalu ditulis "Jabatan" (nama di kode tetap role); entri audit lama masih "Peranan" |
| Pengguna dan Peranan | selesai | Tambah, ubah, hapus pengguna dengan peran; tambah, ganti nama, hapus peranan; hanya Super Admin (D-0029, I-0014). Reset kata sandi per pengguna, kata sandi bawaan dari .env, wajib ganti saat masuk pertama, kedaluwarsa 30 hari (D-0031, D-0032); aksi sensitif memakai konfirmasi kata sandi (D-0033). Pengguna punya filter Status (Aktif, Dihapus, Semua) dan Pulihkan akun terhapus; Peranan memakai datatable server-side (2026-11-12, I-0023) |
| Pengaturan | selesai | Parameter proyek yang bisa diubah saat berjalan (tabel `settings`, D-0043): nama aplikasi, meta deskripsi, penyimpanan aktif, logo, dan favicon; halaman error statis dan email tetap memakai nama bawaan dari build. Parameter lain menyusul bila pemilik menambah (2026-11-13, I-0038, I-0039) |
| MFA | ditunda | Atas instruksi pemilik, sebelum go-live (D-0022) |
| Permission | ditunda | Dikerjakan di tahap akhir (I-0002) |

## Kondisi saat ini

Skeleton Laravel 12 dengan paket role dan permission (spatie), activity log, dan bahasa Indonesia. Belum ada logika bisnis, model domain, atau antarmuka. Boost dan standar boosts-guidelines sudah dipasang dan gate tervalidasi. Inertia React (Vite 8, TypeScript, Wayfinder) sudah terpasang, lengkap dengan komponen UI, token tema, aturan, dan layout hasil adopsi react-starter. Login, logout, dan audit logs sudah ada. Halaman yang ada: `login`, `dashboard` (masih kosong), `audit-logs`, `roles` (menu Data Jabatan), `users` (menu Kelola Pengguna), `offices` (menu Data Kantor), `storage` (menu Penyimpanan), `storage/{type}/files` (pengelola berkas), `profile`, dan `ui-guideline` (acuan komponen); selain `login`, semuanya hanya untuk yang sudah masuk. Fitur awal (Audit Logs, Peranan, Pengguna) hanya untuk Super Admin lewat peran Spatie (D-0029); izin rinci per fitur bisnis belum ada.

## Keputusan produk

Belum ada. Keputusan teknis dicatat di `docs/decisions.md`.

## Pertanyaan terbuka

- Peran apa saja yang ada di alur kredit, dan hak masing-masing? (pemilik)
- Data apa yang disimpan per debitur, dan di mana berkas dokumen disimpan (S3, SFTP, lokal)? (pemilik)

## Penyiapan production

Seeder tidak dijalankan di production. Data yang dibutuhkan aplikasi dibuat lewat command idempoten yang dijalankan di setiap deploy, setelah `php artisan migrate --force`:

```bash
php artisan roles:sync-base
```

`SEED_DEMO_ACCOUNTS` tidak boleh diaktifkan di production (D-0019). `SESSION_SECURE_COOKIE=true` wajib di production (HTTPS). Retensi audit dijalankan oleh scheduler, jadi server memerlukan cron `* * * * * php artisan schedule:run` (D-0023). Izin per fitur akan disinkronkan lewat jalur yang sama saat permission dikerjakan.

Export audit dan notifikasi memakai antrean (`QUEUE_CONNECTION=database`), jadi production butuh worker yang selalu hidup di bawah supervisor: `php artisan queue:work --tries=3 --timeout=80` (timeout job 80 detik, `retry_after` antrean 90 detik, jangan disamakan atau dilampaui). Berkas export disimpan di disk bawaan (`FILESYSTEM_DISK`, harus disk bersama bila server lebih dari satu) selama 24 jam, lalu dihapus `audit:prune-exports` lewat scheduler (D-0023).

## Utang dan hal yang belum dikerjakan

| Hal | Dikerjakan saat |
| --- | --- |
| Remote repository belum tersambung, jadi semua commit hanya ada di mesin lokal. Pemilik yang menyambungkannya. | Pemilik menyambungkan remote, lalu `git push`. |
| MFA belum ada (D-0022). Akun Super Admin hanya dilindungi password. | Sebelum go-live: dibuat, atau pemilik mencatat keputusan lain. Aplikasi tidak boleh di-deploy sebelum itu. |
| Izin rinci per fitur belum ada: fitur awal hanya dibedakan Super Admin atau bukan (D-0029, I-0002). | Fitur bisnis pertama dikerjakan. |
| Kata sandi bawaan di `.env` diketahui banyak orang bila bocor; risiko diterima pemilik (D-0032). Belum ada lupa kata sandi lewat email. | Alur lupa kata sandi dikerjakan, atau MFA tersedia. |
| Komponen starter yang bergantung API belum dibuat ulang: avatar-editor, idle-timeout, notification-bell, reauth-dialog, password-fields, serta halaman MFA dan profil (D-0015). | Fitur yang membutuhkannya dikerjakan (profil, ganti password, MFA). |
| Urutan kolom Pengguna di audit logs memakai subquery nama yang tidak terindeks, lambat bila catatan sudah jutaan baris (D-0023). | Catatan audit membesar, atau nama pelaku disalin ke kolom terindeks. |
| Pemeriksaan integritas rantai audit belum dibuat. Export audit sudah ada, tetapi belum dijaga izin tersendiri (`audit.md`; TODO di route). | Pemilik memintanya; izin ekspor di tahap permission. |
| Foto profil dan pengaturan MFA di Profil nonaktif (TODO di kode). | Unggah foto dan MFA dikerjakan. |
| Logo lengkap (`logo_full_*`) dan favicon belum dipakai; warna cokelat dan hijau logo belum menjadi token. Warna utama biru logo sudah ditetapkan (D-0027). | Dipakai saat halaman yang membutuhkannya dibuat. |
| `DataTable` dan `FileInput` (D-0016), serta `DateRangePicker`, `TagInput`, dan `FileUploader` (D-0038), melebihi batas 80 baris per fungsi. | Salah satunya diubah secara berarti, atau 2027-05-11. |
| Peringatan sesi habis dihitung di layar dari aktivitas orangnya, tetapi polling lonceng notifikasi tiap 30 detik ikut memperpanjang jam sesi di server selama tab terbuka, jadi batas diam hanya ditegakkan layar (server tetap menutup sesi 12 jam, dan 15 menit bila tab ditutup). | Bila batas diam harus ditegakkan server juga: polling dihentikan saat tidak ada aktivitas, atau tidak memperpanjang sesi. |
| Tes ujung ke ujung (Playwright, `e2e/`) sudah ditulis tetapi belum pernah dijalankan; butuh `npx playwright install` dan database uji. | Dijalankan di akhir, atas keputusan pemilik (2026-11-13). |
| Dari template react-starter belum diadopsi: halaman verifikasi dua langkah (komponen `TwoFactorChallengeForm` sudah ada) dan pengaturan MFA di Profil dengan kode QR (butuh paket `qrcode.react`); unggah foto profil sungguhan di `AvatarEditor`; unggahan sungguhan untuk `FileUploader` (contoh memakai unggahan pura-pura). | Dikerjakan saat fiturnya dibutuhkan (MFA, foto profil, batas sesi, unggah berkas). Sebelum membuatnya, lihat berkas template yang bersangkutan. |
| Tidak dipakai dari template karena memang bukan urusan kode aplikasi ini: konfigurasi nginx, header keamanan, hook git `pre-commit`, `lib/http`, `form`, `resource`, `mock/`, dan `auth/` (memakai fetch dan API tiruan; akses server lewat Inertia). Header keamanan adalah urusan server (keputusan pemilik 2026-11-13). | Tidak dikerjakan. |
| Skill Boost `infer-conventions` belum dijalankan. | Setelah fitur pertama selesai dan ada kode bisnis untuk dibaca. |

## Langkah berikutnya

1. Fitur berikutnya menunggu instruksi pemilik (dicatat di `docs/instructions.md`).
2. Fitur bisnis pertama dan isinya (peran, data debitur, alur) ditentukan pemilik; agent tidak mengarang aturan bisnis.
