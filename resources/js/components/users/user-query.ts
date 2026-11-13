import type { UserFilters, UserStatus } from '@/components/users/types';
import { DEFAULT_PER_PAGE } from '@/lib/list';

/** The value of the office filter that stands for users without an office. */
export const NO_OFFICE = 'none';

export const STATUS_OPTIONS: { value: UserStatus; label: string }[] = [
    { value: 'active', label: 'Aktif' },
    { value: 'deleted', label: 'Dihapus' },
    { value: 'all', label: 'Semua' },
];

/** The status a Combobox value stands for; anything else is the default, active accounts. */
export function toStatus(value: string | null): UserStatus {
    return STATUS_OPTIONS.find((option) => option.value === value)?.value ?? 'active';
}

/** The URL query of a filter set: only what differs from the defaults, so the default view has a clean address. */
export function toQuery(filters: UserFilters, page = 1): Record<string, string | number> {
    const query: Record<string, string | number> = {};

    if (filters.search) {
        query.search = filters.search;
    }

    if (filters.role) {
        query.role = filters.role;
    }

    if (filters.office) {
        query.office = filters.office;
    }

    if (filters.status !== 'active') {
        query.status = filters.status;
    }

    if (filters.sort !== 'name') {
        query.sort = filters.sort;
    }

    if (filters.direction !== 'asc') {
        query.direction = filters.direction;
    }

    if (filters.per_page !== DEFAULT_PER_PAGE) {
        query.per_page = filters.per_page;
    }

    if (page > 1) {
        query.page = page;
    }

    return query;
}

export function hasActiveFilters(filters: UserFilters): boolean {
    return Boolean(filters.search || filters.role || filters.office || filters.status !== 'active');
}

/** The reset button shows only when something differs from the default view: a filter, the search text, or the order. */
export function canReset(filters: UserFilters, search: string): boolean {
    return hasActiveFilters(filters) || search !== '' || filters.sort !== 'name' || filters.direction !== 'asc';
}
