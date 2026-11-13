import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import type { S3Entry } from '@/components/storage/types';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/choice';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { PasswordInput } from '@/components/ui/password-input';
import { update } from '@/routes/storage';

type Props = { entry: S3Entry; onClose: () => void };

/** The connection values of Amazon S3 or a compatible service. An empty secret keeps the one that was saved. */
export function S3FormDialog({ entry, onClose }: Props) {
    const form = useForm({ key: '', secret: '', ...entry.form });
    const set = (field: 'key' | 'secret' | 'region' | 'bucket' | 'endpoint' | 'root', value: string) => form.setData(field, value);
    const kept = (isSet: boolean) => (isSet ? 'Sudah tersimpan; kosongkan untuk tetap memakainya.' : undefined);

    return (
        <Modal persistent wide open onOpenChange={(open) => !open && onClose()} title="Ubah Konfigurasi Amazon S3" description="Yang disimpan di sini dipakai menggantikan bawaan .env.">
            <form
                noValidate
                onSubmit={(event) => {
                    event.preventDefault();
                    form.patch(update('s3').url, {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Konfigurasi S3 disimpan.');
                            onClose();
                        },
                    });
                }}
            >
                <DialogBody className="grid gap-3 sm:grid-cols-2">
                    <Field label="Access key" required={entry.secrets.key_hint === null} error={form.errors.key} hint={kept(entry.secrets.key_hint !== null)}>
                        <PasswordInput autoComplete="off" maxLength={128} placeholder={entry.secrets.key_hint ?? ''} value={form.data.key} onChange={(e) => set('key', e.target.value)} />
                    </Field>
                    <Field label="Secret key" required={!entry.secrets.secret_set} error={form.errors.secret} hint={kept(entry.secrets.secret_set)}>
                        <PasswordInput autoComplete="off" maxLength={255} value={form.data.secret} onChange={(e) => set('secret', e.target.value)} />
                    </Field>
                    <Field label="Region" required error={form.errors.region}>
                        <Input maxLength={50} value={form.data.region} onChange={(e) => set('region', e.target.value)} />
                    </Field>
                    <Field label="Bucket" required error={form.errors.bucket}>
                        <Input maxLength={63} value={form.data.bucket} onChange={(e) => set('bucket', e.target.value)} />
                    </Field>
                    <Field label="Endpoint" error={form.errors.endpoint} hint="Hanya untuk layanan yang kompatibel dengan S3 (misalnya MinIO); kosongkan untuk Amazon S3.">
                        <Input maxLength={255} placeholder="https://" value={form.data.endpoint} onChange={(e) => set('endpoint', e.target.value)} />
                    </Field>
                    <Field label="Folder dasar" error={form.errors.root} hint="Awalan di dalam bucket; kosong berarti seluruh bucket.">
                        <Input maxLength={200} value={form.data.root} onChange={(e) => set('root', e.target.value)} />
                    </Field>
                    <Switch
                        className="sm:col-span-2"
                        checked={form.data.use_path_style}
                        onCheckedChange={(checked) => form.setData('use_path_style', checked)}
                        label="Alamat bergaya path"
                        description="Nyalakan bila layanan memakai alamat endpoint/bucket, bukan bucket.endpoint."
                    />
                </DialogBody>
                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Batal
                    </Button>
                    <Button type="submit" loading={form.processing}>
                        Simpan
                    </Button>
                </DialogFooter>
            </form>
        </Modal>
    );
}
