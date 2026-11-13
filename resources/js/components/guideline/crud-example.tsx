import { Inbox, Plus } from 'lucide-react';
import { rowColumns } from '@/components/guideline/crud-columns';
import { EMPTY_DRAFT, toDraft } from '@/components/guideline/crud-data';
import { RowFormDialog } from '@/components/guideline/crud-form-dialog';
import { useCrudState } from '@/components/guideline/crud-state';
import { TONES } from '@/components/guideline/guideline-data';
import { Button } from '@/components/ui/button';
import { Combobox } from '@/components/ui/combobox';
import { DataTable } from '@/components/ui/data-table';
import { ConfirmDialog } from '@/components/ui/dialog';
import { FilterBar, SearchInput } from '@/components/ui/filter-bar';

/** The list example: a table with search, filter, sort, paging, and add, edit, and delete dialogs, on static rows in memory. */
export function CrudExample() {
    const { view, filters, form, removal } = useCrudState();
    const columns = rowColumns({ onEdit: (row) => form.openDraft(toDraft(row)), onRemove: removal.setRemoving });

    return (
        <>
            <div>
                <div className="mb-2 flex items-center justify-between gap-2">
                    <div>
                        <h2 className="text-sm font-semibold">Tabel (DataTable) dan contoh CRUD</h2>
                        <p className="text-xs text-muted">
                            Data statis di memori: Tambah, Ubah, dan Hapus membuka dialog lalu mengubah tabel. Muat ulang halaman untuk mengembalikan.
                        </p>
                    </div>
                    <Button size="sm" onClick={() => form.openDraft(EMPTY_DRAFT)}>
                        <Plus />
                        Tambah
                    </Button>
                </div>
                <DataTable
                    rows={view.visible}
                    rowKey={(r) => r.id}
                    columns={columns}
                    onRowClick={(r) => form.openDraft(toDraft(r))}
                    toolbar={
                        <FilterBar search={<SearchInput value={filters.search} onChange={filters.setSearch} placeholder="Cari…" label="Cari contoh" />}>
                            <Combobox
                                className="w-full sm:w-36"
                                clearable
                                searchable={false}
                                placeholder="Status"
                                options={TONES.map((t) => ({ value: t, label: t }))}
                                value={filters.status}
                                onChange={filters.setStatus}
                            />
                        </FilterBar>
                    }
                    sort={{
                        ...filters.order,
                        onSort: (column) => filters.setOrder((o) => ({ sort: column, direction: o.sort === column && o.direction === 'asc' ? 'desc' : 'asc' })),
                    }}
                    pagination={{
                        meta: {
                            current_page: view.current,
                            last_page: view.lastPage,
                            from: view.matching.length === 0 ? 0 : (view.current - 1) * filters.perPage + 1,
                            to: Math.min(view.matching.length, view.current * filters.perPage),
                            total: view.matching.length,
                        },
                        perPage: filters.perPage,
                        options: [5, 10],
                        onPage: filters.setPage,
                        onPerPage: (value) => {
                            filters.setPerPage(value);
                            filters.setPage(1);
                        },
                    }}
                    empty={{ icon: <Inbox />, title: 'Tidak ada data' }}
                />
            </div>
            <RowFormDialog draft={form.draft} nameError={form.nameError} onChange={form.setDraft} onSubmit={form.save} onClose={() => form.setDraft(null)} />
            <ConfirmDialog
                open={removal.removing !== null}
                onOpenChange={(next) => !next && removal.setRemoving(null)}
                title="Hapus Data?"
                description={`"${removal.removing?.name ?? ''}" akan dihapus dari contoh ini.`}
                onConfirm={removal.confirmRemove}
            />
        </>
    );
}
