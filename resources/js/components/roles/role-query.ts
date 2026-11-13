import type { RoleFilters } from '@/components/roles/types';
import { canResetList, toListQuery } from '@/lib/list';

export const toQuery = (filters: RoleFilters, page = 1) => toListQuery(filters, page, 'name');

export const canReset = (filters: RoleFilters, search: string) => canResetList(filters, search, 'name');
