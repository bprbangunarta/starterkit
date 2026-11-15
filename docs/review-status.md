# Status review fitur

Catatan ini menjaga agar review tidak diulang untuk fitur yang sudah ditinjau, dan tidak terlewat untuk fitur yang belum. Fokus utama sekarang membuat fitur; review dijalankan di akhir, sekali per fitur, atas permintaan pemilik (`/review-fitur`).

Terakhir diperbarui: 2026-11-15. Perbarui berkas ini setiap kali sebuah review selesai atau sebuah fitur baru dibuat.

## Cara memakai

- Sebelum meminta review, lihat tabel: hanya fitur berstatus **belum ditinjau** yang perlu dijalankan.
- Jalankan `/review-fitur <commit acuan>`. Commit acuan di tabel adalah commit tepat sebelum fitur dimulai, jadi hanya perubahan fitur itu yang masuk daftar berkas.
- `/review-fitur` hanya meninjau berkas yang cocok dengan `review.globs` di `.claude/hooks/boost-gate.json`. Perubahan yang seluruhnya frontend, aturan, atau dokumen menghasilkan daftar kosong (tercatat di kolom Catatan). UI diperiksa pemilik sendiri.
- Satu review per fitur. Temuan BLOKIR diperbaiki, lalu satu peninjauan ulang untuk berkas yang diperbaiki; sisanya dilaporkan, tidak ditinjau ketiga kali.
- Review menilai backend. Hasil `PASS` hanya berlaku untuk berkas yang masuk lingkup saat itu.

## Status

Arti status: **ditinjau** = sudah direview dan temuan BLOKIR diperbaiki; **belum ditinjau** = belum pernah direview; **belum tercatat** = riwayat git dan dokumen tidak menyebut review, jadi anggap belum sampai ada bukti lain; **di luar review** = tidak ada berkas dalam lingkup review.

Commit acuan fitur baru adalah commit tepat sebelum fitur dimulai (`git rev-parse HEAD` sebelum mengerjakan).

| Fitur | Status review | Bukti | Commit acuan | Catatan |
| --- | --- | --- | --- | --- |
| Seluruh fitur backend bawaan starterkit | ditinjau | ringkasan review 2026-11-14 selama pembuatan starterkit | commit pertama proyek turunan | Riwayat tiap review ada di commit `55a99fd` (`git show 55a99fd:docs/review-status.md`). Hanya diuji di disk tiruan untuk S3 dan SFTP |
