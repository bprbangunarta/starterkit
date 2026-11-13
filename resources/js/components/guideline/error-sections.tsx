import { Section } from '@/components/guideline/section';
import { buttonVariants } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ERROR_PAGES as ERROR_SCREENS } from '@/lib/error-pages';
import { APP_NAME } from '@/lib/brand';
import { show } from '@/routes/ui-guideline/errors';
import { renderLoginCodeEmail } from '../../../../email/login-code';

/** The static error pages (resources/views/errors, made by `npm run error-pages`), keyed by status code. */
const ERROR_PAGES = Object.entries(import.meta.glob<string>('../../../views/errors/*.blade.php', { query: '?raw', import: 'default', eager: true }))
    .map(([file, html]) => ({ code: /(\d{3})\.blade\.php$/.exec(file)?.[1] ?? file, html }))
    .filter((page) => ERROR_SCREENS.some((screen) => String(screen.status) === page.code))
    .sort((a, b) => a.code.localeCompare(b.code));

const ERROR_LABELS: Record<string, string> = {
    '401': 'Perlu masuk',
    '403': 'Akses ditolak',
    '404': 'Tidak ditemukan',
    '419': 'Kedaluwarsa',
    '429': 'Terlalu banyak',
    '500': 'Galat server',
    '503': 'Pemeliharaan',
};

export function ErrorPagesSection() {
    return (
        <Section
            title="Halaman error dan pemeliharaan"
            description="Layar penuh tanpa sidebar dan header: ilustrasi, apa yang terjadi dalam kata-kata (bukan hanya angka), kode kecil, dan jalan keluar. Tab di bawah adalah HTML statis di resources/views/errors yang ditampilkan Laravel saat aplikasi tidak bisa menjawab (npm run error-pages); tautan di bawahnya membuka layar yang sama di dalam aplikasi."
        >
            <Tabs defaultValue="503">
                <TabsList className="flex-wrap">
                    {ERROR_PAGES.map((page) => (
                        <TabsTrigger key={page.code} value={page.code}>
                            {page.code} {ERROR_LABELS[page.code]}
                        </TabsTrigger>
                    ))}
                </TabsList>
                {ERROR_PAGES.map((page) => (
                    <TabsContent key={page.code} value={page.code} className="pt-3">
                        <iframe
                            title={`Halaman error ${page.code}`}
                            sandbox=""
                            srcDoc={page.html}
                            className="h-[420px] w-full rounded-md border border-line bg-white"
                        />
                    </TabsContent>
                ))}
            </Tabs>
            <div className="flex flex-wrap items-center gap-2 text-xs text-muted">
                <span>Di dalam aplikasi, layar penuh:</span>
                {ERROR_SCREENS.map((screen) => (
                    <a
                        key={screen.status}
                        href={show(screen.status).url}
                        target="_blank"
                        rel="noreferrer"
                        className={buttonVariants({ variant: 'outline', size: 'sm' })}
                    >
                        {screen.status}
                    </a>
                ))}
            </div>
        </Section>
    );
}

export function EmailSection() {
    const email = renderLoginCodeEmail({ appName: APP_NAME, code: '482915', minutes: 10, name: 'Rina Wulandari', sentAt: '1 Oktober 2026, 21:16 WIB' });

    return (
        <Section
            title="Email kode OTP"
            description="Pratinjau templatenya (email/login-code.ts). Versi Blade untuk Laravel ada di resources/views/emails; belum dikirim sampai verifikasi dua langkah dikerjakan."
        >
            <iframe title="Pratinjau email OTP" srcDoc={email.html} className="h-[560px] w-full rounded-md border border-line bg-white" />
            <pre className="max-h-56 overflow-auto rounded-md bg-canvas p-3 text-xs whitespace-pre-wrap">{email.text}</pre>
        </Section>
    );
}
