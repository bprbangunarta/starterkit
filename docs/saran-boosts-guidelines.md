# Saran penyempurnaan boosts-guidelines (dari pengalaman proyek SIPEBRI)

Catatan ini disusun dari satu sesi kerja yang panjang (2026-11-11) memakai `boosts-guidelines-1.1.0` bersama gerbang Boost (`.claude/hooks/boost-gate.json`), subagent `boost-reviewer`, dan rule di `.ai/rules`. Isinya usulan untuk versi berikutnya, disertai bukti dari kejadian nyata. Bukan keputusan proyek; keputusan proyek ada di `docs/decisions.md`.

## Status terhadap boosts-guidelines 1.2.0 (dicek 2026-11-12)

Catatan ini ditulis terhadap 1.1.0. Versi 1.2.0 sudah menjawab sebagian besar bagian alur review dan gerbang. Rule di `template/.ai/rules` **tidak berubah sama sekali** dari 1.1.0 (selisih nol baris pada `auth`, `audit`, `tests`, `api`, `security`, `config`, `migrations`).

| Usulan | Status di 1.2.0 |
| --- | --- |
| A1, A2 Review hanya saat diminta | **Tercakup.** `review.mode` (`on_request` bawaan) dan perintah `/review-fitur`. Beda: tidak ada status `sesuai`; pemicunya perintah dari pemilik, dan boleh satu review ulang untuk berkas yang diperbaiki (usulan kita: tanpa review ulang). |
| A3 Gerbang membaca vonis reviewer | **Tercakup.** Laporan dicocokkan lewat `agentId`, baris `VERDICT:` dibaca, gerbang menunggu laporan masuk. |
| A4 Jenis temuan | **Koreksi:** tiga jenis (BLOKIR, KEPUTUSAN USER, SARAN) dan aturan "KEPUTUSAN USER tidak membuat FAIL" sudah ada sejak 1.1.0. FAIL di proyek ini muncul karena penyimpangan belum dicatat `diusulkan` saat review (itu BLOKIR yang sah). 1.2.0 menambahkan di `/review-fitur`: agent mencatatnya `diusulkan` lebih dulu. Usulan tersisa: pengingat itu juga masuk ke prompt reviewer. |
| A5 Biaya reviewer | **Sebagian.** Hasil generate Wayfinder masuk `exempt`, reviewer menerima base commit (diff), angka biaya didokumentasikan. Belum: model lebih murah untuk reviewer, batas jumlah reviewer. |
| A6 Tingkat review per area | **Belum.** `review.globs` masih memuat `resources/js/**` dan `resources/views/**` sehingga `/review-fitur` ikut meninjau UI. |
| B1 `max_blocks` | **Tercakup.** Bawaan 1. |
| B2 Pesan penahanan | **Tercakup.** Daftar diringkas, keluaran test dipotong 1500 karakter, ANSI dibuang. |
| B3 Heredoc dibaca sebagai berkas | **Tercakup.** Perbaikan di `shell.php`. |
| B4 Prasyarat bertahan sepanjang sesi | Belum. |
| B5 Ringkasan rule satu halaman | Belum. |
| B6 Batas memori phpstan di skrip Composer | Belum. |
| B7 Konfigurasi lokal untuk pengecualian pribadi | Belum (pengecualian pribadi tetap lewat `boost-gate.json`). |
| C, D (rule) | **Belum satu pun.** Termasuk kebijakan kata sandi, `password.confirm`, akses bertahap, batas bisnis, test integritas foreign key, test di mesin production, `.env.example` kosong, ekspor besar. |
| F (format: inti dan profil, ID rule) | Belum. |

Memperbarui proyek yang sudah adopsi ke 1.2.0: salin `template/.claude/hooks/*.php`, `template/.claude/commands/review-fitur.md`, dan `template/.claude/agents/boost-reviewer.md`; tambahkan `"mode": "on_request"` di bagian `review` pada `boost-gate.json`; turunkan `quality.max_blocks` ke 1; tambahkan `resources/js/actions/**`, `resources/js/routes/**`, dan `resources/js/wayfinder/**` ke `exempt`; lalu `php artisan boost:update`. Hook proyek ini identik dengan 1.1.0, jadi penyalinan tidak menimpa perubahan lokal.

## Ringkasan: tiga masalah terbesar

1. **Review terjadi terlalu sering dan terlalu mahal.** Gerbang menuntut review setiap kali ada berkas berubah, bahkan untuk perubahan tampilan kecil. Satu fitur memicu belasan review dan puluhan penahanan.
2. **Gerbang tidak bisa membaca vonis reviewer.** Hasil panggilan agent hanya "berjalan di latar belakang"; laporan PASS atau FAIL datang belakangan sebagai pesan terpisah. Gerbang selalu menganggapnya "tanpa VERDICT".
3. **Beberapa rule bertabrakan dengan cara kerja bertahap** (fitur dasar dulu, izin dan MFA belakangan), sehingga setiap fitur menghasilkan penyimpangan yang harus dicatat dan reviewer menandainya FAIL.

## A. Alur review yang diusulkan

### A1. Siklus status satu fitur

Satu fitur melewati status berikut, dan review hanya terjadi di satu titik:

| Status | Artinya | Review? |
| --- | --- | --- |
| `dikerjakan` | Fitur dibangun, termasuk revisi tampilan dan penyesuaian dari pemilik | Tidak. Hanya test dan pemeriksaan otomatis. |
| `sesuai` | Pemilik menyatakan fitur sudah sesuai (revisi selesai) | Titik pemicu review. |
| `ditinjau` | Review dijalankan satu kali, temuan langsung diperbaiki | Satu kali, tidak diulang. |
| `selesai` | Temuan diperbaiki, test lolos, hasilnya final | Tidak ada review ulang. |

Alasan: di proyek ini fitur hampir selalu direvisi beberapa kali setelah kode pertama (tata letak, istilah, warna, aturan). Setiap revisi memicu review baru, padahal kode berubah lagi setelahnya. Review di tengah jalan menilai kode yang akan berubah.

### A2. Cara gerbang mengetahui "fitur sesuai"

- Satu penanda eksplisit: berkas `.ai/state/review-requested` (isi: nama fitur dan daftar commit), dibuat saat pemilik atau agent menyatakan `sesuai`.
- Tanpa penanda, gerbang **tidak** menuntut review. Ia hanya menuntut format, analisis statis, dan test.
- Dengan penanda, gerbang menuntut tepat satu laporan review, lalu menghapus penanda saat laporan ada dan temuannya ditandai diperbaiki.

### A3. Vonis reviewer harus bisa dibaca gerbang

Masalah nyata: reviewer berjalan sebagai agent latar belakang. Gerbang membaca hasil panggilan ("agent diluncurkan") dan menyimpulkan "tanpa VERDICT", walau laporan PASS sudah masuk sebagai pesan terpisah. Akibatnya gerbang menahan tiga kali untuk review yang sebenarnya sudah selesai.

Usul:
- Reviewer **menulis laporan ke berkas** (`.ai/reviews/<fitur>.md`) yang berakhir dengan baris `VERDICT: PASS|FAIL`, dan gerbang membaca berkas itu, bukan hasil panggilan agent.
- Atau reviewer dijalankan sinkron (di latar depan) dan hasilnya dikembalikan utuh ke pemanggil.
- Sebutkan keduanya di guideline, supaya agent tidak menebak.

### A4. Aturan vonis: keputusan yang tertunda bukan kegagalan kode (sebagian sudah ada sejak 1.1.0, lihat status di atas)

Kejadian nyata: reviewer memberi `VERDICT: FAIL` karena `password.confirm` belum dipasang (D-0030) dan karena ekspor audit belum punya izin tersendiri (D-0026). Keduanya sudah dicatat sebagai keputusan yang menunggu pemilik. Agent tidak boleh memutuskannya sendiri, jadi FAIL itu tidak bisa diperbaiki oleh agent.

Usul tiga jenis temuan dengan akibat berbeda:
- **BLOKIR**: pelanggaran rule tanpa keputusan tercatat. Membuat FAIL, diperbaiki agent.
- **KEPUTUSAN PEMILIK**: pelanggaran yang sudah dicatat `diusulkan` dan menunggu pemilik. Tidak membuat FAIL; dilaporkan ke pemilik. Bila belum dicatat sama sekali, agent mencatatnya `diusulkan` dulu.
- **SARAN**: tidak membuat FAIL.

### A5. Biaya reviewer

Satu fitur sedang di proyek ini menghabiskan 40 sampai 90 ribu token per reviewer, dan belasan reviewer dijalankan. Usul:
- Reviewer menerima **diff dan rule yang cocok dengan glob**, bukan membaca ulang seluruh berkas.
- Daftar berkas review **mengecualikan berkas hasil generate** (`resources/js/actions`, `resources/js/routes`, `resources/js/wayfinder`). Di proyek ini daftar yang dikirim gerbang berisi banyak berkas Wayfinder.
- Batas jumlah reviewer per fitur (misalnya satu), dan model yang lebih murah untuk reviewer.
- Gerbang tidak mengirim ulang daftar berkas yang panjang di setiap penahanan; cukup jumlah dan jalur ke berkas daftarnya.

### A6. Tingkat review per area

Konfigurasi `review.globs` hari ini hanya daftar jalur. Usul tingkat per area:

| Area | Tingkat |
| --- | --- |
| `app/**`, `routes/**`, `config/**`, `database/**` | Review penuh saat `sesuai` |
| `resources/js/**`, `resources/views/**` | Hanya test dan pemeriksaan standar (pengecualian pemilik di proyek ini) |
| `docs/**`, komentar, hasil generate | Tidak ada |

Bila perubahan UI membuat test lama tidak cocok, test tetap wajib lolos; itu satu-satunya syaratnya.

## B. Gerbang (hook): perbaikan teknis

1. **`max_blocks`.** Setiap penahanan memakan satu giliran penuh. Default 3 terlalu banyak. Usul: 1 (tahan sekali dengan pesan singkat, lalu lepas dan laporkan).
2. **Pesan penahanan terlalu panjang.** Berisi daftar puluhan berkas yang sama setiap kali, dan keluaran test berwarna (ANSI, ratusan titik). Usul: pesan satu paragraf, hanya nama test yang gagal, tanpa kode warna (`--no-ansi`).
3. **Pembaca berkas mengenali token sampah.** Pada perintah Bash dengan heredoc yang menulis banyak berkas, gerbang menolak dan menyebut "berkas" seperti `$roles`, `*/`, `$before`, dan potongan teks tampilan (`Notifikasi, Tidak, )`). Ia salah membaca isi heredoc sebagai jalur berkas. Usul: kenali hanya jalur dari tujuan redirect yang jelas atau dari alat Write/Edit, dan wajibkan alat Write/Edit untuk berkas aplikasi.
4. **Penolakan membuang kerja.** Satu penolakan membatalkan seluruh perintah (misalnya sepuluh berkas sekaligus), sehingga isi besar harus dikirim ulang. Usul: gerbang melaporkan semua prasyarat yang kurang sekali jalan dan prasyarat bertahan selama sesi.
5. **Prasyarat dihitung ulang setelah konteks diringkas.** Seluruh rule dan skill harus dibaca ulang, puluhan ribu karakter. Usul: ringkasan rule satu halaman (`.ai/rules/digest.md`) cukup untuk memenuhi prasyarat, dengan rule lengkap hanya dibaca bila perlu.
6. **`phpstan` kehabisan memori** pada batas bawaan 128 MB saat dijalankan lewat perintah biasa; gerbang memakai `--memory-limit=1G` sehingga lolos. Usul: pasang batas yang sama di skrip Composer proyek supaya perintah manual dan gerbang sama.
7. **Gerbang tidak boleh diubah oleh agent** (ditolak sistem sebagai self-modification). Usul: dokumentasikan bahwa perubahan konfigurasi gerbang selalu dikerjakan pemilik, dan sediakan berkas konfigurasi pengguna terpisah (`boost-gate.local.json`, tidak dikunci) untuk pengecualian pribadi seperti "UI tanpa review".

## C. Rule yang bertabrakan dengan kenyataan

| Rule | Masalah di proyek ini | Usul |
| --- | --- | --- |
| `auth.md`: kata sandi minimal 15 karakter bila satu-satunya faktor | Pengguna belum terbiasa; pemilik memilih 8 (D-0031) dan MFA ditunda, jadi rule dilanggar | Sediakan "profil kebijakan kata sandi" yang bisa dipilih proyek (15 tanpa MFA, 8 dengan MFA, atau kustom dengan catatan keputusan) dan batas jelas untuk nilai terendah |
| `auth.md`: aksi sensitif memakai `password.confirm` | Butuh dialog konfirmasi ulang yang tidak ada di template starter; tiap fitur menghasilkan penyimpangan (D-0025, D-0030) | Sertakan komponen dan contoh alurnya di starter, atau definisikan versi minimal yang diterima |
| `audit.md`: membaca dan mengekspor log audit dijaga izin tersendiri | Bertabrakan dengan pendekatan bertahap "Super Admin dulu, izin rinci belakangan" (D-0024, D-0026, D-0029) | Tambahkan klausa "akses bertahap": pembatasan peran saja sah sampai tahap izin, dengan catatan keputusan |
| `audit.md`: model baru wajib `Audited` | Model catatan kerja (`AuditExport`) tidak layak dicatat perubahannya | Dokumentasikan pengecualian "catatan kerja" dan cara menandainya di arch test |
| `api.md`: ekspor besar diunduh lewat signed URL | Proyek memakai rute berautentikasi yang terikat pemesan, berlaku 24 jam | Terima keduanya, sebutkan syarat minimal (terikat pemesan, kedaluwarsa, tidak bisa ditebak) |
| `security.md`: config wajib dicek saat boot | Cek saat boot menggagalkan `composer install` dan perintah artisan di mesin tanpa `.env` | Tulis polanya: cek malas saat dipakai, atau hanya bila tidak berjalan dari konsol |
| `.env.example`: hanya contoh nilai aman | Kunci seperti kata sandi bawaan tidak punya contoh aman | Izinkan placeholder kosong dengan komentar |
| `tests.md`: test di mesin yang sama dengan production | Preset skeleton memakai SQLite dalam memori dan menyembunyikan dua bug nyata (ID ULID di kolom `subject_id` bertipe angka, keunikan yang bergantung collation) | Jadikan langkah penyiapan wajib: `phpunit.xml` memaksa mesin production dengan database `*_test` terpisah; README menyebutkan |

## D. Rule yang perlu ditambah

1. **Batas urusan bisnis.** Agent tidak boleh mengarang aturan bisnis (peran, kolom data, alur persetujuan) dan menulisnya ke PRD atau keputusan. Kejadian nyata: agent menulis daftar peran kredit dan kolom debitur sebagai "rekomendasi", lalu ditarik kembali atas teguran pemilik. Untuk bisnis: tanya dan tunggu; untuk teknis: ikuti rekomendasi.
2. **Pengelolaan kata sandi.** Belum ada rule untuk: kata sandi bawaan dari `.env`, wajib ganti saat masuk pertama, kedaluwarsa N hari, riwayat N terakhir, dan reset oleh admin (sesi pengguna diputus). Semuanya ditambahkan di proyek ini (D-0031, D-0032) tanpa acuan.
3. **Integritas relasi database.** Rule hanya berkata "gunakan foreign key". Tambahkan standar test otomatis yang gagal bila ada kolom `*_id` tanpa foreign key, dengan daftar pengecualian beralasan (kolom polimorfik, sesi, snapshot). Proyek ini punya contohnya di `tests/Feature/DatabaseIntegrityTest.php`.
4. **Penghapusan data yang masih terhubung.** Penjaga harus ada di database (RESTRICT), bukan hanya di kode, dan pesan ramahnya di aplikasi. Kasus nyata: peranan yang dipegang pengguna; relasi bawaan paket (`model_has_roles`) bertipe CASCADE.
5. **Test tidak boleh bergantung pada data acak factory untuk teks yang diperiksa.** Satu test pencarian sesekali gagal karena nama acak admin ikut cocok. Aturan: nilai yang dipakai sebagai kata kunci atau urutan diisi eksplisit.
6. **Aksi tulis administratif**: batas laju per aksi (60 per menit), alasan wajib untuk perubahan peran, kunci baris untuk invarian "minimal satu Super Admin", dan transaksi. Semua ini baru ditemukan reviewer setelah kode ditulis; sebagai rule mereka bisa ditulis benar sejak awal.
7. **Antarmuka**: judul kartu dan modal diawali huruf kapital di setiap kata; pilihan ganda berjumlah banyak memakai combobox dengan pencarian (bukan kotak centang); tombol aksi utama halaman (Tambah, Export) memakai warna primary; tombol yang mengambil data harus menyebut cakupannya (misalnya "semua baris sesuai filter").
8. **Ekspor besar**: berjalan di antrean, berkas privat terikat pemesan, kedaluwarsa, batas jumlah ekspor aktif per pengguna dan global, nama berkas yang tidak bisa ditebak, dan pemberitahuan lewat lonceng. Rule `api.md` memuat sebagian; padukan dengan batas yang ditemukan reviewer.
9. **Mesin antrean di production**: tulis kebutuhan worker, `--timeout` yang lebih kecil dari `retry_after`, dan supervisor sebagai bagian penyiapan production.

## E. Alur kerja yang berhasil dan layak dibakukan

- **Pencatatan instruksi** di `docs/instructions.md` dengan tinjauan dampak sebelum dikerjakan, status dari `dikerjakan` sampai `selesai`. Menolong saat konteks diringkas.
- **Keputusan dengan status dan pemutus**: `diusulkan` (menunggu pemilik) lalu `berlaku` setelah pemilik menyetujui di percakapan. Kutip kalimat persetujuan pemilik.
- **UI Guideline sebagai acuan hidup** dengan test yang gagal bila komponen baru tidak punya contoh.
- **Pemeriksa standar frontend** (`npm run standards`) yang menangkap fungsi di atas 80 baris, `items-start`, dan sejenisnya sebelum review.
- **Test sebagai penangkap bug yang nyata**: pindah ke MySQL menemukan bug yang SQLite tidak lihat.
- **Larangan menulis nilai rahasia ke repositori** yang ditegakkan sistem (kata sandi bawaan nyaris masuk dokumen dan test; perintahnya diblokir).

## F. Usulan format versi berikutnya

1. Pisahkan guideline menjadi **inti** (wajib) dan **profil** (kebijakan kata sandi, akses bertahap, review per fitur), dengan pilihan yang dicatat proyek di `docs/decisions.md`.
2. Sediakan **berkas konfigurasi lokal** untuk pengecualian pribadi pemilik, yang tidak mengubah guideline bersama.
3. Beri tiap rule **ID stabil** supaya reviewer dan keputusan bisa merujuknya (contoh: `AUTH-03`), dan setiap penyimpangan menyebut ID itu.
4. Sertakan **anggaran biaya** dalam panduan: berapa reviewer per fitur, berapa penahanan gerbang, kapan memulai sesi baru (satu sesi per fitur menjaga konteks tetap kecil).
