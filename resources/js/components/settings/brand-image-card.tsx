import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import { useReauth } from '@/components/reauth/reauth-provider';
import { Button } from '@/components/ui/button';
import { Field } from '@/components/ui/field';
import { FileInput } from '@/components/ui/file-input';
import { Card, CardBody, CardFooter, CardHeader } from '@/components/ui/misc';
import { destroy, store } from '@/routes/settings/images';

export type BrandImage = { url: string; custom: boolean; mimes: string[]; max_kb: number };

type Props = { kind: 'logo' | 'favicon'; title: string; image: BrandImage };

/** One image of the brand: what is shown now, a file to replace it with, and a way back to the bundled one. */
export function BrandImageCard({ kind, title, image }: Props) {
    const form = useForm<{ file: File | null }>({ file: null });
    const { confirmed } = useReauth();
    const accept = image.mimes.map((extension) => `.${extension}`).join(',');

    const upload = () =>
        confirmed(() =>
            form.post(store(kind).url, {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    toast.success(`${title} diganti.`);
                },
            }),
        );

    const restore = () =>
        confirmed(() => form.delete(destroy(kind).url, { preserveScroll: true, onSuccess: () => toast.success(`${title} kembali ke bawaan.`) }));

    return (
        <Card>
            <CardHeader title={title} description={image.custom ? 'Gambar yang diunggah dipakai' : 'Gambar bawaan dipakai'} />
            <CardBody className="flex flex-col gap-3">
                <img src={image.url} alt={`${title} saat ini`} className="size-12 rounded-md border border-line bg-canvas object-contain p-1" />
                <Field label={`Ganti ${title.toLowerCase()}`} error={form.errors.file} hint={`${image.mimes.join(', ')}; paling besar ${image.max_kb} KB`}>
                    <FileInput
                        accept={accept}
                        maxBytes={image.max_kb * 1024}
                        value={form.data.file === null ? [] : [form.data.file]}
                        onValueChange={([file]) => form.setData('file', file ?? null)}
                        onReject={(message) => toast.error(message)}
                        aria-invalid={!!form.errors.file}
                    />
                </Field>
            </CardBody>
            <CardFooter>
                {image.custom && (
                    <Button variant="outline" onClick={restore} loading={form.processing}>
                        Kembalikan
                    </Button>
                )}
                <Button disabled={form.data.file === null} loading={form.processing} onClick={upload}>
                    Unggah
                </Button>
            </CardFooter>
        </Card>
    );
}
