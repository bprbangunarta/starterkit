# Alur kerja fitur baru

Gate di `.claude/hooks` hanya menuntut rule dibaca, skill dimuat, dan `search-docs` dipanggil sekali per konteks. Itu batas bawah, bukan alur kerja. Setiap fitur baru mengikuti langkah berikut.

## Sebelum menulis kode

- Catat instruksi pemilik di `docs/instructions.md` dan tinjau dampaknya sebelum mengerjakan. Jangan mencatatnya setelah selesai.
- Untuk layar atau komponen UI, buka padanannya di folder `src` template react-starter dan adaptasi untuk Inertia. Jalur folder itu tidak disimpan di repositori ini: jika belum diketahui di sesi ini, tanyakan ke pemilik dan jangan menebaknya. Sebutkan di laporan akhir apa yang dibuat baru.
- Baca file `.ai/rules` yang cocok dengan path memakai tool Read, bukan `cat` di Bash. Untuk skill `laravel-best-practices` dan `testing-best-practices`, baca juga file di folder `rules/` skill itu yang sesuai topik fitur.
- Panggil `search-docs` untuk setiap API Laravel, Inertia, Pest, atau paket lain yang dipakai fitur. Alasan "polanya sudah ada di kode lain" tidak cukup untuk melewatinya. Dokumentasi Inertia 3 belum tentu tercakup, jadi cocokkan hasilnya dengan skill yang terpasang.
- Lihat skema dengan `database-schema` sebelum membuat migration atau model. Lihat data dengan `database-query`, bukan klien `mysql` atau tinker.

## Lebih dari satu fitur dalam satu sesi

- Gate tidak menghitung ulang. Ulangi langkah di atas untuk setiap fitur, atau mulai sesi baru dengan `/clear` setelah fitur selesai.

## Laporan akhir

- Sebut panggilan `search-docs` dan `database-schema`, template yang dibuka, dan komponen yang dibuat baru.
