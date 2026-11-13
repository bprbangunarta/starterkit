import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import type { RoleRow } from '@/components/roles/types';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { store, update } from '@/routes/roles';

/** Adds a role, or renames the one given. */
export function RoleFormDialog({ role, onClose }: { role: RoleRow | null; onClose: () => void }) {
    const form = useForm({ name: role?.name ?? '' });

    const submit = () => {
        const done = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(role ? 'Jabatan diubah.' : 'Jabatan ditambahkan.');
                onClose();
            },
        };

        if (role) {
            form.put(update(role.id).url, done);
        } else {
            form.post(store().url, done);
        }
    };

    return (
        <Modal
            persistent
            open
            onOpenChange={(open) => !open && onClose()}
            title={role ? 'Ubah Jabatan' : 'Tambah Jabatan'}
            description="Isi lalu simpan. Galat validasi tampil di bawah kolom."
        >
            <form
                noValidate
                onSubmit={(event) => {
                    event.preventDefault();
                    submit();
                }}
            >
                <DialogBody>
                    <Field label="Nama" required error={form.errors.name} hint="Huruf, angka, spasi, titik, dan strip; paling banyak 60 karakter">
                        <Input
                            autoFocus
                            maxLength={60}
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            aria-invalid={!!form.errors.name}
                        />
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
