import { Head, router } from '@inertiajs/react';
import { Building2, Plus } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { ListSearchToolbar } from '@/components/list-search-toolbar';
import { officeColumns } from '@/components/offices/office-columns';
import { OfficeFormDialog } from '@/components/offices/office-form-dialog';
import { canReset } from '@/components/offices/office-query';
import type { OfficeFilters, OfficePage, OfficeRow, OfficeSortKey } from '@/components/offices/types';
import { useOfficeNavigation } from '@/components/offices/use-office-navigation';
import { useReauth } from '@/components/reauth/reauth-provider';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { ConfirmDialog } from '@/components/ui/dialog';
import { PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';
import { PER_PAGE_OPTIONS } from '@/lib/list';
import { destroy } from '@/routes/offices';

type Props = { offices: OfficePage; filters: OfficeFilters };

/** `false` is closed, `null` adds an office, and an office is changed. */
type Editing = false | null | OfficeRow;

export default function Offices({ offices, filters }: Props) {
    const [editing, setEditing] = useState<Editing>(false);
    const [deleting, setDeleting] = useState<OfficeRow | null>(null);
    const [removing, setRemoving] = useState(false);
    const { loading, visit } = useOfficeNavigation(filters);
    const { confirmed } = useReauth();

    const remove = (office: OfficeRow) =>
        router.delete(destroy(office.id).url, {
            preserveScroll: true,
            onStart: () => setRemoving(true),
            onFinish: () => {
                setRemoving(false);
                setDeleting(null);
            },
            onSuccess: () => toast.success('Kantor dihapus.'),
            onError: (errors) => toast.error(errors.office ?? 'Kantor tidak dapat dihapus.'),
        });

    return (
        <>
            <Head title="Data Kantor" />
            <PageHeader
                title="Data Kantor"
                description="Kantor beserta alamat dan koordinatnya"
                actions={
                    <Button onClick={() => confirmed(() => setEditing(null))}>
                        <Plus /> Tambah
                    </Button>
                }
            />
            <DataTable
                rows={offices.data}
                rowKey={(office) => office.id}
                columns={officeColumns({ onEdit: (office) => confirmed(() => setEditing(office)), onDelete: (office) => confirmed(() => setDeleting(office)) })}
                loading={loading}
                toolbar={
                    <ListSearchToolbar
                        filters={filters}
                        onChange={visit}
                        placeholder="Cari nama atau alamat…"
                        label="Cari kantor"
                        canReset={canReset}
                        defaultSort="name"
                    />
                }
                sort={{
                    sort: filters.sort,
                    direction: filters.direction,
                    onSort: (column) =>
                        visit({ sort: column as OfficeSortKey, direction: filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc' }),
                }}
                pagination={{
                    meta: offices.meta,
                    perPage: filters.per_page,
                    options: PER_PAGE_OPTIONS,
                    onPage: (page) => visit({}, page),
                    onPerPage: (per_page) => visit({ per_page }),
                }}
                empty={{ icon: <Building2 />, title: filters.search ? 'Tidak ada kantor yang cocok dengan pencarian' : 'Belum ada kantor' }}
            />
            {editing !== false && <OfficeFormDialog key={editing?.id ?? 'new'} office={editing} onClose={() => setEditing(false)} />}
            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title="Hapus Kantor?"
                description={deleting ? `Kantor ${deleting.name} akan dihapus. Kantor yang masih dipakai pengguna tidak bisa dihapus.` : ''}
                loading={removing}
                onConfirm={() => deleting && remove(deleting)}
            />
        </>
    );
}

Offices.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
