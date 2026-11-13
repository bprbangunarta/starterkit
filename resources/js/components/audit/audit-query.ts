import type { AuditFilters } from '@/components/audit/types';
import { DEFAULT_PER_PAGE } from '@/lib/list';

/** The URL query of a filter set: only what differs from the defaults, so the default view has a clean address. */
export function toQuery(filters: AuditFilters, page = 1): Record<string, string | number> {
    const query: Record<string, string | number> = {};
    const text = { search: filters.search, module: filters.module, result: filters.result, from: filters.from, to: filters.to };

    for (const [key, value] of Object.entries(text)) {
        if (value) {
            query[key] = value;
        }
    }

    if (filters.sort !== 'time') {
        query.sort = filters.sort;
    }

    if (filters.direction !== 'desc') {
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

export function hasActiveFilters(filters: AuditFilters): boolean {
    return Boolean(filters.search || filters.module || filters.result || filters.from || filters.to);
}

/** The reset button shows only when something differs from the default view: a filter, the search text, or the order. */
export function canReset(filters: AuditFilters, search: string): boolean {
    return hasActiveFilters(filters) || search !== '' || filters.sort !== 'time' || filters.direction !== 'desc';
}
