---
paths:
  - 'resources/js/**'
  - 'resources/css/**'
---

# Standar kode dan tampilan frontend (React, Inertia, Compact UI)

Aturan wajib untuk semua kode di `resources/js` dan `resources/css`. Diadopsi dari template react-starter dan disesuaikan untuk Inertia.
Melengkapi `frontend.md` dan skill `inertia-react-development`; bila bertentangan, aturan yang lebih ketat berlaku. Aturan bertanda **[mesin]**
diperiksa `npm run standards` (bagian dari `npm run check`, yang juga dijalankan Stop gate); sisanya diperiksa saat review. `npm run check`
menjalankan `tsc --noEmit` (`strict`, `noUnusedLocals`, `noUnusedParameters`), **ESLint** (hooks, promise, aksesibilitas; `eslint.config.js`),
Prettier, pemeriksa aturan rumah, dan Vitest.

## Isi

1. [Kode](#kode)
2. [React](#react)
3. [Tampilan (Compact UI)](#tampilan)
4. [Token dan CSS](#token)
5. [Data, formulir, dan keamanan](#data)
6. [Audit log, autentikasi, dan MFA](#audit-mfa)
7. [Tes](#tes)
8. [Sebelum selesai](#selesai)

<a id="kode"></a>
## 1. Kode

- **Kode berbahasa Inggris:** nama berkas, komponen, fungsi, variabel, tipe, kolom data, URL/endpoint, komentar, pesan commit.
- **Hanya teks layar berbahasa Indonesia:** label, judul, galat, toast, isi email. Pesan validasi backend sudah Indonesia; jangan ditulis ulang.
- URL dan endpoint berbahasa Inggris tetapi **sepadan dengan judul layar** (layar "Audit Logs" → `/audit-logs`).
- `strict` aktif. **Tanpa `any`, `as any`, `@ts-ignore`, `@ts-nocheck`** **[mesin]**; pakai `unknown` lalu persempit. Tipe respons API didefinisikan dan dipakai ulang.
- Ekspor bawaan hanya untuk halaman, layout, dan `App` **[mesin]**; lainnya ekspor bernama. Tanpa `console.log`/`debugger` **[mesin]** (`console.error` boleh).
- **Fungsi paling banyak 80 baris** **[mesin]**, kecuali tiga komponen template di D-0016; komponen yang memanjang dipecah per bagian.
- **Berkas paling banyak 400 baris** **[mesin]**; berkas yang memanjang dipecah per bagian (contoh: `components/guideline` memecah `/ui-guideline`).
- Halaman di `resources/js/pages`, komponen bersama di `resources/js/components`, komponen dasar di `resources/js/components/ui`, logika bersama di `resources/js/lib`.
  Cek komponen yang ada sebelum membuat yang baru.
- **Semua akses server lewat Inertia:** navigasi dengan `Link` dan `router`, formulir dengan `<Form>` atau `useForm`, data bacaan lewat props halaman
  (`deferred props`, `WhenVisible`, `usePoll` untuk data yang dimuat belakangan). `fetch` langsung dilarang **[mesin]** (kecuali ping koneksi di
  `hooks/use-network-status.ts`). Panggilan JSON mandiri memakai `useHttp`.
- **URL dibuat dari fungsi Wayfinder** (`@/routes`, `@/actions`), tidak pernah ditulis sebagai string **[mesin]** (D-0014). Hasil generate di
  `resources/js/actions`, `resources/js/routes`, dan `resources/js/wayfinder` tidak di-commit dan tidak diedit.

- **Komentar:** tulis seperti orang yang menjelaskan ke rekan, bukan dokumentasi: jelaskan **mengapa** (jebakan, keputusan, batasan), bukan apa yang sudah terbaca dari kodenya. Satu atau dua baris;
  JSDoc hanya untuk komponen atau ekspor yang perilakunya tidak terlihat dari namanya, dan untuk prop yang butuh satuan atau format. Jangan mengulang tipe, jangan memakai tanda — atau → **[mesin]**,
  dan blok komentar tidak lebih dari 8 baris berturut-turut **[mesin]**.
- **Nama prop nilai:** komponen yang meneruskan prop `<input>` bawaan memakai `onValueChange(nilai)` karena `onChange` sudah milik event native (`MaskedInput`, `FilteredInput`, `CurrencyInput`, `CodeInput`,
  `ColorInput`); komponen berbasis popover atau daftar memakai `onChange(nilai)` (`Combobox`, `MultiCombobox`, `DatePicker`, `TimePicker`, `TagInput`). `Slider` dan `FileInput` memakai `onValueChange` karena nilainya berupa daftar.

<a id="react"></a>
## 2. React

- **Komponen fungsi dan hooks saja** (kecuali `ErrorBoundary`, yang memang harus kelas). Hooks hanya dipanggil di tingkat atas komponen
  atau hook (`use...`), tidak di dalam kondisi, perulangan, atau fungsi bersarang.
- **Satu komponen = satu berkas yang bertanggung jawab pada satu hal.** Jangan mendefinisikan komponen di dalam komponen lain
  (state-nya hilang tiap render); angkat ke tingkat berkas.
- **State seminimal mungkin:** nilai yang bisa dihitung dari props/state lain dihitung saat render, bukan disalin ke state atau `useEffect`.
  Jangan mengubah (`mutate`) state atau props; buat salinan baru (`[...list, item]`, `{ ...obj, a }`).
- **`useEffect` hanya untuk sinkronisasi dengan dunia luar** (event jendela, timer, pengamat). Bersihkan di fungsi balikan (hapus listener,
  hentikan timer, `AbortController`). Jangan memuat data di efek: pakai props halaman Inertia. Jangan menaruh logika yang cukup di event handler ke efek.
  Daftar dependensi harus lengkap; jangan dimatikan demi menghilangkan peringatan.
- **`key` pada daftar memakai id yang stabil**, bukan indeks **[mesin]** (indeks hanya untuk baris skeleton statis di `DataTable`).
- **Promise di event handler** tidak dibiarkan menggantung: `onClick={() => void aksi()}` atau tangani galatnya dengan `try/catch` dan toast.
- **Isian terkendali:** `value` + `onChange` selalu berpasangan; jangan berpindah antara tak terkendali dan terkendali.
- **Aksesibilitas:** `Combobox` tanpa `Field` wajib punya `aria-label` (atau placeholder sebagai cadangan): namanya tidak diambil dari teksnya; tombol ikon wajib `aria-label` (plus `Tip`); isian dibungkus `Field` (label terhubung); galat ditandai `aria-invalid`;
  dialog memakai `Modal`/`ConfirmDialog` (fokus dan Esc ditangani Radix); semua yang bisa diklik dapat dijangkau keyboard (pakai `button`
  atau `Link`, bukan `div` ber-`onClick`; satu-satunya pengecualian adalah lapisan latar penutup laci/dialog).
- **Galat render** ditangkap `components/error-boundary.tsx` (dipasang di `main.tsx`); jangan menelan galat dengan `catch {}` kosong.
- **Teks pengguna** ditampilkan sebagai teks; `dangerouslySetInnerHTML` dilarang **[mesin]**.
- Gunakan `React.memo`/`useMemo`/`useCallback` hanya bila ada masalah kinerja yang terukur, bukan sebagai kebiasaan.
- **ESLint menegakkan** (`eslint.config.js`): `rules-of-hooks` dan `exhaustive-deps` (error), `no-floating-promises` (pakai `void` atau `await`),
  `no-misused-promises`, `consistent-type-imports`, `no-explicit-any`, serta aturan `jsx-a11y` (label, peran ARIA, penanganan keyboard).
  Dua aturan sengaja dimatikan di konfigurasi, dengan alasannya tertulis di sana: `set-state-in-effect` (pemuatan saat mount lewat
  ping koneksi adalah pola resmi) dan `no-autofocus` (login dan dialog memang memfokuskan isian pertama).
- Mematikan aturan ESLint per baris (`eslint-disable-next-line`) hanya dengan komentar alasan di atasnya.

<a id="tampilan"></a>
## 3. Tampilan (Compact UI)

Semua UI baru mengikuti pola yang ada; jangan membuat gaya baru per halaman. Acuan hidupnya adalah halaman `/ui-guideline`
(sidebar → UI Guideline): tampilan baru harus terlihat sama. Perubahan pola memperbaiki komponen bersama dan diterapkan ke semua halaman.

- **Lihat dulu sebelum membuat:** sebelum menulis komponen atau layar, cari padanannya di `resources/js/components` dan di template react-starter; yang sudah ada di sana diadopsi, tidak ditulis ulang. Pembaruan template diterapkan dengan menggabungkan perubahannya (bukan menimpa), dan beda yang disengaja dicatat di `docs/decisions.md` atau `docs/instructions.md`.
- **Pakai ulang** `resources/js/components/ui` (Button, Input, Field, Combobox, MultiCombobox, DatePicker, Modal/ConfirmDialog, Dropdown, Tabs, Tip, Card, Alert, Badge,
  EmptyState, ErrorState, Skeleton, PageHeader, DataTable, BarChart, ColumnChart, DonutChart, dan isian khusus). Ubah komponen dengan memperbaiki komponen
  bersama di `components/ui`, bukan dengan gaya satu kali di halaman. Jangan pakai kontrol native atau berukuran lain.
- **Layout:** sidebar `w-60` (laci ponsel `w-64`), header `h-12`, konten `p-3 sm:p-5`, jarak antar seksi `gap-3`. `PageHeader`: judul
  `text-base font-semibold`, keterangan `text-xs text-muted`, aksi di kanan; susunannya sudah disepakati, jangan diubah tanpa diminta.
- **Baris teks + aksi** (header halaman/blok/dialog, baris pengaturan): `flex flex-wrap items-center justify-between gap-2`, teks
  `min-w-0`. `items-start justify-between` hanya untuk opsi `Combobox` **[mesin]**.
- **Header halaman:** keterangan pendek, tidak mengulang data yang sudah tampil di isi halaman. Tombol di `actions` `PageHeader` berlebar sama: di ponsel mengisi lebar penuh dalam dua kolom sama lebar seperti filter
  (tombol terakhir yang ganjil memenuhi dua kolom), dari `sm` ke atas di kanan dengan lebar sepanjang label terpanjang. Isi `actions` dengan tombolnya langsung (fragment),
  **tanpa `div` pembungkus** **[mesin]**. Semua varian `Button` punya kotak border 1px yang sama (transparan bila tak terlihat), jadi dua tombol bersebelahan sama besar sampai ke piksel.
- **Daftar:** selalu punya keadaan memuat (Skeleton), kosong, dan galat; setiap aksi memberi toast (sonner) sukses/gagal.
- **Tombol** (ikon atau tidak ditentukan oleh **tempatnya**, bukan selera):

  | Tempat | Bentuk | Contoh |
  |---|---|---|
  | header halaman, toolbar, header kartu, dan baris pengaturan di body kartu | **ikon + label** (ikon kiri) | Tambah, Ekspor CSV, Atur ulang, Atur/Ganti (MFA), Kirim kode |
  | footer dialog dan footer kartu (`DialogFooter`, `CardFooter`) | **teks saja** **[mesin]** | Batal, Simpan, Hapus, Konfirmasi, Matikan |
  | tombol kirim pada kartu auth tanpa header (login, verifikasi) | **teks saja**, selebar kartu | Masuk, Verifikasi |
  | aksi baris tabel | **satu tombol titik tiga** (`RowActions`) yang membuka menu: judul baris, aksi berikon, Hapus merah di bawah pemisah; berlaku juga untuk satu aksi. Aksi utama yang sering dipakai boleh tetap tombol ikon di sampingnya (Buka di Penyimpanan) | Ubah, Hapus, Uji koneksi |
| tombol tutup, paginasi panah | **ikon saja** (`size="icon"`) + `aria-label` + `Tip` | Tutup |
  | tombol di samping isian (buat acak, salin) | **ikon saja**, `size="fieldIcon"` (setinggi isian, `h-8`) di dalam `InputGroup` | Buat kata sandi acak, Salin |
  | tautan di keadaan kosong/galat, nomor halaman | teks saja | Coba lagi |

  Urutan footer: batal paling kiri, konfirmasi paling kanan. Satu tempat tidak boleh bercampur: bila satu footer atau satu toolbar sudah memakai ikon,
  semua tombolnya memakai ikon (kecuali tabel di atas).
- **Dialog** punya tiga bagian, seperti kartu: **header** (judul; subjudul satu baris hanya untuk dialog form), **body** (`DialogBody`: isi formulir, atau **pesan konfirmasi**),
  dan **footer** (`DialogFooter`, tombol teks saja). Pesan di `ConfirmDialog` ada di body, bukan di header; jangan menaruh teks panjang di `description` `Modal`. Dialog baca-saja (detail) tidak punya footer: tombol X di header sudah menutupnya, jadi tombol "Tutup" tidak diulang.
  Dialog yang memakai `Modal` wajib memakai `DialogBody` **[mesin]**. Tambah/ubah data: modal kecil (`max-w-sm`) atau form satu halaman; `ConfirmDialog` sebelum menghapus.
- **Judul kartu dan modal memakai huruf kapital di setiap kata** ("Tambah Pengguna", "Kata Sandi", "Hapus Peranan?"); kata sambung `dan`, `di`, `ke`, `dari`, `untuk`, `yang`, `dengan` tetap huruf kecil.
- **Kartu (header, body, footer)** disusun seperti dialog: `CardHeader` (judul `text-sm font-semibold`, keterangan opsional, aksi/badge di kanan,
  garis bawah), `CardBody` (isi, `p-3`), dan `CardFooter` (garis atas) **hanya bila kartu punya tombol** kirim/batal. Kartu tanpa tombol tidak punya footer.
  Tombol di footer: batal paling kiri, konfirmasi paling kanan, **teks saja**; hint boleh di kiri. Jangan menaruh tombol Simpan di dalam body
  atau membuat judul kartu dengan `<h2>` buatan sendiri. Contoh: kartu Kata sandi di Profil dan bagian "Kartu" di `/ui-guideline`.
- **Form:** grid 2–4 kolom dalam `Card` per seksi (judul `text-sm font-semibold`), `Field` dengan label `text-xs`, tanda `*` merah untuk wajib.
- **Tanpa keterangan di bawah isian:** di bawah isian hanya galat validasi yang tampil. Keterangan isian diberikan sebagai `hint` `Field`: tampil sebagai tooltip pada
  ikon info di samping label dan dibaca pembaca layar sebagai deskripsi isian. Hint pendek: untuk satu hal yang tidak terlihat dari label (format, batas, akibat
  pilihan); yang lebih panjang masuk ke deskripsi kartu atau halaman. `Textarea` tidak punya hint sendiri dan tidak ada apa pun di bawahnya: penghitung huruf (`0/200`) digambar `Field` di kanan label, supaya jarak ke komponen di bawahnya tetap sama dengan isian lain; baris sakelar dan
  centang memegang baris keterangannya sendiri karena itu bagian dari pilihannya. Isian dengan tombol di sampingnya memakai `InputGroup` di dalam `Field`.
- **Jenis isian** (semua ada contohnya di `/ui-guideline`, bagian Isian):
  - **Uang dan angka besar:** `CurrencyInput` (teks, hanya angka, format `1.000.000` otomatis, awalan `Rp`; `prefix={null}` untuk angka biasa).
    Nilainya **number** (`1000000`); ke API dikirim angka itu, tidak pernah teks berformat.
  - **Format tetap** (telepon, NIK, NPWP, kode): `MaskedInput` dengan `mask` dari `lib/mask.ts` (`9` angka, `a` huruf, `*` huruf/angka, `h` hex;
    lainnya literal). Nilainya data tanpa tanda baca (`081234567890`); ke API dikirim itu, bukan teks bermask. Tambah mask baru di `MASKS`.
  - **Huruf dan karakter terbatas** (nama, kode): `FilteredInput` dengan himpunan karakter (`NAME_CHARACTER`, `ALPHANUMERIC_CHARACTER`, `CODE_CHARACTER`, sama dengan aturan server):
    karakter lain tidak pernah masuk, diketik maupun ditempel.
  - **Warna:** `ColorInput` (`#rrggbb`); **kode OTP:** `CodeInput`; **kata sandi:** `PasswordInput`; **tanggal:** `DatePicker`; **pilihan:** `Combobox`
    (banyak opsi) atau `RadioGroup` (sampai sekitar 5 opsi yang perlu terlihat); **teks panjang:** `Textarea` (beri `maxLength` untuk penghitung).
  - **Centang vs sakelar:** `Checkbox` untuk pilihan yang menunggu tombol Simpan; `Switch` untuk pengaturan yang berlaku seketika.
  - **Berkas:** `FileInput` (klik atau seret; batasi `accept` dan `maxBytes`; ukuran dan tipe tetap divalidasi ulang di backend) bila berkas dikirim nanti bersama formulir.
    Berkas yang **langsung dikirim** (beberapa sekaligus, dengan progres): `FileUploader`: dropzone dan antrean, dua unggahan serentak, bilah progres per berkas, batalkan, coba lagi, dan pesan penolakan.
    Layar hanya memberi fungsi `upload(file, { onProgress, signal })`; fungsi itu harus berhenti saat `signal` dibatalkan. Di proyek ini belum ada unggahan sungguhan (UI Guideline memakai unggahan pura-pura).
  - Isian berformat tetap teks bagi pembaca layar: jangan ganti `inputMode`/`autoComplete` yang sudah ada, dan bungkus semuanya dengan `Field`
    (`Field` menyambungkan label, hint, dan galat ke isian lewat `id`, `aria-describedby`, `aria-invalid`).
- **Layar menegakkan apa yang ditegakkan server.** Aturan server tentang *apa yang boleh diketik* (hanya angka, nama tanpa angka, panjang, rentang) ditegakkan juga oleh
  isian, sehingga kolom yang tidak bisa memuat suatu karakter tidak pernah menampilkannya; server tetap yang berwenang dan memeriksa lagi. Pilih kontrol menurut aturannya:
  `MaskedInput` untuk format angka (`'9'.repeat(n)` untuk angka polos), `FilteredInput` untuk huruf atau huruf dan angka, `DatePicker` untuk tanggal, `Combobox` untuk pilihan (`MultiCombobox` bila boleh lebih dari satu), dan
  **`maxLength` pada setiap isian teks bebas** (kata sandi 128, nama 100, dst.). `type="number"` dan `type="tel"` **dilarang** **[mesin]** (menerima `e`, tanda, dan huruf). Kirim nilai
  tanpa format. Bila satu aturan berubah di satu sisi, ubah sisi lainnya di commit yang sama dan simpan tes yang mengetik karakter salah ke isian.
- **Tanggal, waktu, dan angka untuk orang** ditulis di satu tempat, `lib/format.ts` (`formatDate`, `formatDateTime`, `formatRelative`, `formatNumber`, `rupiah`; `CurrencyInput` untuk mengetik uang);
  layar tidak memanggil `format`, `Intl`, atau `toLocale...String` sendiri **[mesin]**. Nilai untuk mesin (API, token) tetap ISO dan angka polos; nilai kosong tampil sebagai tanda strip ("–").
- **Menu:** satu fitur satu nama dari label sampai berkas: alamat adalah bentuk Inggris dari label (`/audit-logs` untuk "Audit Logs", `/ui-guideline` untuk "UI Guideline"), berkas `pages/audit-logs.tsx`,
  route Inertia bernama sama di `routes/web.php`, dan tesnya memakai kata yang sama (`layouts/menu-names.test.ts` gagal bila tidak). Judul layar sama dengan label. Label menu satu kata, paling banyak dua, maksimal 20 karakter (D-0036),
  tanpa garis miring **[mesin]**; nama panjang ada di judul halaman. Urutan mengikuti urutan pekerjaan (data yang dibutuhkan data lain lebih dulu). Halaman sejenis dikelompokkan sebagai submenu
  (`{ label, icon, children }` di `layouts/menu.ts`): tertutup sampai dibuka, terbuka sendiri selama salah satu halamannya tampil. Daftarnya satu: `layouts/menu.ts`.
- **Grafik:** `DonutChart` untuk sedikit bagian dari satu keseluruhan, `ColumnChart` untuk rentang berurutan, `BarChart` untuk nama panjang. **Angka selalu tertulis** di samping tiap bagian
  (gambar hanya pelengkap, tersembunyi dari pembaca layar); warna `chart-1` sampai `chart-5` tidak bermakna status dan tiap warnanya minimal 3:1 terhadap latar (dijaga tes).
- **Lapisan mengambang** (dialog, laci, popover, dropdown, tooltip) hanya lewat komponen di `components/ui` (Radix); `fixed inset-0` buatan sendiri dilarang **[mesin]**
  (laci menu ponsel juga dialog Radix). Perilaku yang wajib dan dijaga tes:
  - dialog punya nama (judul) dan peran; `Esc` dan tombol tutup menutupnya; fokus pindah ke dalam saat dibuka dan **kembali ke tombol pembuka** saat ditutup (`useRestoreFocus`
    di `Modal`, `ConfirmDialog`, dan laci, karena dibuka lewat state, bukan `Trigger` Radix);
  - dialog berisi isian memakai `persistent` (klik di luar tidak menutup, supaya isian tidak hilang tak sengaja); dialog baca-saja boleh menutup lewat klik di luar;
  - **semua dialog (`Modal` dan `ConfirmDialog`) tampil di bagian atas layar** (`top-4`, dari `sm` `top-12`), tidak di tengah, supaya muncul di tempat yang sama di setiap halaman;
  - dialog lebih tinggi dari layar menggulir utuh (`max-h-[calc(100dvh-2rem)]`, dari `sm` `4.5rem`), jangan membuat gulir bersarang di `DialogBody`;
  - popover (notifikasi, Combobox, DatePicker) punya `aria-label`; tooltip hanya untuk tombol ikon (nama tetap lewat `aria-label`);
  - animasi hanya pudar (`overlay-fade`), dan mati bila pengguna meminta gerak berkurang;
  - urutan lapisan: lihat tabel Token dan CSS (panel Combobox di dalam dialog tampil di atasnya).
- **Toolbar daftar:** `FilterBar` + `SearchInput`; pencarian sendirian di kiri, semua filter dan tombol Reset di kanan.
- **Tabel:** semua tabel memakai `DataTable` (`<table>` mentah dilarang **[mesin]**). Komponen ini mengurus kartu, toolbar, urutan (`sort`), skeleton,
  kosong, galat + coba lagi, `onRowClick`, paginasi, dan gulir `relative overflow-x-auto`. Definisikan kolom sebagai `Column<Row>[]`:
  `sort` (kunci urutan), `align: 'right'` untuk angka, `srOnly` + `narrow` untuk kolom aksi (klik di sana tidak
  memicu klik baris). **Kolom tidak pernah disembunyikan di layar sempit** (tidak ada `hideBelow`): sel dan header tidak membungkus teks (`whitespace-nowrap`),
  header berhuruf kapital dengan sedikit jarak huruf dan satu ukuran lebih kecil dari isi (`text-2xs`), dan tabel yang lebar menggulir ke samping di dalam kartunya. Dalam kartu/dialog: `bare`, dan `dense` untuk teks kecil. Contoh lengkap: bagian Tabel di halaman `/ui-guideline`.
  Pembungkus gulir wajib `relative`, kalau tidak elemen `absolute` (mis. `sr-only`) melebarkan halaman di ponsel.
- **Paginasi:** di kiri pilihan "baris per halaman" lalu teks jumlah baris ("1–25 dari 80", disembunyikan di ponsel); di kanan nomor halaman (bila lebih dari satu halaman). Susunannya satu, di `components/ui/pagination.tsx`, jadi semua tabel mengikuti.
- **Combobox:** jangan memotong identitas di daftar; daftar boleh lebih lebar dan membungkus baris. Untuk opsi yang mirip, isi `description`
  (ikut dicari). Di dalam `Modal`, periksa gulir roda/sentuh dan navigasi keyboard (opsi aktif harus terlihat).
- **DatePicker:** kalender kosong terbuka di bulan berjalan (atau bulan terdekat dalam rentang), bukan di ujung rentang. Pakai kelas
  `compact-calendar` (sel 28px).
- **Urutan di daftar:** **semua kolom yang berisi data punya tombol header** (`sort` di kolom **dan** properti `sort` di `DataTable`); hanya kolom aksi (`srOnly`) yang tidak. `DataTable` memperingatkan di konsol
  bila ada kolom `sort` tanpa properti `sort`, atau kolom berisi data tanpa kunci `sort` di tabel yang diurutkan. Daftar yang dipaginasi server diurutkan di server dengan kunci yang sama di daftar putih
  (ditambah bersama; kunci tak dikenal jatuh ke urutan bawaan), dan **server menyiapkan indeks** untuk tiap kunci. Klik pertama pada kolom baru mengurutkan naik, klik berikutnya membalik; "Atur ulang" mengembalikan urutan bawaan.
- **Responsif: ukuran uji yang wajib.** Periksa setiap layar yang diubah di **desktop 1280px** (resolusi desktop acuan; bukan lebar bawaan panel peramban atau jendela), tablet **768px**, dan ponsel **375px**
  (juga 320px untuk ponsel terkecil dan 390–430px untuk ponsel umum; 1024px dan 1440px bila tata letaknya berubah di sana). Gunakan isi panjang yang realistis dan periksa keadaan memuat, kosong, dan galat.
  Pada setiap lebar: `document.documentElement.scrollWidth` sama dengan lebar layar (tanpa gulir horizontal halaman; hanya tabel yang menggulir di kartunya), tidak ada kontrol tanpa nama, satu `h1`, dialog dan laci muat
  di layar (dialog yang lebih tinggi menggulir). Halaman tamu (masuk, kata sandi kedaluwarsa, verifikasi) diperiksa pula. Komponen UI baru punya contoh di `/ui-guideline`, dan token warna baru masuk daftar token (tes menegakkan keduanya).

<a id="token"></a>
## 4. Token dan CSS

Satu-satunya sumber gaya adalah `resources/css/app.css` (Tailwind v4, `@theme`). Jangan menulis warna atau ukuran baru di komponen (warna tertulis langsung **[mesin]**; ukuran teks sembarang seperti `text-[11px]` ditolak **[mesin]**); ubah token di sana. Warna status **hanya** lewat tangga di tabel di bawah
(`bg-success-soft text-success-ink`, dst.), bukan palet Tailwind (`emerald`, `amber`, `red`); pesan sebaris memakai `Alert`, bukan `div` buatan sendiri.
Ganti warna utama dengan mengubah token `--color-primary` dan turunannya di `resources/css/app.css`, dan nama merek di `lib/brand.ts`.
`html { font-size: 15px }` sehingga `1rem = 15px`. Warna status dijaga mesin **[mesin]**: palet Tailwind (`emerald`, `amber`, `red`, ...) ditolak.

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `--font-sans` | Instrument Sans, lalu font sistem | seluruh teks |
| `--color-primary` / `-hover` / `-soft` | `oklch(0.51 0.086 186)` (teal `#0f766e`) / `oklch(0.46 0.086 186)` / `oklch(0.96 0.02 186)` | tombol utama, tautan, menu aktif, fokus / disorot / latar lembut (menu aktif, area tarik berkas) |
| `--color-on-primary` | `#ffffff` | teks dan ikon di atas isian primary atau danger (tombol, centang, lencana): **bukan** `text-white` |
| `--color-surface` | `#ffffff` | kartu, sidebar, header, dialog, dropdown |
| `--color-canvas` | `oklch(0.975 0.003 260)` | latar halaman, header tabel, hover |
| `--color-line` | `oklch(0.92 0.006 260)` | semua garis dan batas |
| `--color-ink` / `--color-muted` | `oklch(0.24 0.02 265)` / `oklch(0.55 0.02 265)` | teks utama / label, hint, header tabel |
| `--color-danger` | `oklch(0.55 0.2 27)` | galat, tombol hapus |
| `--color-{success,warning,info,danger}` + `-soft` / `-line` / `-ink` | tangga seragam: soft L 0.97, line L 0.88, ink L 0.42–0.45; hue 155 / 80 / 250 / 27 | status: badge, `Alert`, toast, indikator jaringan |
| `--color-chart-1` … `-5` | lima rona berbobot setara, tiap warnanya ≥ 3:1 terhadap `surface` | seri grafik (tanpa arti status) |
| `--text-2xs` / `--text-xs` / `--text-sm` | `0.6875rem` / `0.75rem` / `0.8125rem` | badge, waktu, label seksi, penghitung / label dan hint / isi, sel tabel, tombol |

| Elemen | Aturan |
|---|---|
| Kontrol | tinggi `h-8`; tombol kecil `h-7`; tombol ikon `size-7`; font `text-sm` |
| Ikon | lucide-react, `size-3.5` di tombol, `size-4` di menu |
| Radius | kontrol `rounded-md`, kartu `rounded-lg border border-line bg-surface`; `rounded` untuk chip, kotak centang, dan kode; `rounded-full` untuk bulatan |
| Teks | `text-2xs` keterangan sangat kecil; `text-xs` label/hint; `text-sm` isi; `text-base` judul halaman dan merek; ukuran besar hanya di halaman 404 |
| Bayangan | `shadow-sm` jempol sakelar, `shadow-md` penanda mengambang, `shadow-lg` dropdown/popover, `shadow-xl` dialog/laci |
| Lapisan | header `z-30`, laci `z-40`, dialog dan penanda jaringan `z-50`, tooltip `z-60`, popover `z-70` |
| Tabel | sel `px-3 py-1` (kolom aksi `py-0.5`: tombol ikonnya yang menentukan tinggi baris), header `text-2xs text-muted bg-canvas` kapital, `divide-y divide-line` |

**Tema gelap.** Nama token sama; nilai gelapnya ada di `resources/css/app.css` (blok `:root[data-theme='dark']` dan salinannya di `@media (prefers-color-scheme: dark)`, harus identik **[tes]**).
Bawaannya mengikuti sistem; `lib/theme.ts` menyimpan pilihan Terang, Gelap, atau Ikuti sistem (preferensi, bukan rahasia, jadi satu-satunya pengecualian aturan `localStorage`) dan `public/theme-init.js`
menerapkannya sebelum halaman tergambar (skrip biasa, dimuat dari `resources/views/app.blade.php` sebelum `@vite`). Nilai gelap primary memakai rona teal proyek (186), bukan rona template. Aturan menulis tampilan:

- hanya token (tak ada warna tertulis, tak ada `text-white` atau `bg-white` untuk UI);
- isian solid primary atau danger memakai `text-on-primary`; tooltip `bg-ink text-surface`;
- token baru wajib punya nilai terang **dan** gelap, dan lolos tes kontras kedua tema (`index.contrast.test.ts`: teks 4.5:1, ikon dan grafik 3:1).

**Gerak (animasi).** Halus dan singkat; tidak ada yang memantul. Satu easing (`--ease-soft`) dan tiga durasi (`--motion-fast` 140 ms, `--motion-base` 220 ms, `--motion-slow` 320 ms) di `resources/css/app.css`.
Layar tidak menulis keyframe sendiri: pakai kelas `motion-*` **[tes]**.

| Kelas | Dipakai untuk |
|---|---|
| `motion-page` | halaman yang baru tampil (dibungkus layout per alamat), keadaan kosong atau galat, blok yang muncul: memudar dan naik 6px |
| `motion-pop` | dialog, popover, dropdown, tooltip: memudar dan membesar dari 97% di titik jangkarnya |
| `motion-slide-right` / `motion-slide-left` | laci (`Drawer`) dan menu ponsel |
| `motion-accordion` | buka-tutup `Accordion` |
| `motion-item` + `--i` | item daftar atau kotak muncul bergantian (`style={{ '--i': Math.min(index, 12) }}`) |
| `motion-row` | baris `DataTable` |
| `motion-grow-x` / `motion-grow-y` | batang grafik dan bilah yang tumbuh dari dasarnya |

Dengan `prefers-reduced-motion` semua kelas `motion-*` dimatikan dan transisi hampir seketika; tes menjaga setiap kelas `motion-*` terdaftar di blok itu. Animasi penutupan memakai `data-state="closed"` Radix.

Layar di luar kerangka aplikasi (masuk, verifikasi 2 langkah, kata sandi kedaluwarsa) memakai `AuthLayout`; halaman error memakai `ErrorPage` layar penuh (tanpa sidebar/header, judul berupa kata, ilustrasi dari `illustrations.tsx` yang warnanya lewat variabel token). 503 adalah halaman pemeliharaan.

Email dan halaman error statis tidak bisa memakai CSS aplikasi, jadi warnanya ditulis sebaris di templatenya sendiri.

<a id="data"></a>
## 5. Data, formulir, dan keamanan

- Validasi sebenarnya di backend; batasan UI (`maxLength`, `min`, `max`) **berpasangan** dengan aturan backend.
- Galat 422 dipetakan ke kolomnya lewat `useForm`; jangan tampilkan galat teknis mentah.
- Jangan simpan rahasia (kata sandi, token, kode) di `localStorage`/`sessionStorage`, URL, atau log. Keduanya dilarang **[mesin]**.

<a id="audit-mfa"></a>
## 6. Audit log, autentikasi, dan MFA (yang dijaga UI)

Aturan sisi server (hash, pembatasan percobaan, masa berlaku OTP, rantai hash, pencatatan kejadian) adalah tanggung jawab backend dan
ditegakkan di controller Laravel (lihat `auth.md`, `security.md`, dan `audit.md`). UI hanya menampilkan dan **tidak boleh melemahkannya**:

- **Audit log hanya dibaca:** tidak ada layar, tombol, atau panggilan untuk mengubah atau menghapus entri. UI menyediakan filter, detail
  sebelum/sesudah, ekspor CSV, dan tombol "Periksa integritas" yang menampilkan hasil verifikasi rantai dari server.
- **Rahasia tidak pernah tampil atau tersimpan di sisi klien** (kata sandi, kode OTP, kunci TOTP selain saat pengaturan awal, token): tidak di
  URL, `localStorage`, atau log. Kunci TOTP dan kode pemulihan hanya tampil sekali saat MFA diaktifkan.
- **Masa berlaku kata sandi:** bila server membalas `password_expired`, UI mengarahkan ke `/password-expired` (tiga kolom yang sama dengan Profil,
  lewat `components/password-fields.tsx`) dan menampilkan pesan 422 apa adanya.
- **MFA:** opsional per pengguna (authenticator atau OTP email); UI menampilkan jeda kirim ulang dari server dan kode pemulihan sekali pakai.
- Layar yang belum dilindungi MFA menampilkan pengingat di atas halaman.
- **Sesi tak aktif:** server memberi batas diam (`session.idle_minutes` di `/me`) dan yang menegakkannya; `IdleTimeout` menjaga layar tetap jujur: permintaan senyap sesekali selama orang bekerja,
  dialog "Sesi akan berakhir" satu menit sebelum habis (tetap masuk atau keluar), lalu keluar ke halaman masuk. Bila server mengakhiri sesi dengan sengaja (`401` `code: session_ended`), pesannya
  tampil sebagai toast sebelum halaman masuk.

<a id="tes"></a>
## 7. Tes

Vitest + Testing Library (jsdom); berkas tes `*.test.ts(x)` diletakkan di sebelah kodenya. `npm run test` (atau `npm run test:watch`), dan juga bagian dari `npm run check`.

- **Wajib dites:** logika murni (`lib/`), komponen isian yang punya perilaku (format, mask, kursor), perilaku aksesibilitas yang mudah rusak (label terhubung ke isian,
  `aria-describedby`).
- **Cara mengetes komponen:** lewat perilaku yang terlihat pengguna (`userEvent.type`, `getByLabelText`, `toHaveValue`), bukan detail implementasi (state, nama kelas).
- **UI Guideline harus lengkap:** tes `pages/ui-guideline.test.ts` gagal bila ada token warna di `resources/css/app.css` yang tidak ada di daftar token UI Guideline. Tes yang sama juga gagal bila ada komponen di `components/ui` yang
  tidak punya contoh di `/ui-guideline` (kecuali blok bangunan internal yang tercantum di tes).
- **Aksesibilitas otomatis:** `resources/js/a11y.test.tsx` menjalankan axe pada halaman masuk, halaman error, File Manage, dan UI Guideline (tanpa kontras: jsdom tidak punya warna, kontras dijaga `index.contrast.test.ts`). Halaman baru yang berarti ditambahkan ke sana.
- **Ujung ke ujung:** `e2e/*.e2e.ts` (Playwright) menjalankan aplikasi sungguhan (Laravel dan aset hasil `npm run build`) terhadap berkas SQLite sendiri (`database/e2e.sqlite`, dibuat ulang tiap dijalankan), dengan akun demo `superadmin`: `npm run test:e2e` (Chromium) atau `npm run test:e2e:all` (juga Firefox dan WebKit). Skenario papan ketik ikut menjaga fokus kembali setelah dialog, laci, dan menu. Butuh peramban Playwright: `npx playwright install`.
- Jangan membuat tes yang bergantung pada waktu nyata atau jaringan.

<a id="selesai"></a>
## 8. Sebelum selesai

```bash
npm run check      # tsc --noEmit + ESLint + Prettier + npm run standards + tes
npm run build      # membangun aset lalu memeriksa anggaran ukuran bundel (scripts/check-bundle.mjs)
```

Coba layar yang diubah di peramban (ponsel dan desktop), termasuk keadaan kosong, galat, dan memuat. Perubahan route yang terasa bagi pengguna dicatat di `CHANGELOG.md`. Pesan commit berbahasa Inggris, satu perubahan logis per commit.

## Dialog sampai 6 isian, halaman untuk lebih dari itu
Form tambah atau ubah dengan paling banyak 6 isian (termasuk pilihan, centang, dan isian alasan) memakai dialog. Lebih dari 6 isian dibuat halaman sendiri (`/<fitur>/create` dan `/<fitur>/{ulid}/edit`) dengan kartu per seksi dan footer Batal dan Simpan di kartu terakhir. Hapus dan konfirmasi singkat tetap dialog. Ditetapkan pemilik 2026-11-14.
