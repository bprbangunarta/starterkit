import { KeyRound, Pencil, RotateCcw, Trash2 } from 'lucide-react';
import type { UserRow } from '@/components/users/types';
import type { Column } from '@/components/ui/data-table';
import { Badge } from '@/components/ui/misc';
import { RowActions } from '@/components/ui/row-actions';
import type { RowAction } from '@/components/ui/row-actions';
import { formatDate } from '@/lib/format';

type Actions = { onEdit: (user: UserRow) => void; onReset: (user: UserRow) => void; onDelete: (user: UserRow) => void; onRestore: (user: UserRow) => void };

/** A deleted user can only be restored; anyone else can be changed, given a new password, or deleted, none of which applies to one's own account except the change. */
function userActions(user: UserRow, { onEdit, onReset, onDelete, onRestore }: Actions): RowAction[] {
    if (user.deleted_at) {
        return [{ label: 'Pulihkan', icon: <RotateCcw />, onSelect: () => onRestore(user) }];
    }

    return [
        { label: 'Ubah', icon: <Pencil />, onSelect: () => onEdit(user) },
        {
            label: 'Reset',
            icon: <KeyRound />,
            disabled: user.is_self,
            reason: 'Gunakan halaman Profil untuk akun Anda sendiri',
            onSelect: () => onReset(user),
        },
        {
            label: 'Hapus',
            icon: <Trash2 />,
            danger: true,
            disabled: user.is_self,
            reason: 'Akun Anda sendiri tidak dapat dihapus',
            onSelect: () => onDelete(user),
        },
    ];
}

export function userColumns(actions: Actions): Column<UserRow>[] {
    return [
        { key: 'name', header: 'Nama', sort: 'name', className: 'font-medium', cell: (user) => user.name },
        { key: 'username', header: 'Username', sort: 'username', cell: (user) => user.username },
        { key: 'email', header: 'Email', sort: 'email', cell: (user) => user.email },
        {
            key: 'role',
            header: 'Jabatan',
            sort: 'role',
            cell: (user) =>
                user.roles.length === 0 ? (
                    '–'
                ) : (
                    <span className="flex gap-1">
                        {user.roles.map((role) => (
                            <Badge key={role}>{role}</Badge>
                        ))}
                    </span>
                ),
        },
        { key: 'office', header: 'Kantor', sort: 'office', cell: (user) => user.office?.name ?? '–' },
        {
            key: 'status',
            header: 'Status',
            sort: 'status',
            cell: (user) => (user.deleted_at ? <Badge tone="danger">Dihapus</Badge> : <Badge tone="success">Aktif</Badge>),
        },
        {
            key: 'created',
            header: 'Dibuat',
            sort: 'created',
            className: 'whitespace-nowrap',
            cell: (user) => (user.created_at ? formatDate(user.created_at) : '–'),
        },
        {
            key: 'actions',
            header: 'Aksi',
            srOnly: true,
            narrow: true,
            align: 'right',
            cell: (user) => <RowActions title={user.name} actions={userActions(user, actions)} />,
        },
    ];
}
