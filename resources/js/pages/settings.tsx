import { Head, useForm } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { BrandImageCard } from '@/components/settings/brand-image-card';
import type { BrandImage } from '@/components/settings/brand-image-card';
import { useReauth } from '@/components/reauth/reauth-provider';
import { Button } from '@/components/ui/button';
import { RadioGroup } from '@/components/ui/choice';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Card, CardBody, CardFooter, CardHeader, PageHeader } from '@/components/ui/misc';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { update } from '@/routes/settings';

type Props = {
    settings: { app_name: string; meta_description: string; active_storage: string };
    images: { logo: BrandImage; favicon: BrandImage };
    /** The storages that files may be saved to: the local one, and the others once their connection passed its test. */
    storages: { value: string; label: string }[];
};

/** The parameters of the project that can be changed while it runs. */
export default function Settings({ settings, images, storages }: Props) {
    const form = useForm(settings);
    const { confirmed } = useReauth();

    return (
        <>
            <Head title="Pengaturan" />
            <PageHeader title="Pengaturan" description="Konfigurasi proyek" />
            <form
                noValidate
                className="flex flex-col gap-3"
                onSubmit={(event) => {
                    event.preventDefault();
                    confirmed(() => form.patch(update().url, { preserveScroll: true, onSuccess: () => toast.success('Pengaturan disimpan.') }));
                }}
            >
                <Card>
                    <CardHeader title="Umum" description="Nama dan deskripsi yang tampil di tab peramban dan hasil pencarian" />
                    <CardBody className="grid gap-3 sm:grid-cols-2">
                        <Field label="Nama aplikasi" required error={form.errors.app_name} className="sm:col-span-2">
                            <Input
                                maxLength={60}
                                value={form.data.app_name}
                                onChange={(e) => form.setData('app_name', e.target.value)}
                                aria-invalid={!!form.errors.app_name}
                            />
                        </Field>
                        <Field
                            label="Meta deskripsi"
                            error={form.errors.meta_description}
                            hint="Ringkasan satu atau dua kalimat, paling banyak 160 huruf"
                            className="sm:col-span-2"
                        >
                            <Textarea
                                maxLength={160}
                                value={form.data.meta_description}
                                onChange={(e) => form.setData('meta_description', e.target.value)}
                                aria-invalid={!!form.errors.meta_description}
                            />
                        </Field>
                    </CardBody>
                </Card>
                <Card>
                    <CardHeader title="Penyimpanan" description="Tempat berkas proyek disimpan; yang lain tetap bisa dilihat di menu Penyimpanan" />
                    <CardBody>
                        <Field
                            label="Penyimpanan aktif"
                            error={form.errors.active_storage}
                            hint="Hanya penyimpanan yang konfigurasinya lengkap dan uji koneksinya berhasil yang tampil di sini"
                        >
                            <RadioGroup
                                aria-label="Penyimpanan aktif"
                                value={form.data.active_storage}
                                onValueChange={(value) => form.setData('active_storage', value)}
                                options={storages}
                            />
                        </Field>
                    </CardBody>
                    <CardFooter>
                        <Button type="submit" loading={form.processing}>
                            Simpan
                        </Button>
                    </CardFooter>
                </Card>
            </form>
            <div className="mt-3 grid gap-3 lg:grid-cols-2">
                <BrandImageCard kind="logo" title="Logo" image={images.logo} />
                <BrandImageCard kind="favicon" title="Favicon" image={images.favicon} />
            </div>
        </>
    );
}

Settings.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
