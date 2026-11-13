import { Pencil, Trash2 } from 'lucide-react';
import type { RoleRow } from '@/components/roles/types';
import type { Column } from '@/components/ui/data-table';
import { Badge } from '@/components/ui/misc';
import { RowActions } from '@/components/ui/row-actions';
import { formatDate, formatNumber } from '@/lib/format';

type Actions = { onEdit: (role: RoleRow) => void; onDelete: (role: RoleRow) => void };

export function roleColumns({ onEdit, onDelete }: Actions): Column<RoleRow>[] {
    return [
        {
            key: 'name',
            header: 'Nama',
            sort: 'name',
            className: 'font-medium',
            cell: (role) => (
                <span className="flex items-center gap-2">
                    {role.name}
                    {role.is_base && <Badge tone="info">Dasar</Badge>}
                </span>
            ),
        },
        { key: 'users', header: 'Pengguna', sort: 'users', align: 'right', cell: (role) => formatNumber(role.users_count) },
        {
            key: 'created',
            header: 'Dibuat',
            sort: 'created',
            className: 'whitespace-nowrap',
            cell: (role) => (role.created_at ? formatDate(role.created_at) : '–'),
        },
        {
            key: 'actions',
            header: 'Aksi',
            srOnly: true,
            narrow: true,
            align: 'right',
            cell: (role) => (
                <RowActions
                    title={role.name}
                    actions={[
                        {
                            label: 'Ubah',
                            icon: <Pencil />,
                            disabled: role.is_base,
                            reason: 'Jabatan dasar tidak dapat diubah namanya',
                            onSelect: () => onEdit(role),
                        },
                        {
                            label: 'Hapus',
                            icon: <Trash2 />,
                            danger: true,
                            disabled: role.is_base,
                            reason: 'Jabatan dasar tidak dapat dihapus',
                            onSelect: () => onDelete(role),
                        },
                    ]}
                />
            ),
        },
    ];
}
