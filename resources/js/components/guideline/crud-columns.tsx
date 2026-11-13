import { Pencil, Trash2 } from 'lucide-react';
import type { Row } from '@/components/guideline/crud-data';
import type { Column } from '@/components/ui/data-table';
import { Badge } from '@/components/ui/misc';
import { RowActions } from '@/components/ui/row-actions';
import { rupiah } from '@/lib/format';

export function rowColumns(actions: { onEdit: (row: Row) => void; onRemove: (row: Row) => void }): Column<Row>[] {
    return [
        { key: 'name', header: 'Nama', className: 'font-medium', cell: (r) => r.name, sort: 'name' },
        { key: 'status', header: 'Status', cell: (r) => <Badge tone={r.status}>{r.status}</Badge>, sort: 'status' },
        { key: 'amount', header: 'Nilai', align: 'right', className: 'tabular-nums', cell: (r) => rupiah(r.amount), sort: 'amount' },
        {
            key: 'actions',
            header: 'Aksi',
            srOnly: true,
            narrow: true,
            align: 'right',
            cell: (r) => (
                <RowActions
                    title={r.name}
                    heading={r.name}
                    actions={[
                        { label: 'Ubah', icon: <Pencil />, onSelect: () => actions.onEdit(r) },
                        { label: 'Hapus', icon: <Trash2 />, danger: true, onSelect: () => actions.onRemove(r) },
                    ]}
                />
            ),
        },
    ];
}
