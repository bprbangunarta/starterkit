import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import type { SftpEntry } from '@/components/storage/types';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { MaskedInput } from '@/components/ui/masked-input';
import { PasswordInput } from '@/components/ui/password-input';
import { Textarea } from '@/components/ui/textarea';
import { update } from '@/routes/storage';

type Props = { entry: SftpEntry; onClose: () => void };

/** The connection values of an SFTP server: a password, a private key, or both. An empty secret keeps the one that was saved. */
export function SftpFormDialog({ entry, onClose }: Props) {
    const form = useForm({ password: '', private_key: '', ...entry.form });
    const set = (field: 'host' | 'username' | 'password' | 'private_key' | 'root', value: string) => form.setData(field, value);
    const kept = (isSet: boolean) => (isSet ? 'Sudah tersimpan; kosongkan untuk tetap memakainya.' : undefined);

    return (
        <Modal persistent wide open onOpenChange={(open) => !open && onClose()} title="Ubah Konfigurasi SFTP" description="Yang disimpan di sini dipakai menggantikan bawaan .env.">
            <form
                noValidate
                onSubmit={(event) => {
                    event.preventDefault();
                    form.patch(update('sftp').url, {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Konfigurasi SFTP disimpan.');
                            onClose();
                        },
                    });
                }}
            >
                <DialogBody className="grid gap-3 sm:grid-cols-2">
                    <Field label="Host" required error={form.errors.host}>
                        <Input maxLength={255} autoComplete="off" value={form.data.host} onChange={(e) => set('host', e.target.value)} />
                    </Field>
                    <Field label="Port" required error={form.errors.port}>
                        <MaskedInput mask="99999" value={form.data.port} onValueChange={(value) => form.setData('port', value)} />
                    </Field>
                    <Field label="Username" required error={form.errors.username}>
                        <Input maxLength={100} autoComplete="off" value={form.data.username} onChange={(e) => set('username', e.target.value)} />
                    </Field>
                    <Field label="Folder dasar" error={form.errors.root} hint="Folder awal di server; kosong berarti folder utama akun.">
                        <Input maxLength={200} value={form.data.root} onChange={(e) => set('root', e.target.value)} />
                    </Field>
                    <Field label="Kata sandi" error={form.errors.password} hint={kept(entry.secrets.password_set) ?? 'Isi kata sandi atau kunci privat.'}>
                        <PasswordInput autoComplete="new-password" maxLength={255} value={form.data.password} onChange={(e) => set('password', e.target.value)} />
                    </Field>
                    <Field label="Kunci privat" error={form.errors.private_key} hint={kept(entry.secrets.private_key_set) ?? 'Tempel isi kunci (-----BEGIN … PRIVATE KEY-----).'} className="sm:col-span-2">
                        <Textarea className="font-mono" maxLength={8192} spellCheck={false} value={form.data.private_key} onChange={(e) => set('private_key', e.target.value)} />
                    </Field>
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
