import { Button } from '@/components/ui/button';
import { BarChart, ColumnChart, DonutChart } from '@/components/ui/chart';
import { Alert, Badge } from '@/components/ui/misc';
import { CHART_BARS, CHART_COLUMNS, CHART_DONUT, STATUS, TOKENS } from '@/components/guideline/guideline-data';
import { Section, SubTitle } from '@/components/guideline/section';

export function ColorSection() {
    return (
        <Section
            title="Warna (Token Tema)"
            description="Semua token warna di resources/css/app.css, termasuk keluarga status (success, warning, info, danger). Hanya lewat token; jangan menulis warna sendiri."
        >
            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                {TOKENS.map(([name, swatch, use]) => (
                    <div key={name} className="flex min-w-0 items-center gap-2.5">
                        <span className={`size-8 shrink-0 rounded-md border border-line ${swatch}`} />
                        <div className="min-w-0">
                            <p className="font-mono text-xs">{name}</p>
                            <p className="truncate text-xs text-muted">{use}</p>
                        </div>
                    </div>
                ))}
            </div>
        </Section>
    );
}

export function TypographySection() {
    return (
        <Section
            title="Tipografi dan Ukuran"
            description="Font Instrument Sans. Isi text-sm (0.8125rem), label dan hint text-xs, keterangan sangat kecil text-2xs, kontrol h-8, radius rounded-md, kartu rounded-lg border border-line."
        >
            <p className="text-base font-semibold">Judul halaman (text-base font-semibold)</p>
            <p className="text-sm">Isi dan sel tabel (text-sm)</p>
            <p className="text-xs text-muted">Label, keterangan, dan hint (text-xs text-muted)</p>
            <p className="text-2xs text-muted">Keterangan sangat kecil: badge, waktu notifikasi, label seksi sidebar, penghitung (text-2xs)</p>
            <p className="text-xs text-muted">
                Bayangan: <code>shadow-sm</code> bidang kecil (jempol sakelar), <code>shadow-md</code> penanda mengambang, <code>shadow-lg</code> dropdown dan
                popover, <code>shadow-xl</code> dialog dan laci. Lapisan: header <code>z-30</code>, laci <code>z-40</code>, dialog dan penanda jaringan{' '}
                <code>z-50</code>, tooltip <code>z-60</code>, popover <code>z-70</code>.
            </p>
        </Section>
    );
}

export function ChartSection() {
    return (
        <Section
            title="Grafik"
            description="Donat untuk sedikit bagian dari satu keseluruhan, kolom untuk rentang yang berurutan, batang mendatar untuk nama panjang. Angka selalu tertulis di samping bagiannya; warna hanya pelengkap."
        >
            <div className="grid gap-4 md:grid-cols-3">
                <div className="flex flex-col gap-2">
                    <SubTitle>DonutChart</SubTitle>
                    <DonutChart label="Contoh donat" items={CHART_DONUT} centerLabel="contoh" />
                </div>
                <div className="flex flex-col gap-2">
                    <SubTitle>ColumnChart</SubTitle>
                    <ColumnChart label="Contoh kolom" items={CHART_COLUMNS} />
                </div>
                <div className="flex flex-col gap-2">
                    <SubTitle>BarChart</SubTitle>
                    <BarChart label="Contoh batang" items={CHART_BARS} />
                </div>
            </div>
        </Section>
    );
}

export function StatusColorSection() {
    return (
        <Section
            title="Warna Status (Badge, Alert, Toast)"
            description="Satu keluarga warna: tiap nada punya base (ikon), soft (latar), line (garis), dan ink (teks). Toast, badge, alert, dan indikator jaringan memakai token yang sama."
        >
            <div className="grid gap-2.5">
                {STATUS.map((s) => (
                    <div key={s.tone} className="grid items-center gap-2 md:grid-cols-[9rem_8rem_auto_1fr_auto]">
                        <p className="text-xs font-medium">{s.label}</p>
                        <div className="flex gap-1">
                            {s.swatches.map((c) => (
                                <span key={c} title={c} className={`size-6 rounded border border-line ${c}`} />
                            ))}
                        </div>
                        <Badge tone={s.tone} className="justify-self-start">
                            {s.tone}
                        </Badge>
                        <Alert tone={s.tone} className="py-1.5 text-xs">
                            Contoh pesan {s.label.toLowerCase()} di dalam halaman.
                        </Alert>
                        <Button variant="outline" size="sm" className="justify-self-start" onClick={s.notify}>
                            Coba toast
                        </Button>
                    </div>
                ))}
            </div>
        </Section>
    );
}
