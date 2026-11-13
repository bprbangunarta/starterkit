import { Head } from '@inertiajs/react';
import { Plus, Users as UsersIcon } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import type { OfficeOption } from '@/components/offices/types';
import { useReauth } from '@/components/reauth/reauth-provider';
import { UserConfirmations } from '@/components/users/user-confirmations';
import { userColumns } from '@/components/users/user-columns';
import { UserFormDialog } from '@/components/users/user-form-dialog';
import { hasActiveFilters } from '@/components/users/user-query';
import { UserToolbar } from '@/components/users/user-toolbar';
import type { UserFilters, UserPage, UserRow, UserSortKey } from '@/components/users/types';
import { useUserNavigation } from '@/components/users/use-user-navigation';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { PageHeader } from '@/components/ui/misc';
import { PER_PAGE_OPTIONS } from '@/lib/list';
import AppLayout from '@/layouts/app-layout';

type Props = { users: UserPage; filters: UserFilters; roles: string[]; offices: OfficeOption[] };

/** `false` is closed, `null` adds a user, and a user is changed. */
type Editing = false | null | UserRow;

export default function Users({ users, filters, roles, offices }: Props) {
    const [editing, setEditing] = useState<Editing>(false);
    const [deleting, setDeleting] = useState<UserRow | null>(null);
    const [resetting, setResetting] = useState<UserRow | null>(null);
    const [restoring, setRestoring] = useState<UserRow | null>(null);
    const { loading, visit } = useUserNavigation(filters);
    const { confirmed } = useReauth();

    return (
        <>
            <Head title="Kelola Pengguna" />
            <PageHeader
                title="Kelola Pengguna"
                description="Akun yang dapat masuk ke aplikasi"
                actions={
                    <Button onClick={() => confirmed(() => setEditing(null))}>
                        <Plus /> Tambah
                    </Button>
                }
            />
            <DataTable
                rows={users.data}
                rowKey={(user) => user.id}
                columns={userColumns({
                    onEdit: (user) => confirmed(() => setEditing(user)),
                    onReset: (user) => confirmed(() => setResetting(user)),
                    onDelete: (user) => confirmed(() => setDeleting(user)),
                    onRestore: (user) => confirmed(() => setRestoring(user)),
                })}
                loading={loading}
                toolbar={<UserToolbar filters={filters} roles={roles} offices={offices} onChange={visit} />}
                sort={{
                    sort: filters.sort,
                    direction: filters.direction,
                    onSort: (column) =>
                        visit({ sort: column as UserSortKey, direction: filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc' }),
                }}
                pagination={{
                    meta: users.meta,
                    perPage: filters.per_page,
                    options: PER_PAGE_OPTIONS,
                    onPage: (page) => visit({}, page),
                    onPerPage: (per_page) => visit({ per_page }),
                }}
                empty={{ icon: <UsersIcon />, title: hasActiveFilters(filters) ? 'Tidak ada pengguna yang cocok dengan filter' : 'Belum ada pengguna' }}
            />
            {editing !== false && (
                <UserFormDialog key={editing?.id ?? 'new'} user={editing} roles={roles} offices={offices} onClose={() => setEditing(false)} />
            )}
            <UserConfirmations
                deleting={deleting}
                resetting={resetting}
                restoring={restoring}
                onClose={() => (setDeleting(null), setResetting(null), setRestoring(null))}
            />
        </>
    );
}

Users.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
