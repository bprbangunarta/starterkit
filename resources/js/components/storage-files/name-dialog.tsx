import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import type { StorageItem } from '@/components/storage-files/types';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { update } from '@/routes/storage/files';
import { store } from '@/routes/storage/folders';

/** Creating a folder in the open folder, or renaming a file. */
export type NameDraft = { mode: 'create' } | { mode: 'rename'; item: StorageItem };

type Props = { storage: string; folder: string; draft: NameDraft; onClose: () => void };

/** Asks for the name of a new folder or the new name of a file; the server refuses a name that the folder already has. */
export function NameDialog({ storage, folder, draft, onClose }: Props) {
    const form = useForm({ path: draft.mode === 'rename' ? draft.item.path : folder, name: draft.mode === 'rename' ? draft.item.name : '' });

    const submit = () => {
        const done = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(draft.mode === 'create' ? 'Folder dibuat' : 'Nama diubah');
                onClose();
            },
            onError: (errors: Record<string, string>) => errors.storage && toast.error(errors.storage),
        };

        if (draft.mode === 'create') {
            form.post(store(storage).url, done);
        } else {
            form.patch(update(storage).url, done);
        }
    };

    return (
        <Modal
            persistent
            open
            onOpenChange={(next) => !next && onClose()}
            title={draft.mode === 'rename' ? 'Ubah Nama' : 'Folder Baru'}
            description={draft.mode === 'rename' ? undefined : 'Folder dibuat di lokasi yang sedang dibuka.'}
        >
            <form
                noValidate
                onSubmit={(e) => {
                    e.preventDefault();
                    submit();
                }}
            >
                <DialogBody>
                    <Field label="Nama" required error={form.errors.name}>
                        <Input
                            autoFocus
                            maxLength={200}
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
