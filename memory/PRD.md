# PRD: STARTERKIT

Memori bersama proyek untuk semua agent. File ini ditulis ulang di tempat, bukan ditambah seperti log. Standar penulisan kode tidak dicatat di sini, tempatnya di `.ai/rules`.

Terakhir diperbarui: 2026-11-14

## Visi

STARTERKIT adalah titik awal proyek Laravel 13 dengan Inertia dan React untuk aplikasi internal: dasar yang sudah standar (masuk dan sesi, pengguna dan jabatan, audit, penyimpanan berkas, pengaturan, sistem tampilan) beserta aturan kode, pemeriksaan otomatis, dan tesnya. Proyek turunan menambahkan fitur bisnisnya di atas dasar ini tanpa mengubah standarnya.

Bagian Visi, Pengguna, dan Fitur ditulis ulang oleh proyek turunan untuk produknya sendiri.

## Pengguna dan peran

| Peran | Yang boleh dilakukan |
| --- | --- |
| Super Admin | Semua akses (`Gate::before` mengizinkan semuanya) |
| Guest | Belum ditentukan |

Peran lain ditentukan oleh proyek turunan.

## Fitur bawaan

| Fitur | Status | Catatan |
| --- | --- | --- |
| Login dan logout | selesai | Email atau username, sesi 15 menit tidak aktif dan 12 jam maksimal, batas percobaan bertingkat (D-0021) |
| Konfirmasi kata sandi | selesai | Aksi sensitif meminta kata sandi lagi, berlaku 15 menit (D-0033) |
| Kata sandi | selesai | Minimal 8 karakter, kedaluwarsa 30 hari, riwayat 3, kata sandi awal dari `.env` yang wajib diganti (D-0031, D-0032) |
| Kelola Pengguna dan Data Jabatan | selesai | Tambah, ubah, hapus, pulihkan pengguna; jabatan (role) dikelola Super Admin (D-0029) |
| Data Kantor | selesai | Contoh data referensi lengkap: datatable server-side, filter, relasi ke pengguna (D-0037) |
| Audit logs | selesai | Perubahan data dan kejadian keamanan, filter, detail, ekspor CSV lewat antrean (D-0023, D-0026) |
| Penyimpanan | selesai | Lokal, S3, dan SFTP dengan konfigurasi dan uji koneksi dari layar, serta pengelola berkas dengan pratinjau (D-0040, D-0041, D-0042) |
| Pengaturan | selesai | Nama aplikasi, meta deskripsi, logo, favicon, dan penyimpanan aktif dari layar (D-0043) |
| Profil | selesai | Data akun dan ubah kata sandi sendiri; kartu MFA tampil tanpa fungsi |
| Header keamanan | selesai | Satu middleware untuk semua jawaban (D-0044) |
| Sistem tampilan | selesai | UI Guideline, token tema terang dan gelap, halaman error dan email bermerek, peringatan sesi habis, halaman pemeliharaan |
| MFA | ditunda | Dikerjakan proyek turunan bila dibutuhkan (D-0022) |
| Permission rinci | ditunda | Fitur awal hanya membedakan Super Admin dan bukan (D-0029) |

## Kondisi saat ini

Laravel 13 dengan SQLite, Inertia React (Vite 8, TypeScript, Wayfinder), paket role dan permission (spatie), activity log, dan bahasa Indonesia. Boost dan standar boosts-guidelines terpasang. Semua pemeriksaan lolos: Pint, Larastan, tes PHP, tsc, ESLint, Prettier, pemeriksa standar, tes frontend, dan build. Merek STARTERKIT (teal) memakai logo dan favicon netral yang bisa diganti dari menu Pengaturan atau berkasnya di `public/`.

## Keputusan produk

Belum ada. Keputusan teknis dicatat di `docs/decisions.md`.

## Pertanyaan terbuka

Ditentukan proyek turunan: peran dan hak aksesnya, serta data bisnis yang disimpan.

## Proses latar

Ekspor audit dan notifikasi memakai antrean (`QUEUE_CONNECTION=database`), jadi perlu worker yang hidup: `php artisan queue:work --tries=3 --timeout=80` (timeout job 80 detik, `retry_after` antrean 90 detik, jangan disamakan atau dilampaui). Berkas ekspor disimpan di disk bawaan (`FILESYSTEM_DISK`) selama 24 jam, lalu dihapus `audit:prune-exports` lewat scheduler (`* * * * * php artisan schedule:run`, D-0023). `roles:sync-base` membuat role dasar dan aman diulang.

## Utang dan hal yang belum dikerjakan

| Hal | Dikerjakan saat |
| --- | --- |
| Remote repository belum tersambung, jadi commit hanya ada di mesin lokal. | Pemilik menyambungkan remote, lalu `git push`. |
| MFA belum ada (D-0022): akun Super Admin hanya dilindungi kata sandi. | Proyek turunan memutuskan, atau mencatat keputusan lain. |
| Izin rinci per fitur belum ada (D-0029). | Fitur bisnis pertama dikerjakan. |
| Kata sandi awal di `.env` diketahui banyak orang bila bocor (D-0032). Belum ada lupa kata sandi lewat email. | Alur lupa kata sandi dikerjakan, atau MFA tersedia. |
| Komponen yang bergantung API belum lengkap: unggah foto profil di `AvatarEditor` dan halaman MFA beserta kode QR (butuh paket `qrcode.react`); unggahan sungguhan untuk `FileUploader` (contoh memakai unggahan pura-pura). | Fitur yang membutuhkannya dikerjakan. |
| Urutan kolom Pengguna di audit logs memakai subquery nama yang tidak terindeks, lambat bila catatan sudah jutaan baris (D-0023). | Catatan audit membesar, atau nama pelaku disalin ke kolom terindeks. |
| Pemeriksaan integritas rantai audit belum dibuat. Ekspor audit belum dijaga izin tersendiri (`audit.md`; TODO di route). | Diminta, atau saat permission rinci dikerjakan. |
| `DataTable` dan `FileInput` (D-0016), serta `DateRangePicker`, `TagInput`, dan `FileUploader` (D-0038), melebihi batas 80 baris per fungsi. | Salah satunya diubah secara berarti, atau 2027-05-11. |
| Batas diam sesi ditegakkan layar; polling lonceng notifikasi tiap 30 detik memperpanjang jam sesi di server selama tab terbuka (server tetap menutup sesi 12 jam). | Bila batas diam harus ditegakkan server juga: polling dihentikan saat tidak ada aktivitas, atau tidak memperpanjang sesi. |
| Tes ujung ke ujung (Playwright, `e2e/`) sudah ditulis tetapi belum dijalankan di starterkit; butuh `npx playwright install`. | Dijalankan di akhir proyek turunan. |
| Skill Boost `infer-conventions` belum dijalankan. | Setelah fitur pertama selesai dan ada kode bisnis untuk dibaca. |

## Langkah berikutnya

1. Proyek turunan: ganti merek (`npm run rebrand`), tulis ulang PRD ini, dan isi `DEFAULT_USER_PASSWORD` di `.env`.
2. Fitur bisnis pertama dan isinya ditentukan pemiliknya; agent tidak mengarang aturan bisnis.
