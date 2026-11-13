import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import type { StorageEntry } from '@/components/storage/types';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { update } from '@/routes/storage/name';

type Props = { entry: StorageEntry; onClose: () => void };

/** Gives a storage a name of its own; leaving it empty goes back to the name of its kind. The connection values are not touched. */
export function StorageNameDialog({ entry, onClose }: Props) {
    const form = useForm({ name: entry.name ?? '' });

    return (
        <Modal persistent open onOpenChange={(open) => !open && onClose()} title="Ubah Nama Penyimpanan">
            <form
                noValidate
                onSubmit={(event) => {
                    event.preventDefault();
                    form.patch(update(entry.type).url, {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Nama penyimpanan disimpan.');
                            onClose();
                        },
                    });
                }}
            >
                <DialogBody>
                    <Field label="Nama" error={form.errors.name} hint={`Kosongkan untuk memakai nama jenisnya: ${entry.kind}.`}>
                        <Input autoFocus maxLength={100} placeholder={entry.kind} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
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
