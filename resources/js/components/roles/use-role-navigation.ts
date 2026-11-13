import { toQuery } from '@/components/roles/role-query';
import type { RoleFilters } from '@/components/roles/types';
import { useListNavigation } from '@/hooks/use-list-navigation';
import { roles } from '@/routes';

const RELOADED = ['roles', 'filters'];

export function useRoleNavigation(filters: RoleFilters) {
    return useListNavigation(filters, { url: roles().url, toQuery, only: RELOADED });
}
