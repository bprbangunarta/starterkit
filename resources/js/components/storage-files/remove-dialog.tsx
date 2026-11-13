import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import type { StorageItem } from '@/components/storage-files/types';
import { ConfirmDialog } from '@/components/ui/dialog';
import { destroy } from '@/routes/storage/files';

type Props = { storage: string; item: StorageItem; onClose: () => void };

/** Deleting a file, or an empty folder; the server says so when the folder still has things in it. */
export function RemoveDialog({ storage, item, onClose }: Props) {
    const [busy, setBusy] = useState(false);

    return (
        <ConfirmDialog
            open
            onOpenChange={(next) => !next && onClose()}
            title={item.type === 'folder' ? 'Hapus Folder?' : 'Hapus Berkas?'}
            description={
                item.type === 'folder'
                    ? `Folder "${item.name}" akan dihapus bila kosong.`
                    : `Berkas "${item.name}" akan dihapus dari penyimpanan dan tidak bisa dikembalikan.`
            }
            loading={busy}
            onConfirm={() =>
                router.delete(destroy(storage).url, {
                    data: { path: item.path },
                    preserveScroll: true,
                    onStart: () => setBusy(true),
                    onFinish: () => {
                        setBusy(false);
                        onClose();
                    },
                    onSuccess: () => toast.success(item.type === 'folder' ? 'Folder dihapus' : 'Berkas dihapus'),
                    onError: (errors) => toast.error(errors.path ?? errors.storage ?? 'Gagal menghapus.'),
                })
            }
        />
    );
}
