import { useListNavigation } from '@/hooks/use-list-navigation';
import { toQuery } from '@/components/users/user-query';
import type { UserFilters } from '@/components/users/types';
import { users } from '@/routes';

const RELOADED = ['users', 'filters'];

export function useUserNavigation(filters: UserFilters) {
    return useListNavigation(filters, { url: users().url, toQuery, only: RELOADED });
}
