import { toast } from 'sonner';
import type { BadgeTone } from '@/components/ui/misc';

/** Every `--color-*` token of resources/css/app.css (a test keeps this list complete). Class names are written out so Tailwind can see them. */
export const TOKENS = [
    ['primary', 'bg-primary', 'Warna utama: tombol, tautan, item menu aktif, fokus'],
    ['primary-hover', 'bg-primary-hover', 'Tombol utama saat disorot'],
    ['primary-soft', 'bg-primary-soft', 'Latar lembut warna utama (item menu aktif, area tarik berkas)'],
    ['surface', 'bg-surface', 'Kartu, sidebar, header, dialog'],
    ['on-primary', 'bg-on-primary', 'Teks dan ikon di atas isian primary atau danger'],
    ['canvas', 'bg-canvas', 'Latar halaman, header tabel, hover'],
    ['line', 'bg-line', 'Garis pemisah dan batas'],
    ['ink', 'bg-ink', 'Teks utama'],
    ['muted', 'bg-muted', 'Teks pendukung (label, hint, header tabel)'],
    ['danger', 'bg-danger', 'Galat, tombol hapus'],
    ['success', 'bg-success', 'Berhasil: ikon dan elemen solid'],
    ['success-soft', 'bg-success-soft', 'Berhasil: latar badge, alert, toast'],
    ['success-line', 'bg-success-line', 'Berhasil: garis tepi'],
    ['success-ink', 'bg-success-ink', 'Berhasil: teks di atas latar soft'],
    ['warning', 'bg-warning', 'Peringatan: ikon dan elemen solid'],
    ['warning-soft', 'bg-warning-soft', 'Peringatan: latar badge, alert, toast'],
    ['warning-line', 'bg-warning-line', 'Peringatan: garis tepi'],
    ['warning-ink', 'bg-warning-ink', 'Peringatan: teks di atas latar soft'],
    ['info', 'bg-info', 'Informasi: ikon dan elemen solid'],
    ['info-soft', 'bg-info-soft', 'Informasi: latar badge, alert, toast'],
    ['info-line', 'bg-info-line', 'Informasi: garis tepi'],
    ['info-ink', 'bg-info-ink', 'Informasi: teks di atas latar soft'],
    ['danger-soft', 'bg-danger-soft', 'Galat: latar badge, alert, toast'],
    ['danger-line', 'bg-danger-line', 'Galat: garis tepi'],
    ['danger-ink', 'bg-danger-ink', 'Galat: teks di atas latar soft'],
    ['chart-1', 'bg-chart-1', 'Grafik: warna seri 1 (tanpa arti status)'],
    ['chart-2', 'bg-chart-2', 'Grafik: warna seri 2 (tanpa arti status)'],
    ['chart-3', 'bg-chart-3', 'Grafik: warna seri 3 (tanpa arti status)'],
    ['chart-4', 'bg-chart-4', 'Grafik: warna seri 4 (tanpa arti status)'],
    ['chart-5', 'bg-chart-5', 'Grafik: warna seri 5 (tanpa arti status)'],
];

/** Status ladder: one row per tone. Class names are written out so Tailwind can see them. */
export const STATUS = [
    {
        tone: 'success',
        label: 'Berhasil',
        swatches: ['bg-success', 'bg-success-soft', 'bg-success-line', 'bg-success-ink'],
        notify: () => toast.success('Data tersimpan'),
    },
    { tone: 'info', label: 'Informasi', swatches: ['bg-info', 'bg-info-soft', 'bg-info-line', 'bg-info-ink'], notify: () => toast.info('Ada pembaruan data') },
    {
        tone: 'warning',
        label: 'Peringatan',
        swatches: ['bg-warning', 'bg-warning-soft', 'bg-warning-line', 'bg-warning-ink'],
        notify: () => toast.warning('Sesi hampir berakhir'),
    },
    {
        tone: 'danger',
        label: 'Galat',
        swatches: ['bg-danger', 'bg-danger-soft', 'bg-danger-line', 'bg-danger-ink'],
        notify: () => toast.error('Gagal menyimpan data'),
    },
] as const;

export const CHART_DONUT = [
    { label: 'Tetap', count: 42 },
    { label: 'Kontrak', count: 18 },
    { label: 'Trainee', count: 6 },
];
export const CHART_COLUMNS = [
    { label: '< 1', count: 6 },
    { label: '1-3', count: 14 },
    { label: '3-5', count: 20 },
    { label: '5-10', count: 17 },
    { label: '> 10', count: 9 },
];
export const CHART_BARS = [
    { label: 'Kantor Pusat', count: 31 },
    { label: 'Kantor Cabang Utara', count: 19 },
    { label: 'Kantor Kas Selatan', count: 11 },
    { label: 'Belum diisi', count: 5 },
];

export const TONES: BadgeTone[] = ['neutral', 'info', 'success', 'warning', 'danger'];
