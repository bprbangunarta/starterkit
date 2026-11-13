import { X } from 'lucide-react';
import { canReset } from '@/components/audit/audit-query';
import type { AuditFilters, AuditOption } from '@/components/audit/types';
import { Button } from '@/components/ui/button';
import { Combobox } from '@/components/ui/combobox';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { FilterBar, SearchInput } from '@/components/ui/filter-bar';
import { useDebouncedSearch } from '@/hooks/use-debounced-search';

type Props = {
    filters: AuditFilters;
    modules: string[];
    results: AuditOption[];
    onChange: (patch: Partial<AuditFilters>) => void;
};

/** The earliest day the audit log can hold entries for. */
const MIN_DATE = new Date(2020, 0, 1);

export function AuditToolbar({ filters, modules, results, onChange }: Props) {
    const [search, setSearch] = useDebouncedSearch(filters.search, (value) => onChange({ search: value }));

    const reset = () => {
        setSearch('');
        // Resetting also returns to the default order (newest first).
        onChange({ search: null, module: null, result: null, from: null, to: null, sort: 'time', direction: 'desc' });
    };

    return (
        <FilterBar search={<SearchInput value={search} onChange={setSearch} placeholder="Cari pengguna, kejadian, modul, IP…" label="Cari audit log" />}>
            <div className="w-full sm:w-60">
                <DateRangePicker
                    value={{ from: filters.from ?? '', to: filters.to ?? '' }}
                    min={MIN_DATE}
                    clearable
                    placeholder="Rentang tanggal"
                    aria-label="Rentang tanggal"
                    onChange={(span) => onChange({ from: span.from || null, to: span.to || null })}
                />
            </div>
            <Combobox
                className="w-full sm:w-40"
                clearable
                placeholder="Modul"
                aria-label="Filter modul"
                options={modules.map((module) => ({ value: module, label: module }))}
                value={filters.module}
                onChange={(module) => onChange({ module })}
            />
            <Combobox
                className="w-full sm:w-36"
                clearable
                searchable={false}
                placeholder="Hasil"
                aria-label="Filter hasil"
                options={results}
                value={filters.result}
                onChange={(result) => onChange({ result })}
            />
            {canReset(filters, search) && (
                <Button variant="ghost" size="sm" onClick={reset}>
                    <X />
                    Atur ulang
                </Button>
            )}
        </FilterBar>
    );
}
