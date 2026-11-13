import { Pencil, Trash2 } from 'lucide-react';
import type { OfficeRow } from '@/components/offices/types';
import type { Column } from '@/components/ui/data-table';
import { RowActions } from '@/components/ui/row-actions';
import { formatDate, formatNumber } from '@/lib/format';

type Actions = { onEdit: (office: OfficeRow) => void; onDelete: (office: OfficeRow) => void };

export function officeColumns({ onEdit, onDelete }: Actions): Column<OfficeRow>[] {
    return [
        { key: 'name', header: 'Nama', sort: 'name', className: 'font-medium', cell: (office) => office.name },
        { key: 'address', header: 'Alamat', sort: 'address', cell: (office) => office.address ?? '–' },
        { key: 'latitude', header: 'Latitude', sort: 'latitude', align: 'right', className: 'tabular-nums', cell: (office) => office.latitude ?? '–' },
        { key: 'longitude', header: 'Longitude', sort: 'longitude', align: 'right', className: 'tabular-nums', cell: (office) => office.longitude ?? '–' },
        { key: 'users', header: 'Pengguna', sort: 'users', align: 'right', cell: (office) => formatNumber(office.users_count) },
        {
            key: 'created',
            header: 'Dibuat',
            sort: 'created',
            className: 'whitespace-nowrap',
            cell: (office) => (office.created_at ? formatDate(office.created_at) : '–'),
        },
        {
            key: 'actions',
            header: 'Aksi',
            srOnly: true,
            narrow: true,
            align: 'right',
            cell: (office) => (
                <RowActions
                    title={office.name}
                    actions={[
                        { label: 'Ubah', icon: <Pencil />, onSelect: () => onEdit(office) },
                        {
                            label: 'Hapus',
                            icon: <Trash2 />,
                            danger: true,
                            disabled: office.users_count > 0,
                            reason: 'Kantor yang masih dipakai pengguna tidak dapat dihapus',
                            onSelect: () => onDelete(office),
                        },
                    ]}
                />
            ),
        },
    ];
}
