import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import type { UserRow } from '@/components/users/types';
import { ConfirmDialog } from '@/components/ui/dialog';
import { destroy, passwordReset, restore } from '@/routes/users';

type Props = {
    deleting: UserRow | null;
    resetting: UserRow | null;
    restoring: UserRow | null;
    onClose: () => void;
};

/** The confirmations of a row action: delete the user, give the account the default password again, or restore a deleted user. */
export function UserConfirmations({ deleting, resetting, restoring, onClose }: Props) {
    const [busy, setBusy] = useState(false);

    const options = (success: string, failure: string) => ({
        preserveScroll: true,
        onStart: () => setBusy(true),
        onFinish: () => setBusy(false),
        onSuccess: () => toast.success(success),
        onError: (errors: Record<string, string>) => toast.error(errors.user ?? failure),
    });

    return (
        <>
            <ConfirmDialog
                open={resetting !== null}
                onOpenChange={(open) => !open && onClose()}
                title="Atur Ulang Kata Sandi?"
                description={
                    resetting
                        ? `${resetting.name} akan keluar dari semua perangkat, kata sandinya kembali ke kata sandi bawaan, dan harus diganti saat masuk berikutnya.`
                        : ''
                }
                confirmLabel="Atur Ulang"
                loading={busy}
                onConfirm={() => {
                    if (resetting) {
                        router.post(
                            passwordReset(resetting.id).url,
                            {},
                            options(`Kata sandi ${resetting.name} dikembalikan ke kata sandi bawaan.`, 'Kata sandi tidak dapat diatur ulang.'),
                        );
                        onClose();
                    }
                }}
            />
            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && onClose()}
                title="Hapus Pengguna?"
                description={deleting ? `${deleting.name} tidak akan bisa masuk lagi. Riwayatnya tetap ada di audit logs.` : ''}
                loading={busy}
                onConfirm={() => {
                    if (deleting) {
                        router.delete(destroy(deleting.id).url, options('Pengguna dihapus.', 'Pengguna tidak dapat dihapus.'));
                        onClose();
                    }
                }}
            />
            <ConfirmDialog
                open={restoring !== null}
                onOpenChange={(open) => !open && onClose()}
                title="Pulihkan Pengguna?"
                description={restoring ? `${restoring.name} dapat masuk lagi dengan kata sandi dan jabatan yang dimilikinya sebelum dihapus.` : ''}
                confirmLabel="Pulihkan"
                loading={busy}
                onConfirm={() => {
                    if (restoring) {
                        router.post(restore(restoring.id).url, {}, options('Pengguna dipulihkan.', 'Pengguna tidak dapat dipulihkan.'));
                        onClose();
                    }
                }}
            />
        </>
    );
}
