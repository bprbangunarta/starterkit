import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import { OfficeFields } from '@/components/offices/office-fields';
import type { OfficeData } from '@/components/offices/office-fields';
import type { OfficeRow } from '@/components/offices/types';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { store, update } from '@/routes/offices';

type Props = { office: OfficeRow | null; onClose: () => void };

/** Adds an office, or changes the one given. */
export function OfficeFormDialog({ office, onClose }: Props) {
    const form = useForm<OfficeData>({
        name: office?.name ?? '',
        address: office?.address ?? '',
        latitude: office?.latitude ?? '',
        longitude: office?.longitude ?? '',
    });

    const submit = () => {
        const done = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(office ? 'Kantor diubah.' : 'Kantor ditambahkan.');
                onClose();
            },
        };

        if (office) {
            form.put(update(office.id).url, done);
        } else {
            form.post(store().url, done);
        }
    };

    return (
        <Modal
            persistent
            wide
            open
            onOpenChange={(open) => !open && onClose()}
            title={office ? 'Ubah Kantor' : 'Tambah Kantor'}
            description="Isi lalu simpan. Galat validasi tampil di bawah kolom."
        >
            <form
                noValidate
                onSubmit={(event) => {
                    event.preventDefault();
                    submit();
                }}
            >
                <DialogBody className="grid gap-3 sm:grid-cols-2">
                    <OfficeFields form={form} />
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
