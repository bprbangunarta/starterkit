import { X } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { FilterBar, SearchInput } from '@/components/ui/filter-bar';
import { useDebouncedSearch } from '@/hooks/use-debounced-search';
import type { ListFilters } from '@/lib/list';

type Props<Filters extends ListFilters> = {
    filters: Filters;
    onChange: (patch: Partial<Filters>) => void;
    placeholder: string;
    label: string;
    /** Whether the reset button shows: the search text and the order are the only things a plain list can change. */
    canReset: (filters: Filters, search: string) => boolean;
    defaultSort: Filters['sort'];
    /** Extra controls of the list, such as a switch between views, shown before the reset button. */
    children?: ReactNode;
};

/** The toolbar of a list that only searches and sorts: the search box at the left and, once something changed, the reset button. */
export function ListSearchToolbar<Filters extends ListFilters>({ filters, onChange, placeholder, label, canReset, defaultSort, children }: Props<Filters>) {
    const [search, setSearch] = useDebouncedSearch(filters.search, (value) => onChange({ search: value } as Partial<Filters>));

    const reset = () => {
        setSearch('');
        onChange({ search: null, sort: defaultSort, direction: 'asc' } as Partial<Filters>);
    };

    return (
        <FilterBar search={<SearchInput value={search} onChange={setSearch} placeholder={placeholder} label={label} />}>
            {children}
            {canReset(filters, search) && (
                <Button variant="ghost" size="sm" onClick={reset}>
                    <X /> Atur ulang
                </Button>
            )}
        </FilterBar>
    );
}
