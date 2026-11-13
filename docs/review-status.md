# Status review fitur

Catatan ini menjaga agar review tidak diulang untuk fitur yang sudah ditinjau, dan tidak terlewat untuk fitur yang belum. Fokus utama sekarang membuat fitur; review dijalankan di akhir, sekali per fitur, atas permintaan pemilik (`/review-fitur`, I-0013 dan I-0017).

Terakhir diperbarui: 2026-11-13. Perbarui berkas ini setiap kali sebuah review selesai atau sebuah fitur baru dibuat.

## Cara memakai

- Sebelum meminta review, lihat tabel: hanya fitur berstatus **belum ditinjau** yang perlu dijalankan.
- Jalankan `/review-fitur <commit acuan>`. Commit acuan di tabel adalah commit tepat sebelum fitur dimulai, jadi hanya perubahan fitur itu yang masuk daftar berkas.
- `/review-fitur` hanya meninjau berkas yang cocok dengan `review.globs` di `.claude/hooks/boost-gate.json`. Perubahan yang seluruhnya frontend, aturan, atau dokumen menghasilkan daftar kosong (tercatat di kolom Catatan). UI diperiksa pemilik sendiri.
- Satu review per fitur. Temuan BLOKIR diperbaiki, lalu satu peninjauan ulang untuk berkas yang diperbaiki; sisanya dilaporkan, tidak ditinjau ketiga kali.
- Review menilai backend. Hasil `PASS` hanya berlaku untuk berkas yang masuk lingkup saat itu.

## Status

Arti status: **ditinjau** = sudah direview dan temuan BLOKIR diperbaiki; **belum ditinjau** = belum pernah direview; **belum tercatat** = riwayat git dan dokumen tidak menyebut review, jadi anggap belum sampai ada bukti lain; **di luar review** = tidak ada berkas dalam lingkup review.

| Fitur | Status review | Bukti | Commit acuan | Catatan |
| --- | --- | --- | --- | --- |
| Login, logout, dan jejak audit awal | ditinjau | review 2026-11-14 (Auth): FAIL lalu PASS | `9a7ffad~1` | Tercakup sebagian oleh review audit logs 2026-11-12 bila berkas auth ikut dalam lingkupnya; lingkup tepatnya tidak tercatat |
| Profil dan ubah kata sandi sendiri | ditinjau | review 2026-11-14 (Auth) | `2ad2e3e~1` | Aturan kata sandi sudah ditinjau lewat fitur Kata sandi di bawah |
| Pengguna dan Peranan (CRUD, alasan jabatan, kunci baris) | ditinjau | commit `a048122` (2026-11-11), perbaikan temuan review | `cdf93cb~1` | Filter Status dan Pulihkan pengguna (`914aa94`) ikut ditinjau 2026-11-14; datatable Peranan belum |
| Kata sandi: aturan, bawaan dari .env, reset, kedaluwarsa 30 hari (D-0030 sampai D-0032) | ditinjau | commit `2dac519` (2026-11-11) | `9b2b54f~1` | |
| Konfirmasi kata sandi untuk aksi sensitif (D-0033) | ditinjau | review 2026-11-14 (Auth) | `c986ee2~1` | Mencakup grup route sensitif (`3b98411`) dan jawaban 423 (`9e5c697`) |
| Audit logs: halaman, ekspor antrean, detail, label jabatan, nilai sebelum dan sesudah | ditinjau | `/review-fitur` vonis PASS (2026-11-12); saran docblock diterapkan (`980a8df`) | `969fd53~1` | Ekspor CSV di antrean (`8a3492f`, `37ba425`) dibuat 2026-11-11; apakah masuk lingkup review itu tidak tercatat |
| Data Kantor, relasi `users.office_id`, filter Kantor di Kelola Pengguna | ditinjau | review 2026-11-14 (Data Kantor): PASS tanpa temuan BLOKIR | `f3d0361~1` | Satu commit dengan rename Jabatan dan menu (UI, di luar review) |
| Menu Administrator dan penamaan Jabatan | di luar review | seluruhnya label, menu, dan teks layar | `f3d0361~1` | Diperiksa pemilik di layar |
| Adopsi template react-starter, IdleTimeout, MaintenanceGate, halaman error, email OTP | ditinjau | review 2026-11-14 (bagian server): FAIL lalu PASS | `dddcb2c~1` | Bagian PHP (batas diam sesi di `/me`, halaman pemeliharaan, email) layak ditinjau; sisanya frontend |
| Penyimpanan: daftar S3 dan SFTP, konfigurasi dari layar, uji koneksi, kembali ke bawaan (D-0040) | ditinjau | review 2026-11-14: FAIL lalu PASS setelah tiga temuan BLOKIR diperbaiki | `f803eeb~1` | Penjaga host dan rahasia terenkripsi adalah bagian yang paling perlu ditinjau |
| Pengelola berkas: daftar, unduh, folder baru, unggah, ubah nama, hapus (D-0041) | ditinjau | review 2026-11-14 (satu review bersama Penyimpanan) | `4035e9b~1` | Hanya diuji di disk tiruan; tidak pernah menulis ke S3 dan SFTP sungguhan |
| Nama penyimpanan (ubah nama) dan tampilan Kotak atau Daftar | ditinjau | review 2026-11-14 (backend; tampilan Kotak atau Daftar frontend, di luar lingkup) | `64133a2~1` | Backend: `RenameStorageSetting`, `StorageNameController`, migrasi `name` |
| Pratinjau berkas (D-0042) | ditinjau | `/review-fitur` 2026-11-13: FAIL lalu PASS setelah dua tes ditambahkan (429 dan penyimpanan gagal) | `896c360~1` | Hanya backend; bagian frontend (laci rincian) di luar lingkup |
| Penyimpanan Lokal, penyimpanan aktif, dan Pengaturan (nama, meta deskripsi, logo, favicon) | ditinjau | review 2026-11-14 (satu review bersama Penyimpanan) | `bc97a71~1` | Backend: `ProjectSettings`, `UpdateProjectSettings`, `UsableStorage`, `SettingController`, `SettingImageController`, `BrandImageController` (rute publik), `ReplaceBrandImage`, `RemoveBrandImage` |
| Menu aksi tabel (`RowActions`, kolom Audit Logs dihapus) | di luar review | `review-scope` untuk `HEAD~1` kosong (2026-11-13) | `376d2eb~1` | Pemilik memeriksa UI sendiri |

## Urutan yang disarankan untuk review di akhir

Berdasarkan risiko, dari yang paling perlu: (1) Penyimpanan seluruhnya dari `f803eeb~1` (menyentuh rahasia, jaringan, dan penulisan ke penyimpanan nyata; pratinjau di dalamnya sudah ditinjau jadi bisa dilewati); (2) Konfirmasi kata sandi; (3) Login, logout, dan Profil; (4) Data Kantor; (5) bagian PHP dari adopsi template. Satu review untuk Penyimpanan lebih murah daripada tiga review terpisah.

## Catatan hasil review

- **Review 1, Penyimpanan dan Pengaturan (2026-11-14):** tiga temuan BLOKIR diperbaiki: kegagalan penyimpanan kini dicatat di log (jenis dan kelas galat, tanpa pesan), nama berkas lebih dari 200 karakter ditolak (bukan dipotong, supaya ekstensi yang diperiksa sama dengan yang disimpan), dan berkas gambar merek dibersihkan bila penyimpanan pengaturannya gagal. Peninjauan ulang: PASS.
- **Keputusan pemilik (2026-11-14):** D-0040 disetujui (berlaku) dan rentang NAT64 kini ditolak.
- **Saran yang belum dikerjakan (tidak memblokir):** tipe parameter `FilesystemAdapter` pada closure di `StorageFileController` dan `StorageFolderController`; alamat NAT64 (`64:ff9b::`) belum masuk daftar awalan metadata di `AssertHostAllowed`; baris baru di `UpdateProjectSettings`, `RenameStorageSetting`, dan `SaveStorageSetting` bisa menghasilkan 500 pada dua permintaan yang tepat bersamaan (constraint unik tetap mencegah duplikat).
- **Review 2, Auth (2026-11-14):** satu temuan BLOKIR diperbaiki: `trustHosts()` dipasang (host selain `APP_URL` dijawab 400; README menjelaskan bahwa `APP_URL` harus alamat sebenarnya). Temuan BLOKIR kedua, header keamanan dari satu middleware aplikasi: pemilik menyetujui pengerjaannya dari sisi kode (2026-11-14), kini ada middleware `SetSecurityHeaders` (**D-0044, berlaku**). Peninjauan ulang atas middleware: FAIL, dua temuan BLOKIR diperbaiki tanpa review ketiga: middleware dipindah ke paling luar (`prepend`) supaya galat dari middleware global (host tak dipercaya, pemeliharaan, isi terlalu besar) ikut membawa header, dan alamat IPv6 dinormalisasi sebelum dicocokkan dengan awalan metadata dan NAT64 (`0064:ff9b::`, `FD00:0EC2::`, dan `::ffff:169.254.x` kini ditolak). Kedua perbaikan punya tes, dan tes header terbukti gagal bila middleware kembali di `append`; `no-store` kemudian dipersempit ke jawaban untuk orang yang sudah masuk. Peninjauan ulang kedua atas perbaikan itu: PASS tanpa temuan BLOKIR.
- **Saran review 2 yang belum dikerjakan:** `mixed $changedAt` di `SetPassword` sebaiknya `?CarbonInterface`; array shape PHPDoc di `UpdatePasswordRequest`; riwayat kata sandi dibaca dan ditulis lewat `DB::table` di tiga tempat (Action, Rule, migration), pertimbangkan model kecil.
- **Review 3, Data Kantor (2026-11-14):** PASS tanpa temuan BLOKIR; filter Status dan Pulihkan pengguna ikut ditinjau. **Menunggu keputusan pemilik:** D-0037 (kantor dihapus permanen hanya bila tidak dipakai, `OfficeSeeder` hanya contoh lokal) masih `diusulkan`. Saran: daftar kantor di dropdown pengguna belum dibatasi jumlahnya (sekitar 7 kantor); `SaveUser` mengosongkan kantor bila `office_id` tidak dikirim (sesuai form sekarang).
- **Review 4, bagian server adopsi template (2026-11-14):** satu temuan BLOKIR diperbaiki: halaman error statis selalu tampil gelap karena generator (`scripts/error-pages.ts`) memasukkan token gelap ke blok terang; kini token terang hanya dari `@theme`, ditambah tes, dan ketujuh halaman dibuat ulang. Peninjauan ulang: PASS. Bagian frontend template (komponen, tema, gerak) di luar lingkup review.
- **Saran review 4 yang belum dikerjakan:** view email teks meng-escape HTML (`O'Brien` tampil `O&#039;Brien`) dan memakai kunci `app.company` yang tidak ada di `config/app.php`; komentar generator masih menyebut `index.css`. Halaman error statis memakai nama dan logo bawaan build, jadi `npm run error-pages` perlu dijalankan ulang setelah mengganti merek.

## Ringkasan 2026-11-14

Semua fitur backend kini ditinjau. Keputusan yang tadinya `diusulkan` sudah diputuskan pemilik 2026-11-14: D-0036 (batas label menu jadi 20 karakter), D-0037 (disetujui), D-0040 (disetujui, NAT64 ditolak), D-0044 (header dari middleware aplikasi). Tidak ada keputusan berstatus `diusulkan` yang tersisa.
