import type { OfficeFilters } from '@/components/offices/types';
import { canResetList, toListQuery } from '@/lib/list';

export const toQuery = (filters: OfficeFilters, page = 1) => toListQuery(filters, page, 'name');

export const canReset = (filters: OfficeFilters, search: string) => canResetList(filters, search, 'name');
