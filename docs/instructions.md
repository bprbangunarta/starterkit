# Catatan instruksi pemilik

Setiap instruksi dicatat di sini sebelum dikerjakan, lalu dampaknya ditinjau dan menunggu persetujuan pemilik. Status: `ditinjau` (menunggu persetujuan), `berlaku` (aturan kerja yang terus dipakai), `dikerjakan`, `selesai`, atau `dijawab` (pertanyaan). Keputusan teknis yang lahir dari sebuah instruksi dicatat di `docs/decisions.md`.

## I-0001: Catat instruksi dan tinjau dampaknya dulu

- Tanggal: 2026-11-11
- Status: berlaku
- Instruksi: "tolong catat setiap intruksi saya jangan langsung dikerjakan tetapi harus ditinjau dulu dampaknya."
- Cara dipakai: instruksi dicatat di berkas ini, dampaknya ditinjau terhadap kode dan keputusan yang ada, lalu pengerjaan baru dimulai setelah pemilik menyetujui tinjauannya.

## I-0002: Permission ditunda sampai akhir

- Tanggal: 2026-11-11
- Status: berlaku
- Instruksi: "kita tidak akan bermain permission dulu nanti diakhir pembatasan hak akses/ permissionya."
- Dampak: semua pengguna yang sudah login boleh mengakses semua halaman dan fitur sampai tahap pembatasan hak akses. Tidak ada izin per fitur, kebijakan (policy), atau pemeriksaan role selain `Gate::before` untuk Super Admin yang sudah ada. Setiap fitur yang dibuat sebelum tahap itu mencatat titik yang nanti perlu dibatasi.

## I-0003: Proses login dan audit logs, MFA ditahan

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "buatkan proses login dan audit logs, untuk mfa kita tahan dulu cukup proses autentikasi."
- Persetujuan: "setujui semua dengan catatan login bisa menggunakan email atau username" (2026-11-11). Tinjauan yang disetujui: login dengan sesi, penutupan halaman dari tamu, audit untuk kejadian autentikasi dan perubahan data pengguna, serta halaman audit logs. Satu kolom login menerima email atau username.
- Keputusan yang lahir dari instruksi ini: D-0021 sampai D-0024.
- Hasil (2026-11-11): login dan logout, audit logs (pencatatan dan halaman), sesi 15 menit dan 12 jam. MFA dan permission sengaja tidak dibuat (D-0022, I-0002).
- Lingkup yang ditinjau: login dan logout dengan sesi, penutupan halaman dari tamu, pencatatan audit untuk kejadian autentikasi dan perubahan data pengguna, serta halaman audit logs untuk melihat catatannya.
- Di luar lingkup: MFA, permission (I-0002), registrasi, lupa dan ganti password, ekspor audit, dan pemeriksaan integritas rantai.

## I-0004: Cara audit logs dibuat dan dipakai di semua proses

- Tanggal: 2026-11-11
- Status: dijawab (di tinjauan I-0003, dirangkum di D-0023)
- Instruksi: "dan bagaimana audit logs ini dibuat dan diimplemetasikan pada semua proses nanti, saya sudah pasang depedensinya"
- Catatan: dependensinya `spatie/laravel-activitylog` 5.1.4, yang sudah terpasang sejak awal bersama tabel `activity_log`.

## I-0005: Header yang dihapus padahal template sudah lengkap

- Tanggal: 2026-11-11
- Status: selesai (header, lewat I-0008; Periksa integritas ditunda)
- Instruksi: "kenapa bagian header ini kau hapus? padahal template sudah lengkap dan intruksi saya juga diawal adopsi ikuti semuanya"
- Temuan: instruksi awal ("adopsi semua komponen dan aturannya") belum dipenuhi di dua tempat. Header halaman audit logs di template punya tombol "Periksa integritas" dan "Ekspor CSV", dan header aplikasi punya lonceng notifikasi serta menu avatar (email, Profil, Keluar) yang sekarang hanya berisi nama dan Keluar.

## I-0008: Header lengkap seperti template, dan tombol Ekspor di audit logs

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "dibagian header jangan dihilangkan ambil semuanya meskipun link/halamannya belum dibuat. dibagian audit log cukup tambahkan tombol export tapi fungsinya tidak dibuat dulu". Lanjutan: "perbaikan UI tidak perlu pengujian/audit ulang kode".
- Tinjauan dampak: header aplikasi mengambil lonceng notifikasi, menu avatar (email, Profil, Keluar), dan pengingat verifikasi dua langkah dari template. Tanpa backend, lonceng selalu kosong dan Profil serta Aktifkan tampil tetapi tidak menuju ke mana pun (nonaktif, bertanda TODO). Halaman audit hanya mendapat tombol "Ekspor CSV" nonaktif; "Periksa integritas" tidak ditambahkan. Server membagikan email pengguna ke props. Tidak ada test baru dan tidak ada reviewer untuk perubahan tampilan ini.

## I-0009: Halaman Profil seperti template

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "hilangkan email cukup profil dan keluar tetapi perhatikan tingginya item list nya sama" dan "sekalian aja buat halaman profilnya dulu samakan dengan template"
- Tinjauan dampak: menu avatar hanya berisi Profil dan Keluar dengan tinggi sama, jadi email tidak lagi dikirim ke semua halaman. Halaman `/profile` memuat tiga kartu template: Akun (data hanya-baca), Kata sandi (berfungsi), dan Verifikasi dua langkah (tampil, tombol nonaktif karena MFA ditunda, D-0022). Ubah kata sandi butuh backend sungguhan: kata sandi saat ini wajib benar, kebijakan D-0001 (minimal 15 karakter, `Password::defaults()` kini dipasang), tidak sama dengan yang lama, dibatasi 6 per menit, sesi di perangkat lain berakhir, dan tercatat satu entri audit tanpa kata sandi. Foto profil belum ada (butuh penyimpanan privat), tombolnya nonaktif. Pengingat MFA kini menautkan ke Profil.

## I-0010: Catat percobaan gagal ganti kata sandi

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "ya kerjakanlah kan harus tuntas" (menjawab usulan agent mencatat percobaan gagal ganti kata sandi, sesuai `logging.md`)
- Tinjauan dampak: kata sandi saat ini yang salah di Profil dicatat sebagai kejadian audit `password_change_failed` (modul Autentikasi, hasil Ditolak, pelaku dan subjek akun itu sendiri) tanpa menyimpan teks yang diketik. Kesalahan form lain (terlalu pendek, konfirmasi beda) tidak dicatat. Pembatas 6 per menit tetap menjadi batas tebakan.

## I-0011: Dialog audit tanpa footer, ekspor CSV, dan pembersihan yajra

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: hapus footer dialog detail audit (tombol Tutup sudah ada ikon X); pastikan isi perubahan data tetap rapi dan boleh menggulir bila banyak; aktifkan fitur ekspor dengan teks tombol "Export" dan pastikan perlu dependensi baru atau tidak; jawab apakah audit logs sudah SSR dan apakah yajra dipakai (hapus bila tidak).
- Tinjauan dampak: footer dialog dihapus, dan tabel perubahan diberi tinggi maksimum dengan gulir sendiri. Ekspor memakai fungsi bawaan PHP dan Laravel (`streamDownload`, `fputcsv`, `lazyByIdDesc`), jadi tidak ada dependensi baru (`maatwebsite/excel` hanya perlu untuk .xlsx, yang tidak diminta). Ekspor mengikuti filter yang sedang dipakai, maksimal 50.000 baris (ditolak dengan pesan bila lebih), menetralkan sel berawalan `=`, `+`, `-`, `@`, dibatasi 6 per menit, dan dicatat sebagai kejadian `exported` tanpa teks pencarian. Atas pesan lanjutan pemilik ("jangan sampai menunggu di halaman dan refresh membatalkan"), ekspor tidak lagi diunduh langsung: tombol hanya membuat permintaan, job antrean menyusun berkas di disk privat, lalu lonceng notifikasi memberi tautan unduh yang hanya bisa dibuka peminta dan berlaku 24 jam; berkas lama dihapus terjadwal. Lonceng pun kini berfungsi (notifikasi database). Dampak operasional: production butuh worker antrean. Izin khusus ekspor ditunda bersama permission (I-0002, D-0024). Yajra tidak dipakai kode mana pun, jadi paket, provider, dan konfigurasinya dihapus. Daftar di tabel sudah diproses di server (paginasi, urutan, filter lewat SQL); render Inertia SSR (HTML dari Node) belum aktif dan tidak dibutuhkan halaman di balik login.

## I-0012: Merek dari satu tempat

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: memakai logo dan warna dari folder `public/`.
- Tinjauan dampak: huruf "S" diganti logo bundar di sidebar dan layar masuk/galat (`BrandMark`). Warna utama diatur lewat token `--color-primary`, `-hover`, dan `-soft`, jadi seluruh antarmuka mengikuti satu warna merek (D-0027).
## I-0013: Review sekali per fitur, UI tanpa review

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "UI memang tidak perlu direview ... tidak perlu diubah biarkan saja karena memang tujuannya baik, ini hanya pengecualian saya saja ... review dilakukan ketika satu fitur sudah selesai baru dilakukan review dan jika hasil review ada temuan langsung diperbaiki dan hasilnya final"
- Tinjauan dampak: gerbang Boost (`.claude/hooks/boost-gate.json`) tidak diubah dan tetap menuntut review untuk berkas yang disentuh. Cara kerja agent yang berubah: tidak meluncurkan reviewer di tengah fitur atau untuk perubahan tampilan; satu review saat fitur lengkap, temuan langsung diperbaiki tanpa review ulang. Test tetap wajib lolos. Konsekuensi: gerbang akan menahan giliran sampai tiga kali pada perubahan tampilan lalu melepas sendiri, dan hasil review tidak dibaca gerbang (laporannya datang belakangan).

## I-0014: Menu Pengguna dan Peranan

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "buatkan dulu menu baru Peranan dan Pengguna kemudian bereskan fungsinya"; lalu "ga ada konfigurasi izin, kan sudah dibilang ga usah mikirin perizinan dulu"
- Tinjauan dampak: dua menu baru di grup Sistem (Peranan lebih dulu karena Pengguna memakainya): `/roles` dan `/users`. Peranan: daftar dengan jumlah pengguna, tambah, ganti nama, hapus; peran dasar (Super Admin, Guest) tidak bisa diganti nama atau dihapus, dan peran yang masih dipakai tidak bisa dihapus. Pengguna: daftar dengan cari, filter peran, urutan, dan paginasi dari server; tambah (nama, username, email, kata sandi awal, peran), ubah (tanpa kata sandi), hapus (soft delete); tidak bisa menghapus diri sendiri atau Super Admin terakhir, dan Super Admin terakhir tidak bisa kehilangan perannya. Perubahan data dicatat otomatis oleh audit pengguna; perubahan peran pengguna dan CRUD peranan dicatat sebagai kejadian audit. Atas penjelasan pemilik, semua fitur awal dikerjakan Super Admin, jadi route Pengguna, Peranan, Audit Logs, dan ekspornya dijaga middleware `role:Super Admin` dari Spatie (D-0029); menu itu juga disembunyikan dari yang bukan Super Admin. Perizinan yang lebih rinci menunggu fitur bisnis. Tidak ada perubahan `config/permission.php`. Hanya kolom pengguna yang sudah ada dipakai (tidak ada kolom bisnis baru). Setelah review: perubahan peran wajib menyertakan alasan (tercatat di audit), kunci baris menjaga minimal satu Super Admin, simpan dan hapus dibungkus transaksi, route tulis dibatasi 60 per menit, dan jumlah peran per pengguna dibatasi 20. `password.confirm` belum dipasang (D-0030, `diusulkan`). Belum ada: ganti kata sandi pengguna lain dan paksa ganti kata sandi awal.

## I-0015: Test memakai MySQL, bukan SQLite

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "database yang digunakan mysql tapi ditest di sqlite ini ga sesuai, emang sengaja atau gimana?"
- Temuan: SQLite dalam memori adalah bawaan skeleton Laravel (`phpunit.xml`) yang tidak pernah diubah, bukan keputusan yang disengaja. `tests.md` meminta test dijalankan di mesin yang sama dengan production, dan perbedaannya sudah terbukti menyembunyikan dua bug (ID ekspor ULID di kolom `subject_id` bertipe angka, dan keunikan nama peranan yang bergantung pada collation).
- Tinjauan dampak: `phpunit.xml` memaksa MySQL dengan database uji terpisah dari database kerja, karena `RefreshDatabase` mengosongkan databasenya. Catatan: digantikan D-0045 (SQLite).

## I-0016: Koreksi kata sandi, peran, dan tampilan

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: tombol Export, Tambah (Peranan, Pengguna) berwarna primary; keterangan data Export; judul kartu dan modal diawali huruf kapital; kata sandi minimal 8; kata sandi awal dari .env dan wajib diganti saat masuk pertama (nilai bawaan yang dipilih pemilik hanya ada di `.env` lokal); fitur reset kata sandi; kedaluwarsa 30 hari dan tidak boleh sama dengan 3 terakhir; pilihan peran memakai combobox, bukan kotak centang; peranan yang masih terhubung tidak boleh bisa dihapus; periksa aturan relasi antar database.
- Tinjauan dampak: lihat D-0031 dan D-0032. Export mengikuti filter dan mencakup semua baris yang cocok (bukan hanya halaman), dan sekarang tombolnya menjelaskannya. Pilihan peran memakai komponen baru `MultiCombobox` (pencarian, pilihan ganda, label yang bisa dihapus) tanpa dependensi baru. Penghapusan peranan yang dipegang pengguna kini ditolak juga oleh database (`model_has_roles.role_id` RESTRICT), dan satu test memastikan semua kolom `*_id` adalah foreign key kecuali daftar pengecualian beralasan. `.env.example` dikosongkan; test memakai nilai uji sendiri.

## I-0017: Review sekali setelah fitur dinyatakan sesuai, dan catatan saran guideline

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: review memakai subagent adalah yang paling boros dan mengesalkan; review dilakukan setelah fitur dinyatakan `sesuai` (revisi UI dan penyesuaian selesai), temuan langsung diperbaiki, baru dinyatakan `selesai`. Minta catatan saran penyempurnaan `boosts-guidelines-1.1.0` dari pengalaman ini untuk diperbarui pemilik ke versi baru.
- Tinjauan dampak: siklus status satu fitur menjadi `dikerjakan`, `sesuai`, `ditinjau`, `selesai` (lihat `docs/saran-boosts-guidelines.md`, bagian A). Agent tidak meluncurkan reviewer sebelum pemilik menyatakan `sesuai`, memperbaiki temuan tanpa review ulang, lalu menyatakan `selesai`. Catatan saran ditulis di `docs/saran-boosts-guidelines.md`; guideline asli dan gerbang tidak diubah.

## I-0018: Bangun konfirmasi kata sandi untuk aksi sensitif

- Tanggal: 2026-11-12
- Status: dikerjakan
- Instruksi: "boleh dikerjakan" (menjawab pertanyaan D-0030); dan agar versi 1.2.0 tidak diubah (review hanya saat diminta lewat `on_request`).
- Tinjauan dampak: lihat D-0033. Tujuh route (tambah, ubah, hapus pengguna; atur ulang kata sandi; tambah, ubah, hapus peranan) memakai `password.confirm`; dialog `ReauthDialog` meminta kata sandi sebelum aksi, dan server menjawab 423 bila konfirmasi habis di tengah jalan. Props bersama `auth.user.confirmed_until` memberi tahu layar sampai kapan konfirmasi berlaku. Review tidak dijalankan sampai pemilik menyatakan fitur `sesuai` (`/review-fitur`).

## I-0019: Halaman harus mengikuti template, buka template dulu

- Tanggal: 2026-11-12
- Status: selesai
- Instruksi: "kenapa UI nya buat sendiri? kan ditemplate sudah lengkap" dan "halaman login juga berbeda saya cek".
- Temuan: halaman kata sandi kedaluwarsa dan halaman login dibuat tanpa membuka `react-starter` lebih dulu, padahal instruksi awal menyuruh mengadopsi semua komponen template (sama dengan I-0005).
- Tindakan: `login` kini mengikuti template (kartu berpadding, label "Kredensial", placeholder, tombol nonaktif sampai terisi). `password-expired` memakai kartu, peringatan, ilustrasi `password-expired`, dan footer dari template; satu varian tambahan untuk akun yang baru dibuat atau di-reset (template tidak punya). `confirm-password` memakai pola kartu yang sama. Dialog konfirmasi disesuaikan dengan `reauth-dialog` template (pemicunya lewat props Inertia, bukan event dari klien HTTP template). Dialog tambah dan ubah pengguna serta peranan diberi subjudul dan `aria-invalid` seperti contoh CRUD di UI Guideline. Satu beda disengaja: tanpa kotak "Ingat saya" (D-0021).
- Aturan kerja: sebelum membuat layar atau komponen baru, buka padanannya di `/Users/Bananay/Claude/react-starter/src` dan adaptasi; hanya yang tidak ada di template yang dibuat baru, dan itu disebut di laporan.

## I-0020: Konfirmasi kata sandi cukup modal di atas, MultiCombobox seragam

- Tanggal: 2026-11-12
- Status: selesai
- Instruksi: "konfirmasi sandi saat aksi sensitif cukup pakai modal aja, gunakan posisi modal top untuk semua yang sudah diterapkan"; "MultiCombobox untuk Peranan boleh dibuat dengan memperhatikan desain komponen yang serupa jangan sampai berbeda sendiri".
- Tinjauan dampak: halaman `/confirm-password` dan rutenya dihapus. Middleware `EnsurePasswordIsConfirmed` menjawab 423 (`reauth_required`) alih-alih mengalihkan; satu `ReauthProvider` di layout memegang modal konfirmasi (tampil di atas seperti semua dialog) dan membukanya juga saat server menjawab 423. Semua tujuh aksi sensitif memakai modal yang sama. `MultiCombobox` ditulis ulang mengikuti `Combobox`: pemicu, kotak pencarian, baris daftar, tombol papan ketik, dan tombol hapus yang sama; label terpilih tampil di satu baris pemicu seperti satu label di `Combobox`, tanpa label di bawah kolom. Bug yang ditemukan di jalan: middleware memakai `time()` yang tidak ikut jam uji, diganti `now()`.

## I-0021: Semua modal berposisi di atas

- Tanggal: 2026-11-12
- Status: selesai
- Instruksi: "gunakan posisi modal top untuk semua yang sudah diterapkan" (maksudnya semua modal di aplikasi, bukan hanya modal konfirmasi kata sandi); lalu "kalau reusable satu diubah semuanya ikut berubah".
- Temuan: agent pertama memindahkan hanya modal konfirmasi lewat prop `placement` (salah baca). Semua dialog memang lewat dua komponen bersama, `Modal` dan `ConfirmDialog`, yang memakai satu konstanta kelas di `components/ui/dialog.tsx`.
- Tindakan: konstanta itu kini menaruh dialog di atas (`top-4`, dari `sm` `top-12`) dan prop `placement` dihapus, jadi semua dialog (Pengguna, Peranan, konfirmasi kata sandi, detail audit, contoh di UI Guideline) ikut berubah dari satu tempat. Satu-satunya `Dialog.Content` di luar komponen bersama adalah laci menu ponsel di `app-layout.tsx`, yang memang laci di sisi kiri, bukan dialog. Aturannya ditulis di `.ai/rules/frontend-react.md`.

## I-0022: Profil harus sama dengan template

- Tanggal: 2026-11-12
- Status: selesai
- Instruksi: "halaman profil saya cek masih tidak sama, apakah instruksi adopsi template ini kurang jelas atau gimana sih? saya sudah mulai muak".
- Temuan: instruksinya jelas; kegagalan ada pada agent. Profil dulu hanya diadaptasi dari sebagian berkas template (baris 160-310), sehingga baris foto dan baris email di kartu verifikasi dua langkah tidak sama.
- Tindakan: setelah membaca `pages/profile.tsx` dan `components/avatar-editor.tsx` template utuh, baris foto kini memakai `AvatarEditor` (avatar `size-14`, "Foto profil", petunjuk JPG/PNG/WebP, tombol "Unggah foto" dengan ikon kamera, nonaktif karena unggah belum ada), tombol "Atur" baris email memakai ikon `KeyRound`, dan teks email menyebut alamat pemiliknya. Placeholder tanggal di audit logs disamakan ("Dari", "Sampai"; label aksesibilitas "Tanggal dari/sampai"). Beda yang disengaja: teks catatan kartu kata sandi ("Perangkat lain akan keluar..." sesuai perilaku nyata), deskripsi dashboard dan audit logs (tanpa kata "Placeholder" atau klaim "anti-rusak" yang belum ada), tombol "Export" dan tanpa "Periksa integritas" (instruksi pemilik), dan dialog detail audit tanpa footer (instruksi pemilik).

## I-0023: Peranan memakai datatable, Pengguna perlu filter status dan pulihkan

- Tanggal: 2026-11-12
- Status: dikerjakan
- Instruksi: "halaman peranan gunakan datatabel, halaman pengguna bisa hapus pengguna tapi tidak ada filter status aktif atau tidak aktif gimana ini? ga bisa direstore juga".
- Tinjauan dampak: hapus pengguna sudah berupa hapus lunak (baris tetap ada), tetapi daftar menyembunyikannya dan email atau username-nya tetap terpakai (unique di database), jadi akun terhapus tidak bisa dilihat, dipulihkan, atau dibuat ulang. Tindakan: filter Status (Aktif bawaan, Dihapus, Semua), kolom Status yang bisa diurutkan, aksi Pulihkan (dengan konfirmasi kata sandi, tercatat sebagai `restored` di audit) yang hanya tampil di baris terhapus; ubah, atur ulang kata sandi, dan hapus untuk akun terhapus menjawab 404 sampai dipulihkan. Peranan kini memakai DataTable penuh seperti Pengguna (pencarian, urutan, paginasi dari server); hook navigasi dan pencarian dipakai bersama lewat `hooks/use-list-navigation.ts` dan `hooks/use-debounced-search.ts`.
- Keputusan teknis agent: "tidak aktif" diartikan akun yang dihapus (belum ada konsep nonaktif terpisah dari hapus). Bila pemilik ingin menonaktifkan sementara tanpa menghapus, itu fitur baru dan perlu keputusan pemilik.
- Lanjutan (pemilik menyetujui): audit logs ikut memakai hook navigasi, hook pencarian, dan konstanta paginasi bersama (`lib/list.ts`), jadi tidak ada salinan lagi. Dua berkas template bernama generik (`lib/utils.ts`, `components/ui/misc.tsx`) dikecualikan lewat D-0034.

## I-0024: Alasan saat tambah pengguna, dan audit peran tanpa nilai sebelum dan sesudah

- Tanggal: 2026-11-12
- Status: dikerjakan
- Instruksi: pada tambah pengguna muncul "Alasan perubahan peran" padahal menambah; dan detail audit untuk ubah peranan dan peran pengguna menulis "Tidak ada perubahan data pada catatan ini", padahal log seharusnya mencatat nilai sebelum dan sesudah.
- Temuan alasan: aturan `audit.md` meminta alasan untuk ganti peran, dan memberi peran saat akun dibuat termasuk pemberian hak akses, jadi alasannya tetap diminta, tetapi labelnya salah. Kini berbunyi "Alasan pemberian peran" bila pengguna belum punya peran (akun baru), dan "Alasan perubahan peran" bila sudah.
- Temuan audit (bug): entri manual (peranan dibuat, diubah, dihapus; peran pengguna diubah) menaruh nilai lama dan baru di `properties`, sedangkan detail membaca kolom `attribute_changes`, sehingga selalu tampak kosong. Kini `RecordAudit` menerima `changes` dan menyimpannya di kolom itu: nama peranan sebelum dan sesudah, dan daftar peran pengguna sebelum dan sesudah (label "Peran", daftar tampil satu baris). Alasan perubahan ikut tampil sebagai baris "Alasan" di detail. Test regresi ditulis lebih dulu dan gagal sebelum perbaikan.
- Catatan: entri lama yang sudah tertulis di database kerja tetap tampak kosong (log hanya bisa ditambah, tidak diubah); entri baru benar.

## I-0025: Nama dan email di audit logs jangan disamarkan, dan log peranan kosong

- Tanggal: 2026-11-12
- Status: dikerjakan
- Instruksi: log pengguna menampilkan "[disembunyikan]" untuk nama dan email padahal bukan kredensial; karyawan terikat kontrak, penggunaan datanya diizinkan, dan hanya Super Admin yang melihat log; ekspor juga ditanyakan. Log peranan dilaporkan masih kosong.
- Tinjauan dampak: penyamaran nama dan email berasal dari aturan `audit.md` dan `security.md` (data pribadi tidak ditulis). Pemilik memutuskan menyimpang untuk jejak audit; dicatat sebagai D-0035 (berlaku). `password` tetap disamarkan. Ekspor tidak memuat nilai sebelum dan sesudah, jadi tidak terpengaruh. Log peranan: entri #22 sampai #29 di database kerja ditulis sebelum perbaikan I-0024 (kolom perubahannya `[]`); tidak ada entri peranan setelah perbaikan, jadi yang dilihat adalah entri lama. Label subjek "Role #5" diganti "Peranan #5".
- Pemeriksaan kesesuaian semua jenis entri (pemilik menanyakan, 2026-11-12): entri perubahan data (pengguna lewat model, peranan, peran pengguna) sudah menyimpan nilai sebelum dan sesudah. Entri kejadian (masuk, keluar, gagal masuk, terkunci, kata sandi gagal diganti atau dikonfirmasi, kata sandi diatur ulang) memang tidak punya nilai sebelum dan sesudah. Dua celah ditutup: permintaan ekspor kini menampilkan jumlah baris dan filternya di detail (`details`), dan peranan yang dihapus kini mencatat ID peranannya sebagai subjek. Entri lama sebelum perbaikan tetap kosong.

## I-0026: Komentar berkas template harus mengikuti antislop

- Tanggal: 2026-11-13
- Status: selesai
- Instruksi: "ini bawaan template banyak banget komentarnya, ga pake antislop?"
- Temuan: kerapatan komentar frontend 312 baris dari 6.763 baris kode (sekitar 4,6%), jadi bukan banyak. Tetapi pemindaian sebelumnya hanya memeriksa komentar `//`, bukan blok `/** */`, sehingga delapan blok bertulisan tiga baris atau lebih terlewat (tujuh dari template, satu `menu.ts` yang daftar aturannya sudah ada di `.ai/rules/frontend-react.md`). Aturan antislop: satu baris, dua bila baris kedua membawa fakta baru.
- Tindakan: kedelapan blok dipangkas menjadi dua baris dengan fakta intinya; kode tidak disentuh. Test frontend, standar, dan Prettier lolos.

## I-0027: Susunan menu baru dan halaman Data Kantor

- Tanggal: 2026-11-13
- Status: dikerjakan
- Instruksi: label "Sistem" menjadi "Administrator"; grup "Kelola Referensi" berisi submenu "Data Kantor" dan "Data Jabatan" (sebelumnya Peranan); di bawahnya "Kelola Pengguna" (sebelumnya Pengguna); halaman placeholder untuk Data Kantor; judul dan hal lain yang perlu ikut disesuaikan. Pemilik menambahkan: "route tidak perlu diubah".
- Tinjauan dampak: route yang ada (`/roles`, `/users`) dan nama route tidak diubah, jadi berkas `pages/roles.tsx` dan `pages/users.tsx` tetap. Satu route baru `/offices` (nama `offices`, berkas `pages/offices.tsx`) hanya untuk Super Admin, dibutuhkan agar halaman Data Kantor bisa dibuka. Judul halaman dan teks layar mengikuti nama menu: "Data Jabatan" (judul, dialog tambah dan ubah, hapus, toast, pencarian, keadaan kosong, tooltip, pesan galat server di halaman itu) dan "Kelola Pengguna" (judul). Pilihan peran di form pengguna menunjuk ke "menu Data Jabatan". Menu induk yang semua anaknya khusus Super Admin ikut tersembunyi bagi pengguna lain; fungsi penyaring menu dipindah ke `layouts/menu.ts` dan diuji. Data Kantor hanya halaman kosong bertanda TODO: isi kantor ditentukan pemilik.
- Lanjutan (pemilik: "di kode tidak apa-apa namanya masih role, tapi di UI ganti jadi jabatan"): semua tulisan layar "Peran" dan "Peranan" menjadi "Jabatan": kolom, isian, filter, dan alasan di Pengguna, label perubahan di detail audit, modul, keterangan, dan subjek audit, serta nama atribut validasi. Nama di kode (Role, RoleController, `roles`, `role_changed`) tidak diubah. Entri audit lama tetap memakai "Peranan" karena log hanya bisa ditambah; modul "Peranan" dan "Jabatan" akan tampil berdampingan di filter modul sampai entri lama terhapus oleh retensi.
- Label grup "Kelola Referensi" 16 karakter, usulan pengecualian di D-0036 (diusulkan).

## I-0028: Model dan CRUD Kantor, dan relasinya dengan pengguna

- Tanggal: 2026-11-13
- Status: dikerjakan
- Instruksi: buat model dan CRUD kantor (nama, alamat, longitude, latitude); pengguna boleh tanpa kantor atau hanya satu kantor; buat relasinya; buat satu seeder contoh karena data kantor hanya tujuh dan jarang berubah, pemilik akan melengkapi datanya.
- Tinjauan dampak: tabel `offices` (hanya nama yang wajib dan unik; alamat kosong boleh; latitude dan longitude `decimal(10,7)` boleh kosong, harus diisi berpasangan atau dikosongkan keduanya, dan pasangannya unik: dua kantor tidak boleh sama titiknya, berapa pun jumlah desimal yang diketik, sedangkan banyak kantor tanpa koordinat boleh) dan `users.office_id` (boleh kosong, kunci asing RESTRICT). Satu kantor punya banyak pengguna, satu pengguna paling banyak satu kantor. Halaman Data Kantor memakai datatable server-side (cari, urut, paginasi) dengan tambah, ubah, hapus untuk Super Admin, dan aksi tulis memakai konfirmasi kata sandi (D-0033) serta tercatat di audit logs dengan modul "Kantor". Form dan daftar Kelola Pengguna mendapat isian dan kolom Kantor (bisa dikosongkan, bisa diurutkan). Koordinat dicek di server (derajat desimal, rentang, paling banyak tujuh desimal) dan dibatasi juga di isian. Ejaan di kode `longitude` (bukan "longtitude"). `OfficeSeeder` berisi satu kantor contoh bertanda TODO; pemilik mengisi tujuh kantor sungguhan. Aturan seeder dan keputusan hapus ada di D-0037 (diusulkan). Satu bug tertangkap oleh test sebelum selesai: aturan `between` tanpa `numeric` menghitung panjang teks, sehingga lintang 91 lolos; kini ada `numeric` dan test untuk batasnya.
- Lanjutan (pemilik: "hanya nama yang wajib dan uniq, koordinat uniq nullable, alamat nullable" dan "sesuaikan juga pengguna filter kantor"): aturan di atas ditulis ulang dan filter Kantor ditambah di Kelola Pengguna (satu kantor, atau "Tanpa kantor"), bisa digabung dengan filter lain dan masuk ke tombol Atur ulang. Migrasi kantor belum pernah dijalankan di database kerja, jadi diubah langsung.
- Belum dikerjakan: nama kantor di detail audit pengguna (yang tersimpan ID kantor, berlabel "ID kantor").

## I-0029: Penghitung karakter Textarea di kanan atas

- Tanggal: 2026-11-13
- Status: dikerjakan
- Instruksi: penghitung batas karakter pada textarea jangan di pojok kanan bawah karena merusak jarak yang sudah ada; taruh di kanan atas, dan simpan sebagai aturan.
- Tinjauan dampak: perubahan di komponen bersama `components/ui/textarea.tsx`, sehingga semua Textarea ikut (kini Alamat kantor dan contoh di UI Guideline). Penghitung tampil di kanan atas, sejajar dengan label `Field`, dan tidak lagi memakai ruang di bawah isian, sehingga jarak ke isian berikutnya sama dengan isian lain. Ini sengaja berbeda dari template react-starter, yang menaruhnya di bawah. Aturannya ditulis di `.ai/rules/frontend-react.md` (bagian Tanpa keterangan di bawah isian). Tes baru untuk hitungan, penyembunyian dari pembaca layar, dan ketiadaan penghitung tanpa batas.
- Catatan: penghitung diposisikan di atas isian, jadi Textarea tanpa `Field` berlabel di atasnya akan menempel di elemen sebelumnya; saat ini semua pemakaiannya di dalam `Field`.

## I-0030: Pemeriksaan menu yang disesuaikan pemilik

- Tanggal: 2026-11-13
- Status: selesai
- Instruksi: "cek menu ada beberapa yang sudah saya sesuaikan".
- Temuan: pemilik menambah entri "User Profile" dan memecah bagian menu menjadi Administrator serta "Audit & Design" (Audit Logs dan UI Guideline). Entri "User Profile" masih menunjuk ke alamat Dashboard dengan ikon Dashboard (salinan baris di atasnya), sehingga dua entri membuka halaman yang sama dan keduanya tampak aktif di `/`. Tes `menu.test.ts` saya masih memakai susunan lama.
- Tindakan: "User Profile" kini menuju halaman `profile` (alamat `/profile`, route `profile`, berkas `pages/profile.tsx`, sesuai aturan menu) dengan ikon `UserRound`; judul halaman Profil menjadi "User Profile" agar sama dengan label menu. Susunan dan nama bagian buatan pemilik tidak diubah. Tes menu diperbarui ke susunan baru dan ditambah pemeriksaan bahwa setiap tautan menuju alamat sendiri.

## I-0031: Posisi paginasi tabel

- Tanggal: 2026-11-13
- Status: dikerjakan
- Instruksi: komponen paginasi (teks "1-1 dari 1" dan pilihan "25 / halaman") diminta pindah posisi bila reusable. Percobaan pertama (teks di kiri, pilihan di ujung kanan) salah mengartikan; yang diminta pemilik hanya menukar keduanya: "tinggal dituker aja posisinya".
- Tinjauan dampak: komponen reusable, yaitu satu `components/ui/pagination.tsx` yang dipakai `DataTable`, jadi Pengguna, Jabatan, Kantor, dan Audit Logs ikut berubah dari satu tempat. Susunan baru: di kiri pilihan "baris per halaman" lalu teks jumlah baris, di kanan nomor halaman (bila lebih dari satu halaman). Ini sengaja berbeda dari template, yang menaruh teks lebih dulu lalu pilihan. Aturannya ditulis di `.ai/rules/frontend-react.md` (bagian Paginasi), dan ada tes urutannya.

## I-0032: Menu Penyimpanan dan halaman placeholder

- Tanggal: 2026-11-13
- Status: dikerjakan (placeholder)
- Instruksi: "saya merubah menu tolong dicek, dan untuk menu penyimpanan buatkan dulu saja halaman placeholder, ini untuk mengatur dan test koneksi penyimpanan S3 dan SFTP yang default kalau belum di isi ada di env kalau sudah di isi nanti yang dipakai konfigurasi yang di ui".
- Temuan menu: pemilik mengganti nama bagian menjadi "Design Sistem" dan menambah entri "Penyimpanan" di dalamnya. Entri itu masih menunjuk ke alamat Audit Logs dengan ikon yang sama (salinan baris di atasnya), sehingga dua entri membuka halaman yang sama; tes menu saya masih memakai susunan lama.
- Tindakan: halaman `storage` (alamat `/storage`, route `storage`, berkas `pages/storage.tsx`, judul "Penyimpanan", ikon `HardDrive`), hanya untuk Super Admin, isinya kosong bertanda TODO. Entri menu menuju halaman itu; susunan dan nama bagian buatan pemilik tidak diubah. Tes menu diperbarui, dan ada tes akses halaman.
- Aturan yang diminta untuk pengerjaan nanti: konfigurasi S3 dan SFTP bawaan dari `.env`; bila sudah diisi di layar, konfigurasi dari layar yang dipakai; ada tombol uji koneksi.
- Catatan desain untuk saat dikerjakan (belum diputuskan pemilik): (1) rahasia (kunci akses, kata sandi, kunci privat) disimpan terenkripsi (cast `encrypted`), tidak pernah dikirim kembali ke browser, dan perubahannya masuk audit tanpa nilai rahasia (`security.md`); (2) uji koneksi memanggil sistem luar: batas total 10 detik dan sambungan 3 detik, dan karena tujuan diisi pengguna, perlu allow-list host dan penolakan alamat internal dan metadata cloud (`security.md`); (3) bagaimana menggabungkan konfigurasi layar dan `.env` (per field atau satu paket), dan apa yang terjadi pada berkas yang sudah tersimpan di disk lama bila konfigurasi berganti, perlu keputusan pemilik; (4) perubahan konfigurasi penyimpanan layak memakai konfirmasi kata sandi (D-0033).

## I-0033: Adopsi ulang pembaruan template react-starter

- Tanggal: 2026-11-13
- Status: dikerjakan
- Instruksi: "Saya sudah melakukan pembaruan pada template starter, tolong adopsi kembali semuanya dan terapkan pada proyek kita. kedepannya jangan buat komponen sendiri tapi lihat yang sudah ada, beberapa penambahan komponen yang sebelumnya tidak ada sudah saya tambahkan juga".
- Cara: perubahan template sejak adopsi awal (commit `2af564a`) sampai `7265793` digabung 3 arah per berkas (awal template, proyek, template terbaru). Berkas proyek yang masih sama dengan template awal ditimpa; berkas yang sudah disesuaikan digabung, dan beda yang disengaja dipertahankan. Acuan untuk penggabungan berikutnya: template diadopsi sampai `7265793`.
- Diambil dari template: komponen baru (`Accordion`, `Breadcrumb` yang diubah ke tautan Inertia, `ClearButton`, `DateRangePicker`, `Drawer`, `FileUploader`, `OptionPanel`, `Progress`, `Slider`, `Stepper`, `TagInput`, `TimePicker`) dengan tesnya; `MultiCombobox` buatan template menggantikan buatan saya (yang dulu dibuat sendiri karena template belum punya) dan `Combobox` yang dipecah memakai `OptionPanel`; tema gelap (`lib/theme.ts`, `ThemeMenu`, `ThemedToaster`, `public/theme-init.js` dimuat dari `app.blade.php`, token `on-primary`, nilai gelap memakai rona biru logo); sistem gerak (`motion-*`, termasuk dialog, laci, halaman, dan menu ponsel) dengan tes; penghitung Textarea digambar `Field` di kanan label; paginasi dengan pilihan per halaman sebelum teks jumlah (teks disembunyikan di ponsel); filter tanggal audit logs satu `DateRangePicker`; pelaporan galat dan muat ulang saat potongan lama (`lib/report.ts`, dipasang di `app.tsx`); aturan komentar (tanpa tanda — dan →, blok maksimal 8 baris) di pemeriksa standar; contoh komponen baru di UI Guideline (navigasi, langkah, progres, tema, laci, rentang tanggal, jam, tag, slider, dan unggah berkas dengan unggahan pura-pura); tes kontras dan gerak untuk stylesheet.
- Dipertahankan dari proyek (sengaja berbeda): dialog tampil di atas layar (instruksi pemilik), logo dan nama proyek, `AvatarEditor` dengan unggah nonaktif, lonceng dan reauth versi Inertia, menu dan layout versi Inertia, daftar gerbang `longFunctionAllowed` (lihat D-0038), dan komentar yang sudah dipangkas ke dua baris bila lebih ketat dari template.
- Tahap kedua (pemilik: "saya bilang adopsi semuanya, 3 poin 'Yang tidak diadopsi' boleh dikerjakan"): halaman File Manage (data demo di memori dalam penyimpanan sesi, pemilih berkas memakai unggahan pura-pura, ditaruh di menu Design Sistem, dipecah ke `components/file-manage` agar memenuhi batas ukuran) dengan tes; tes aksesibilitas (`a11y.test.tsx`, axe) dan tes ujung ke ujung Playwright (`e2e/`, `playwright.config.ts`) disesuaikan ke aplikasi Laravel; paket `vitest-axe`, `@playwright/test`, dan `tsx` (D-0039); halaman error statis dibuat sebagai view Blade `resources/views/errors/*.blade.php` lewat `npm run error-pages` (kini Laravel menampilkan halaman bergaya itu untuk 401, 403, 404, 419, 429, 500, dan 503), dengan pratinjau layar penuh `/ui-guideline/errors/{status}`; email kode OTP (`email/login-code.ts` untuk pratinjau, view Blade di `resources/views/emails`) dengan pratinjau di UI Guideline; skrip `rebrand` dan `check-bundle` disesuaikan ke file proyek ini; layar verifikasi dua langkah sebagai komponen `TwoFactorChallengeForm` dengan tes. Pembaruan terbaru template (`06d9442`: DateRangePicker hanya melaporkan rentang lengkap) ikut diambil.
- Menyusul (pemilik: "ini harusnya bisa dikerjakan"): `IdleTimeout` dan `MaintenanceGate` diadopsi untuk Inertia. `IdleTimeout` (hook `useIdleCountdown`) memberi peringatan satu menit sebelum batas diam dan keluar otomatis lewat rute `logout`; batasnya dari prop bersama `session.idle_minutes` (`session.lifetime`, 0 untuk tamu). `MaintenanceGate` mendengar `httpException` 503 dari Inertia (misalnya `php artisan down`) dan menampilkan halaman pemeliharaan menggantikan modal galat Inertia. Keduanya dipasang di `AppLayout` (`MaintenanceGate` juga di `AuthLayout`), dengan tes.
- Belum bisa jalan penuh: halaman verifikasi dua langkah (komponen sudah ada, tetapi halaman, rute, dan backend-nya menunggu MFA, D-0022); email OTP belum dikirim siapa pun; tes ujung ke ujung sudah ditulis tetapi belum dijalankan karena butuh peramban Playwright yang diunduh terpisah (`npx playwright install`) dan database uji.
- Keputusan pemilik sesudahnya (2026-11-13): tes Playwright dijalankan nanti di akhir; konfigurasi nginx dan header keamanan tidak perlu karena urusan server, bukan kode; sisa komponen atau UI template yang belum diadopsi dicatat untuk dikerjakan saat dibutuhkan (daftarnya di `memory/PRD.md`, bagian Utang). Tetap tidak diadopsi: `lib/http`, `form`, `resource`, `mock/`, `auth/` (fetch dan API tiruan, D-0016), dan hook git `pre-commit`.
- Aturan dan tes diperbarui: `.ai/rules/frontend-react.md` (komentar, nama prop nilai, tema gelap, gerak, FileUploader, dialog baca-saja tanpa footer, `MultiCombobox`, aturan "lihat dulu sebelum membuat").

## I-0034: Satu menu Penyimpanan untuk S3 dan SFTP, dengan pengelola berkas di dalamnya

- Tanggal: 2026-11-13
- Status: selesai (tahap 1 dan 2)
- Instruksi: "kita ada menu penyimpanan dan file manage, kita akan pilih satu saja. yang saya bayangkan ada list penyimpanan s3 dan sftp dan masing-masing konfigurasinya terus kalau detailnya di klik masuk ke file manage".
- Pemahaman: satu menu Penyimpanan berisi daftar dua penyimpanan (Amazon S3 dan SFTP), masing-masing dengan konfigurasi (bawaan `.env`, diganti yang diisi di layar) dan uji koneksi; klik detail membuka pengelola berkas untuk penyimpanan itu.
- Tahap 1 (selesai): daftar dua penyimpanan dengan sumber (.env atau Layar), ringkasan, dan hasil uji terakhir; form S3 dan SFTP; uji koneksi; kembali ke bawaan; semuanya Super Admin dengan konfirmasi kata sandi dan tercatat di audit (modul "Penyimpanan", kejadian "Uji koneksi"). Keamanan dan keputusannya di D-0040 (diusulkan). Menu File Manage dilepas; halaman contoh `/file-manage` masih ada di alamatnya dan akan diganti oleh pengelola berkas sungguhan.
- Tahap 2 (selesai, pemilik: "lanjut tahap 2 pakai rekomendasi, 2 koneksi sudah terhubung, saat ini kamu hanya boleh membaca saja konten didalam storage"): tombol "Buka" di tiap baris membuka pengelola berkas yang membaca berkas sungguhan lewat Flysystem: daftar folder dan berkas (cari, urut, paginasi dari server, folder dalam alamat), unduh, folder baru, unggah dengan progres, ubah nama berkas, hapus berkas atau folder kosong, rincian. Aturannya di D-0041 (berlaku). Halaman contoh File Manage di memori dan tesnya dihapus karena digantikan; komponennya dipakai ulang (kolom, menu aksi, dialog, drawer, ikon jenis berkas). Batasan kerja dari pemilik: terhadap penyimpanan sungguhan agent hanya boleh membaca; semua tes tulis memakai disk tiruan di mesin ini, dan pengecekan ke dua koneksi nyata hanya membaca (daftar folder teratas dan satu subfolder; tidak ada yang ditulis, diubah, atau dihapus).

## I-0006: Tombol Atur ulang selalu muncul

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "tanpa filter UI atur ulang selalu muncul, heran sumpah gimana sih proses adopsinya"
- Temuan: di template tombol "Atur ulang" (ghost, kecil, ikon X) hanya muncul bila ada filter atau urutan yang diubah, dan mengembalikan urutan bawaan. Di halaman saya selalu tampil (nonaktif). Diperbaiki sesuai template.

## I-0007: Tabel audit log jangan menumpuk kejadian dalam dua baris

- Tanggal: 2026-11-11
- Status: selesai
- Instruksi: "dalam tabel audit log jangan ditumpuk konten kejadian menjadi 2 baris, pisahkan saja tambah kolom baru ini menjadikan UI nya bengkak"
- Tinjauan dampak: kolom dipisah mengikuti tabel template: Waktu, Host (alamat IP), Pengguna, Modul, Kejadian, Data, Hasil, dan Aksi, masing-masing satu baris. Setiap kolom berisi data punya tombol urutan, jadi server menambah kunci urutan `time`, `host`, `user`, `module`, `event`, `record`, `outcome`. Modul memakai `log_name` yang sudah ada dan terindeks. Alamat IP mendapat indeks (migration baru), dan catatan menyimpan metode, path (tanpa query), dan user agent permintaan seperti di dialog detail template. Filter "Kejadian" diganti "Modul" mengikuti template, dan pencarian mencakup keterangan, pengguna, modul, dan IP. Pengurutan "Pengguna" memakai nama pelaku lewat subquery yang tidak terindeks, dicatat sebagai utang di PRD.
- Belum dikerjakan: tombol "Periksa integritas" dan "Ekspor CSV" di header halaman (butuh rantai hash dan ekspor yang dijaga serta dicatat), lonceng notifikasi dan menu avatar di header aplikasi. Lihat I-0005.


## I-0035: Tampilan kotak dan daftar di pengelola berkas, nama penyimpanan, dan tinjauan dependensi

- Tanggal: 2026-11-13
- Status: selesai
- Instruksi: "mode grid dan list ini dihilangkan untuk tampilan file?", "nama Penyimpanan juga ga bisa diubah ini?", dan "coba tinjau untuk memperkaya file manage ini apakah perlu depedensi baru atau tidak? ... kamu tinjau dulu saja"
- Tinjauan dampak: pilihan tampilan Kotak dan Daftar dikembalikan (bawaan Kotak) karena sebelumnya saya hilangkan tanpa memberi tahu. Nama penyimpanan disimpan di kolom `storage_settings.name` (unik, kosong berarti memakai nama jenisnya); mengubahnya tidak menyentuh nilai koneksi, memakai konfirmasi kata sandi, dan tercatat di audit log. Tinjauan dependensi hanya laporan: tidak ada paket yang dipasang.

## I-0036: Pratinjau berkas di pengelola berkas

- Tanggal: 2026-11-13
- Status: selesai
- Instruksi: "kekurangan file manage sekarang ini tidak bisa preview filenya apakah ada solusi?", lalu "ya mulai, pakai rekomendasi"; pengayaan lain (unggah massal, pindah, salin, ZIP, thumbnail) ditunda oleh pemilik sampai ada fitur yang butuh berkas.
- Tinjauan dampak: lihat D-0042. Tanpa dependensi baru.

## I-0037: Aksi baris tabel menjadi menu titik tiga

- Tanggal: 2026-11-13
- Status: selesai
- Instruksi: "apakah bisa pada semua table untuk aksi dibuat Menu Dropdown seperti itu? ... di kolom aksi hanya ada icon titik-titik saja dan kalau di klik ada Menu Dropdown", lalu "Satu aksi saja" (tabel dengan satu aksi juga memakai menu).
- Tinjauan dampak: komponen bersama `components/ui/row-actions.tsx` (`RowActions`), bentuk menunya seperti contoh dropdown di UI Guideline. Dipakai Kelola Pengguna, Data Jabatan, Data Kantor, Penyimpanan, Audit Logs, contoh CRUD di UI Guideline, dan file manager. Tombol "Buka berkas" di Penyimpanan tetap ikon (aksi utama), keputusan agent dari rekomendasi yang diajukan; pemilik hanya menjawab bagian satu aksi. Aturan tombol di `.ai/rules/frontend-react.md` disesuaikan.
- Penyesuaian (2026-11-13): judul baris di atas menu dihilangkan di Data Kantor, Data Jabatan, Kelola Pengguna, Audit Logs, dan Penyimpanan (nama baris tetap menamai tombol untuk pembaca layar; judul hanya tampil bila `heading` diisi, seperti di file manager dan UI Guideline). Label dipendekkan: Reset (kata sandi pengguna), Detail (audit), dan di Penyimpanan Rename, Konfig, Koneksi, Restore.
- Penyesuaian kedua (2026-11-13): Audit Logs tidak lagi punya kolom aksi, karena klik baris sudah membuka dialog detail. Supaya entri tetap terjangkau papan ketik dan pembaca layar tanpa tombol, baris `DataTable` yang punya `onRowClick` kini bisa difokus (Tab) dan dibuka dengan Enter atau Spasi; ini berlaku juga untuk file manager dan contoh CRUD di UI Guideline.

## I-0038: Menu Pengaturan dengan halaman placeholder

- Tanggal: 2026-11-13
- Status: selesai
- Instruksi: "buat menu baru dengan halaman placeholder dulu namanya 'Pengaturan' simpan dibawah menu penyimpanan, fitur ini untuk mengatur beberapa konfigurasi proyek nanti kita bahasan"
- Tinjauan dampak: menu "Pengaturan" (alamat `/settings`, route `settings`, `pages/settings.tsx`) tepat di bawah Penyimpanan, dengan halaman "Belum tersedia" bertanda TODO. Hanya untuk Super Admin, seperti menu administrasi lain (D-0029); itu keputusan agent karena fiturnya mengatur konfigurasi proyek, dan mudah dibuka bila pemilik ingin lain. Isi pengaturan menunggu pembahasan pemilik; tidak ada model, tabel, atau pengaturan yang dibuat.

## I-0039: Penyimpanan lokal di daftar, satu penyimpanan aktif, dan isi menu Pengaturan

- Tanggal: 2026-11-13
- Status: selesai (disetujui pemilik 2026-11-13: "Ya, ikut rekomendasi"; keputusan di D-0043)
- Instruksi: "untuk penyimpanan ini apakah penyimpanan local dari proyek sendiri bisa tampilkan? tujuan dari menu penyimpanan ini untuk melihat penyimpanan berkas pada proyek, jadi meskipun ada banyak nanti jenis penyimpanannya, yang digunakan untuk menyimpan berkas nanti hanya satu"; "alasan ada menu pengaturan ini juga untuk menentukan parameter proyek seperti penyimpanan, favicon, logo, meta deskripsi dan lain-lain yang bisa dikonfigurasi secara dinamis"
- Tinjauan dampak (usulan, belum dikerjakan):
  - Penyimpanan lokal bisa ditampilkan: Laravel sudah punya disk `local` (`storage/app/private`, tidak bisa diakses dari web) dan `public` (`storage/app/public`). `StorageType` kini hanya S3 dan SFTP dengan satu baris tabel `storage_settings` per jenis; jenis Lokal ditambahkan tanpa konfigurasi koneksi (hanya folder akar yang tetap), dan memakai pengelola berkas yang sama. Berkasnya ditulis ke disk `local` (privat), bukan `public`, karena berkas proyek tidak boleh bisa dibuka lewat alamat.
  - Satu penyimpanan aktif: ditambahkan pilihan "Penyimpanan aktif" (satu dari yang terdaftar dan sudah lolos uji) yang dipakai fitur bisnis untuk menyimpan berkas. Yang tidak aktif tetap bisa dilihat dan dikelola. Bawaannya Lokal sampai pemilik memilih lain, dan penyimpanan aktif tidak bisa dipindah ke yang belum lolos uji.
  - Menu Pengaturan: satu tabel `settings` (kunci, nilai) dengan daftar kunci yang tetap di kode (nama aplikasi, logo, favicon, meta deskripsi, dan penyimpanan aktif), dibaca lewat cache dan dihapus saat diubah. Nilai bawaan tetap dari config atau berkas sekarang, jadi aplikasi jalan tanpa baris di tabel. Logo dan favicon diunggah (jenis gambar, ukuran dibatasi, SVG tidak diizinkan), disimpan di penyimpanan aktif, dan disajikan lewat rute aplikasi karena disk lokal privat. `lib/brand.ts` (nama dan logo yang kini tertulis) dan `app.blade.php` (judul, favicon, meta deskripsi) membaca dari pengaturan.
  - Risiko: logo dan favicon dibaca setiap halaman, jadi perlu cache dan header cache; mengunggah ke penyimpanan aktif berarti menulis, jadi tes memakai disk tiruan dan tidak boleh menulis ke S3 atau SFTP sungguhan (aturan pemilik).
- Pertanyaan untuk pemilik: (1) Lokal yang ditampilkan privat saja, atau disk `public` juga? (2) Pengaturan apa saja yang masuk tahap pertama selain penyimpanan aktif, favicon, logo, dan meta deskripsi? (3) Apakah Lokal boleh menjadi penyimpanan aktif di production, atau hanya untuk pengembangan?

## I-0040: Keputusan atas empat usulan hasil review

- Tanggal: 2026-11-14
- Status: selesai
- Instruksi: "D-0044, kalau memang bisa dikerjakan dari sisi kode boleh dikerjakan; D-0040, ikut rekomendasi perbaikan; D-0037, biarkan saja seeder itu untuk data contoh saja dan anggap ini selesai; D-0036, biarkan saja atau buat lebih longgar jadi 20 karakter agar itu tidak jadi pekerjaan menggantung"
- Tinjauan dampak: D-0044 dikerjakan dengan middleware `SetSecurityHeaders`; D-0040 berlaku dan rentang NAT64 ditolak, dan closure `FilesystemAdapter` di controller penyimpanan diberi tipe; D-0037 berlaku apa adanya; D-0036 diselesaikan dengan melonggarkan batas label menu jadi 20 karakter (pilihan agent di antara dua yang ditawarkan pemilik). Saran reviewer tentang baris pertama yang dibuat bersamaan (`UpdateProjectSettings`, `RenameStorageSetting`, `SaveStorageSetting`) tidak dikerjakan: membuat baris lebih dulu akan menggandakan entri audit, dan dampaknya hanya galat 500 pada dua permintaan yang tepat bersamaan.

## I-0041: Jadikan proyek ini starterkit Laravel 13 dengan SQLite

- Tanggal: 2026-11-14
- Status: selesai
- Instruksi: proyek dijadikan starterkit di atas Laravel 13 dengan SQLite, dengan standar yang sama; semua yang bisa dikerjakan dari sisi kode dikerjakan, dependensi yang perlu dipasang diizinkan, dan hasilnya di-commit lokal tanpa remote.
- Hasil: Laravel 13.42, semua paket dicocokkan lewat `composer update`, database SQLite, tes PHP di SQLite dalam memori. Config framework (`cache`, `database`, `logging`, `services`) dan migrasi `cache` dan `jobs` memakai versi Laravel 13, ditambah pengaturan proyek (`app`, `session`, `auth`, `filesystems`); `bootstrap/app.php` mempertahankan aturan JSON dari Laravel 13; skrip `composer` dan `package.json` memakai `artisan dev` dan `@laravel/multiplex`. Tes yang disesuaikan: `DatabaseIntegrityTest` membaca skema lewat Schema builder, dan `LoginTest` membaca galat sesi sebagai array (Laravel 13 menyimpan `ViewErrorBag` sebagai array).

## I-0042: Merek STARTERKIT, env standar, dan README

- Tanggal: 2026-11-14
- Status: selesai
- Instruksi: "sesuaikan brandingnya jadi STARTERKIT, cek env dan example-nya agar standar dan mudah disesuaikan, sesuaikan readme, dan jangan bawa-bawa proyek sebelumnya lagi; pastikan proyek siap digunakan dengan standar yang sama."
- Hasil: merek dan warna lewat `npm run rebrand` (D-0046); logo dan favicon netral baru, aset lama dibuang; semua nama dan istilah bisnis proyek sebelumnya dihapus dari kode, tes, dokumen, README, dan PRD; `.env.example` ditata per bagian dengan komentar dan memuat variabel yang dibaca config; `README.md` ditulis ulang untuk starterkit.

## I-0043: Terapkan perbaikan instruksi agent dari SIPEBRI ke starterkit

- Tanggal: 2026-11-15
- Status: selesai
- Instruksi: "sebelum lanjut saya mau memperbaiku dulu proyek starterkit karena proyek sipebri ini hasil clone dari sana ... saya ingin menerapkannya juga pada proyek starternya"
- Hasil: dibandingkan kedua proyek sejak fork (2026-11-14). Hanya dua perubahan instruksi agent yang umum, dan keduanya dibawa: panduan `.ai/guidelines/feature-workflow.md` (alur kerja fitur baru; ikut tertulis di `CLAUDE.md` dan `AGENTS.md`) dan aturan "dialog sampai 6 isian, halaman untuk lebih dari itu" di `.ai/rules/frontend-react.md` (tanpa rujukan ke I-0015 milik SIPEBRI). Tidak dibawa karena khusus SIPEBRI: warna biru logo, database MySQL pada aturan e2e, PRD, dan catatan instruksi. Hook dan agent di `.claude` sudah identik di kedua proyek.
