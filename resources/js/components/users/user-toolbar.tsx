import { X } from 'lucide-react';
import { canReset, NO_OFFICE, STATUS_OPTIONS, toStatus } from '@/components/users/user-query';
import type { OfficeOption } from '@/components/offices/types';
import type { UserFilters } from '@/components/users/types';
import { Button } from '@/components/ui/button';
import { Combobox } from '@/components/ui/combobox';
import { FilterBar, SearchInput } from '@/components/ui/filter-bar';
import { useDebouncedSearch } from '@/hooks/use-debounced-search';

type Props = {
    filters: UserFilters;
    roles: string[];
    offices: OfficeOption[];
    onChange: (patch: Partial<UserFilters>) => void;
};

export function UserToolbar({ filters, roles, offices, onChange }: Props) {
    const [search, setSearch] = useDebouncedSearch(filters.search, (value) => onChange({ search: value }));

    const reset = () => {
        setSearch('');
        onChange({ search: null, role: null, office: null, status: 'active', sort: 'name', direction: 'asc' });
    };

    return (
        <FilterBar search={<SearchInput value={search} onChange={setSearch} placeholder="Cari nama, username, email…" label="Cari pengguna" />}>
            <Combobox
                className="w-full sm:w-44"
                clearable
                placeholder="Jabatan"
                aria-label="Filter jabatan"
                options={roles.map((role) => ({ value: role, label: role }))}
                value={filters.role}
                onChange={(role) => onChange({ role })}
            />
            <Combobox
                className="w-full sm:w-44"
                clearable
                placeholder="Kantor"
                aria-label="Filter kantor"
                options={[{ value: NO_OFFICE, label: 'Tanpa kantor' }, ...offices.map((office) => ({ value: String(office.id), label: office.name }))]}
                value={filters.office}
                onChange={(office) => onChange({ office })}
            />
            <Combobox
                className="w-full sm:w-36"
                placeholder="Status"
                aria-label="Filter status"
                options={STATUS_OPTIONS}
                value={filters.status}
                onChange={(status) => onChange({ status: toStatus(status) })}
            />
            {canReset(filters, search) && (
                <Button variant="ghost" size="sm" onClick={reset}>
                    <X /> Atur ulang
                </Button>
            )}
        </FilterBar>
    );
}
