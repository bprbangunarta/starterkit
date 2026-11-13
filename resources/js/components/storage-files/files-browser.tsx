import { Folder, LayoutGrid, List } from 'lucide-react';
import type { ReactNode } from 'react';
import { ListSearchToolbar } from '@/components/list-search-toolbar';
import { fileColumns } from '@/components/storage-files/file-columns';
import { FileGrid } from '@/components/storage-files/file-grid';
import { canReset } from '@/components/storage-files/files-query';
import type { FilesFilters, FilesPage, StorageItem } from '@/components/storage-files/types';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Card, EmptyState } from '@/components/ui/misc';
import { Pagination } from '@/components/ui/pagination';
import { PER_PAGE_OPTIONS } from '@/lib/list';

export type FilesView = 'grid' | 'list';

type Props = {
    files: FilesPage;
    filters: FilesFilters;
    loading: boolean;
    view: FilesView;
    onView: (view: FilesView) => void;
    visit: (patch: Partial<FilesFilters>, page?: number) => void;
    actions: (item: StorageItem) => ReactNode;
    onOpen: (item: StorageItem) => void;
};

/**
 * The contents of a folder as a grid of cards or a table, under one toolbar with the search and the switch between the two. The server
 * searches, sorts, and pages both views; only the table has sort headers.
 */
export function FilesBrowser({ files, filters, loading, view, onView, visit, actions, onOpen }: Props) {
    const empty = {
        icon: <Folder />,
        title: filters.search ? 'Tidak ada hasil' : 'Folder ini kosong',
        description: filters.search ? 'Coba kata kunci lain.' : 'Unggah berkas atau buat folder baru.',
    };

    const toolbar = (
        <ListSearchToolbar filters={filters} onChange={visit} placeholder="Cari nama…" label="Cari berkas atau folder" canReset={canReset} defaultSort="name">
            <Button variant={view === 'grid' ? 'primary' : 'outline'} aria-pressed={view === 'grid'} onClick={() => onView('grid')}>
                <LayoutGrid /> Kotak
            </Button>
            <Button variant={view === 'list' ? 'primary' : 'outline'} aria-pressed={view === 'list'} onClick={() => onView('list')}>
                <List /> Daftar
            </Button>
        </ListSearchToolbar>
    );

    const pagination = {
        meta: files.meta,
        perPage: filters.per_page,
        options: PER_PAGE_OPTIONS,
        onPage: (page: number) => visit({}, page),
        onPerPage: (per_page: number) => visit({ per_page }),
    };

    if (view === 'list') {
        return (
            <DataTable
                columns={fileColumns(actions)}
                rows={files.data}
                rowKey={(item) => item.path}
                loading={loading}
                toolbar={toolbar}
                sort={{
                    sort: filters.sort,
                    direction: filters.direction,
                    onSort: (column) => visit({ sort: column, direction: filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc' }),
                }}
                pagination={pagination}
                onRowClick={onOpen}
                empty={empty}
            />
        );
    }

    return (
        <Card>
            {toolbar}
            {files.data.length === 0 ? <EmptyState {...empty} /> : <FileGrid items={files.data} actions={actions} onOpen={onOpen} />}
            <Pagination
                meta={pagination.meta}
                perPage={pagination.perPage}
                perPageOptions={pagination.options}
                onPage={pagination.onPage}
                onPerPage={pagination.onPerPage}
            />
        </Card>
    );
}
