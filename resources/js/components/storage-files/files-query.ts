import type { FilesFilters } from '@/components/storage-files/types';
import { canResetList, toListQuery } from '@/lib/list';

/** The URL query of the file list: the place, then only what differs from the defaults. */
export function toQuery(filters: FilesFilters, page = 1): Record<string, string | number> {
    return { ...(filters.path === '' ? {} : { path: filters.path }), ...toListQuery(filters, page, 'name') };
}

export const canReset = (filters: FilesFilters, search: string) => canResetList(filters, search, 'name');
