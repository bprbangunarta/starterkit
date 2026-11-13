import { Head, router } from '@inertiajs/react';
import { Plus, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { useReauth } from '@/components/reauth/reauth-provider';
import { roleColumns } from '@/components/roles/role-columns';
import { canReset } from '@/components/roles/role-query';
import { RoleFormDialog } from '@/components/roles/role-form-dialog';
import type { RoleFilters, RolePage, RoleRow, RoleSortKey } from '@/components/roles/types';
import { useRoleNavigation } from '@/components/roles/use-role-navigation';
import { ListSearchToolbar } from '@/components/list-search-toolbar';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { ConfirmDialog } from '@/components/ui/dialog';
import { PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';
import { PER_PAGE_OPTIONS } from '@/lib/list';
import { destroy } from '@/routes/roles';

/** `false` is closed, `null` adds a role, and a role is renamed. */
type Editing = false | null | RoleRow;

type Props = { roles: RolePage; filters: RoleFilters };

export default function Roles({ roles, filters }: Props) {
    const [editing, setEditing] = useState<Editing>(false);
    const [deleting, setDeleting] = useState<RoleRow | null>(null);
    const [removing, setRemoving] = useState(false);
    const { loading, visit } = useRoleNavigation(filters);
    const { confirmed } = useReauth();

    const remove = (role: RoleRow) =>
        router.delete(destroy(role.id).url, {
            preserveScroll: true,
            onStart: () => setRemoving(true),
            onFinish: () => setRemoving(false),
            onSuccess: () => {
                toast.success('Jabatan dihapus.');
                setDeleting(null);
            },
            onError: (errors) => {
                toast.error(errors.role ?? 'Jabatan tidak dapat dihapus.');
                setDeleting(null);
            },
        });

    return (
        <>
            <Head title="Data Jabatan" />
            <PageHeader
                title="Data Jabatan"
                description="Jabatan yang dapat diberikan kepada pengguna"
                actions={
                    <Button onClick={() => confirmed(() => setEditing(null))}>
                        <Plus /> Tambah
                    </Button>
                }
            />
            <DataTable
                rows={roles.data}
                rowKey={(role) => role.id}
                columns={roleColumns({ onEdit: (role) => confirmed(() => setEditing(role)), onDelete: (role) => confirmed(() => setDeleting(role)) })}
                loading={loading}
                toolbar={
                    <ListSearchToolbar
                        filters={filters}
                        onChange={visit}
                        placeholder="Cari nama jabatan…"
                        label="Cari jabatan"
                        canReset={canReset}
                        defaultSort="name"
                    />
                }
                sort={{
                    sort: filters.sort,
                    direction: filters.direction,
                    onSort: (column) =>
                        visit({ sort: column as RoleSortKey, direction: filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc' }),
                }}
                pagination={{
                    meta: roles.meta,
                    perPage: filters.per_page,
                    options: PER_PAGE_OPTIONS,
                    onPage: (page) => visit({}, page),
                    onPerPage: (per_page) => visit({ per_page }),
                }}
                empty={{ icon: <ShieldCheck />, title: filters.search ? 'Tidak ada jabatan yang cocok dengan pencarian' : 'Belum ada jabatan' }}
            />
            {editing !== false && <RoleFormDialog key={editing?.id ?? 'new'} role={editing} onClose={() => setEditing(false)} />}
            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title="Hapus Jabatan?"
                description={deleting ? `Jabatan ${deleting.name} akan dihapus. Jabatan yang masih dipakai pengguna tidak bisa dihapus.` : ''}
                loading={removing}
                onConfirm={() => deleting && remove(deleting)}
            />
        </>
    );
}

Roles.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
