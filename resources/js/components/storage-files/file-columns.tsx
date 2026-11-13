import type { ReactNode } from 'react';
import { kindOf } from '@/components/storage-files/items';
import type { StorageItem } from '@/components/storage-files/types';
import type { Column } from '@/components/ui/data-table';
import { formatBytes, formatDate } from '@/lib/format';
import { cn } from '@/lib/utils';

/** The columns of the list; `actions` draws the menu of a row. The sort keys are the ones the server knows. */
export function fileColumns(actions: (item: StorageItem) => ReactNode): Column<StorageItem>[] {
    return [
        {
            key: 'name',
            header: 'Nama',
            sort: 'name',
            className: 'font-medium',
            cell: (item) => {
                const { icon: Icon } = kindOf(item);

                return (
                    <span className="flex items-center gap-2">
                        <Icon className={cn('size-4 shrink-0', item.type === 'folder' ? 'text-primary' : 'text-muted')} />
                        {item.name}
                    </span>
                );
            },
        },
        { key: 'kind', header: 'Jenis', sort: 'kind', cell: (item) => kindOf(item).label },
        {
            key: 'size',
            header: 'Ukuran',
            sort: 'size',
            align: 'right',
            className: 'tabular-nums',
            cell: (item) => (item.size === null ? '–' : formatBytes(item.size)),
        },
        {
            key: 'modified',
            header: 'Diubah',
            sort: 'modified',
            className: 'whitespace-nowrap',
            cell: (item) => (item.modified ? formatDate(item.modified) : '–'),
        },
        { key: 'actions', header: 'Aksi', srOnly: true, narrow: true, align: 'right', cell: actions },
    ];
}
