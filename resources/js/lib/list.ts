export const DEFAULT_PER_PAGE = 25;
export const PER_PAGE_OPTIONS = [10, 25, 50, 100];

/** The filters every plain list shares: a search text, one sort key with its direction, and the page size. */
export type ListFilters = { search: string | null; sort: string; direction: 'asc' | 'desc'; per_page: number };

/** The URL query of a plain list: only what differs from the defaults, so the default view has a clean address. */
export function toListQuery(filters: ListFilters, page: number, defaultSort: string): Record<string, string | number> {
    const query: Record<string, string | number> = {};

    if (filters.search) {
        query.search = filters.search;
    }

    if (filters.sort !== defaultSort) {
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

/** The reset button shows only when something differs from the default view: the search text, typed or applied, or the order. */
export function canResetList(filters: ListFilters, search: string, defaultSort: string): boolean {
    return Boolean(filters.search) || search !== '' || filters.sort !== defaultSort || filters.direction !== 'asc';
}
