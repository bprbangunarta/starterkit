import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import type { OfficeOption } from '@/components/offices/types';
import type { UserRow } from '@/components/users/types';
import { UserFields } from '@/components/users/user-fields';
import type { UserData } from '@/components/users/user-fields';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { store, update } from '@/routes/users';

type Props = { user: UserRow | null; roles: string[]; offices: OfficeOption[]; onClose: () => void };

/** Adds a user, or changes the one given. A new account gets the default password; resetting a password is its own action. */
export function UserFormDialog({ user, roles, offices, onClose }: Props) {
    const form = useForm<UserData>({
        name: user?.name ?? '',
        username: user?.username ?? '',
        email: user?.email ?? '',
        office_id: user?.office ? String(user.office.id) : null,
        roles: user?.roles ?? [],
        reason: '',
    });

    const submit = () => {
        const done = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(user ? 'Pengguna diubah.' : 'Pengguna ditambahkan.');
                onClose();
            },
        };

        if (user) {
            form.put(update(user.id).url, done);
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
            title={user ? 'Ubah Pengguna' : 'Tambah Pengguna'}
            description={user ? 'Ubah data akun lalu simpan.' : 'Kata sandi awal memakai kata sandi bawaan dan wajib diganti saat masuk pertama.'}
        >
            <form
                noValidate
                onSubmit={(event) => {
                    event.preventDefault();
                    submit();
                }}
            >
                <DialogBody className="grid gap-3 sm:grid-cols-2">
                    <UserFields form={form} roles={roles} offices={offices} original={user?.roles ?? []} />
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
