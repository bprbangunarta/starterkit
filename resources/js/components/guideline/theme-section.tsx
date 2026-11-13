import { Section } from '@/components/guideline/section';
import { ThemeMenu } from '@/components/theme-menu';

export function ThemeSection() {
    return (
        <Section
            title="Tema terang dan gelap"
            description="Nama token sama di kedua tema, jadi tampilan tidak berubah per layar. Bawaannya mengikuti sistem; pilihan Terang, Gelap, atau Ikuti sistem disimpan di peramban. Teks di atas isian primary atau danger memakai on-primary, bukan putih."
        >
            <div className="flex items-center gap-2">
                <ThemeMenu />
                <span className="text-xs text-muted">Ganti tema di sini atau di header, lalu lihat semua contoh di halaman ini ikut berubah.</span>
            </div>
        </Section>
    );
}
