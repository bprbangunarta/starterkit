import { useState } from 'react';
import { Section } from '@/components/guideline/section';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Breadcrumb } from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Stepper } from '@/components/ui/stepper';
import { dashboard, uiGuideline } from '@/routes';

export function NavigationSection() {
    const [step, setStep] = useState(1);

    return (
        <Section
            title="Navigasi, langkah, dan progres"
            description="Breadcrumb di atas PageHeader (induk berupa tautan, halaman ini teks). Stepper untuk proses bertahap; vertikal di ponsel. Progress untuk kuota atau tugas; tanpa nilai berarti durasi tidak diketahui. Accordion melipat bagian yang jarang dibuka."
        >
            <Breadcrumb items={[{ label: 'Dashboard', to: dashboard().url }, { label: 'UI Guideline', to: uiGuideline().url }, { label: 'Komponen' }]} />
            <Stepper
                steps={[{ label: 'Data diri', description: 'Nama dan kontak' }, { label: 'Dokumen', description: 'Unggah berkas' }, { label: 'Konfirmasi' }]}
                current={step}
            />
            <div className="flex gap-2">
                <Button variant="outline" disabled={step === 0} onClick={() => setStep((n) => Math.max(0, n - 1))}>
                    Sebelumnya
                </Button>
                <Button variant="outline" disabled={step === 3} onClick={() => setStep((n) => Math.min(3, n + 1))}>
                    Berikutnya
                </Button>
            </div>
            <div className="grid gap-3 md:grid-cols-2">
                <Progress label="Kuota penyimpanan" value={72} />
                <Progress label="Batas hampir habis" value={92} tone="danger" />
                <Progress label="Selesai" value={100} tone="success" />
                <Progress aria-label="Memproses" />
            </div>
            <Accordion type="single" collapsible defaultValue="a" className="rounded-md border border-line px-3">
                <AccordionItem value="a">
                    <AccordionTrigger>Apa itu panduan ini?</AccordionTrigger>
                    <AccordionContent>Acuan hidup untuk komponen dan token yang dipakai semua layar.</AccordionContent>
                </AccordionItem>
                <AccordionItem value="b">
                    <AccordionTrigger>Kapan komponen baru dibuat?</AccordionTrigger>
                    <AccordionContent>Hanya bila belum ada di sini; lihat dulu sebelum membuat sendiri.</AccordionContent>
                </AccordionItem>
            </Accordion>
        </Section>
    );
}
