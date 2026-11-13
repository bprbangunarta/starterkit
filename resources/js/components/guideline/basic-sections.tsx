import { Copy, Database, Download, Inbox, Info, LayoutDashboard, MoreHorizontal, Pencil, Plus, Save, Search, Trash2 } from 'lucide-react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { ILLUSTRATIONS, Illustration } from '@/components/illustrations';
import type { IllustrationName } from '@/components/illustrations';
import { MenuLink, SubMenu } from '@/layouts/app-layout';
import { DropdownContent, DropdownItem, DropdownLabel, DropdownMenu, DropdownSeparator, DropdownTrigger } from '@/components/ui/dropdown';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Badge, Card, CardBody, CardFooter, CardHeader, EmptyState, ErrorState, Skeleton } from '@/components/ui/misc';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Tip } from '@/components/ui/tooltip';
import { dashboard, uiGuideline } from '@/routes';
import { TONES } from '@/components/guideline/guideline-data';
import { Section } from '@/components/guideline/section';

export function ButtonSection() {
    return (
        <Section title="Tombol" description="Aksi level halaman: ikon + label. Footer dialog: hanya teks. Aksi baris tabel: ikon saja dengan Tip.">
            <div className="flex flex-wrap items-center gap-2">
                <Button>
                    <Plus /> Tambah
                </Button>
                <Button variant="outline">
                    <Download /> Ekspor
                </Button>
                <Button variant="ghost">Ghost</Button>
                <Button variant="danger">
                    <Trash2 /> Hapus
                </Button>
                <Button size="sm">Kecil</Button>
                <Button loading>
                    <Save /> Menyimpan
                </Button>
                <Button disabled>Nonaktif</Button>
                <Button variant="ghost" size="icon" aria-label="Cari">
                    <Search />
                </Button>
            </div>
        </Section>
    );
}

export function BadgeAndStateSection() {
    return (
        <Section title="Badge, Kosong, Galat, dan Memuat">
            <div className="flex flex-wrap gap-2">
                {TONES.map((t) => (
                    <Badge key={t} tone={t}>
                        {t}
                    </Badge>
                ))}
            </div>
            <div className="grid gap-3 md:grid-cols-3">
                <Card>
                    <EmptyState
                        icon={<Inbox />}
                        title="Belum ada data"
                        description="Keadaan kosong dengan tombol aksi."
                        action={
                            <Button size="sm">
                                <Plus /> Tambah
                            </Button>
                        }
                    />
                </Card>
                <Card>
                    <ErrorState message="Server tidak dapat dihubungi." onRetry={() => toast.info('Mencoba lagi…')} />
                </Card>
                <Card className="flex flex-col gap-2 p-3">
                    <Skeleton className="h-4 w-2/3" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-1/2" />
                </Card>
            </div>
        </Section>
    );
}

export function CardSection() {
    return (
        <Section
            title="Kartu: Header, Body, Footer"
            description="Disusun seperti dialog. Footer (garis atas) hanya ada bila kartu punya tombol; tombol batal di kiri, konfirmasi di kanan, hanya teks. Kartu tanpa tombol tidak punya footer."
        >
            <div className="grid gap-3 md:grid-cols-2">
                <Card>
                    <CardHeader title="Kartu Form" description="Ada tombol, jadi ada footer" />
                    <CardBody className="flex flex-col gap-3">
                        <Field label="Nama" required>
                            <Input maxLength={100} placeholder="Ketik sesuatu" />
                        </Field>
                    </CardBody>
                    <CardFooter>
                        <p className="text-xs text-muted">Hint boleh di kiri footer.</p>
                        <Button onClick={() => toast.success('Tersimpan')}>Simpan</Button>
                    </CardFooter>
                </Card>
                <Card>
                    <CardHeader title="Kartu Informasi" description="Tanpa tombol, jadi tanpa footer" actions={<Badge tone="success">Aktif</Badge>} />
                    <CardBody>
                        <p className="text-sm">Isi kartu. Aksi di header (badge atau tombol ikon + label) boleh ada tanpa footer.</p>
                    </CardBody>
                </Card>
            </div>
        </Section>
    );
}

export function DropdownSection() {
    return (
        <Section
            title="Menu Dropdown dan Tooltip"
            description="Dropdown untuk beberapa aksi di satu tempat (menu akun). Aksi baris tabel memakai RowActions di atasnya, lihat tabel di bagian CRUD: item berikon, pemisah, dan item berbahaya di bawah. Tip memberi nama pada tombol ikon."
        >
            <div className="flex flex-wrap items-center gap-2">
                <DropdownMenu>
                    <DropdownTrigger asChild>
                        <Button variant="outline">
                            <MoreHorizontal /> Aksi
                        </Button>
                    </DropdownTrigger>
                    <DropdownContent>
                        <DropdownLabel>Proyek Alfa</DropdownLabel>
                        <DropdownItem icon={<Pencil />} onSelect={() => toast.info('Ubah')}>
                            Ubah
                        </DropdownItem>
                        <DropdownItem icon={<Copy />} onSelect={() => toast.info('Duplikat')}>
                            Duplikat
                        </DropdownItem>
                        <DropdownSeparator />
                        <DropdownItem danger icon={<Trash2 />} onSelect={() => toast.error('Hapus')}>
                            Hapus
                        </DropdownItem>
                    </DropdownContent>
                </DropdownMenu>
                <Tip label="Contoh tooltip">
                    <Button variant="ghost" size="icon" aria-label="Info">
                        <Info />
                    </Button>
                </Tip>
            </div>
        </Section>
    );
}

export function SidebarMenuSection() {
    return (
        <Section
            title="Menu Sidebar dengan Submenu"
            description="Induk adalah tombol (ikon, nama, panah) yang membuka dan menutup daftar halaman; submenu tidak berikon (garis dan titik). Tertutup sampai dibuka, dan terbuka sendiri saat salah satu halamannya tampil. Daftarnya ditulis di layouts/menu.ts: { label, icon, children }."
        >
            <nav aria-label="Contoh menu" className="flex w-60 flex-col gap-0.5 rounded-lg border border-line bg-surface p-2">
                <MenuLink item={{ label: 'Dashboard', to: dashboard().url, icon: LayoutDashboard, end: true }} onNavigate={() => undefined} />
                <SubMenu label="Data Master" icon={Database} onNavigate={() => undefined}>
                    {[
                        { label: 'Dashboard', to: dashboard().url },
                        { label: 'UI Guideline', to: uiGuideline().url },
                    ]}
                </SubMenu>
            </nav>
        </Section>
    );
}

export function IllustrationSection() {
    return (
        <Section
            title="Ilustrasi"
            description="Dipakai di layar masuk dan layar error. Warnanya token tema (ikut berubah saat rebranding), dibuat sekali di components/illustrations.tsx. Hiasan saja: teks di sebelahnya yang menyampaikan isinya."
        >
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                {(Object.keys(ILLUSTRATIONS) as IllustrationName[]).map((name) => (
                    <figure key={name} className="flex flex-col items-center gap-1 rounded-md border border-line p-2">
                        <Illustration name={name} className="w-full" />
                        <figcaption className="font-mono text-2xs text-muted">{name}</figcaption>
                    </figure>
                ))}
            </div>
        </Section>
    );
}

export function TabSection() {
    return (
        <Section title="Tab" description="Pindah antar tampilan di halaman yang sama tanpa memuat ulang.">
            <Tabs defaultValue="ringkasan">
                <TabsList>
                    <TabsTrigger value="ringkasan">Ringkasan</TabsTrigger>
                    <TabsTrigger value="riwayat">Riwayat</TabsTrigger>
                </TabsList>
                <TabsContent value="ringkasan" className="pt-3 text-sm">
                    Isi tab pertama.
                </TabsContent>
                <TabsContent value="riwayat" className="pt-3 text-sm">
                    Isi tab kedua.
                </TabsContent>
            </Tabs>
        </Section>
    );
}
