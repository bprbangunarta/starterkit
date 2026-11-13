import { toQuery } from '@/components/offices/office-query';
import type { OfficeFilters } from '@/components/offices/types';
import { useListNavigation } from '@/hooks/use-list-navigation';
import { offices } from '@/routes';

const RELOADED = ['offices', 'filters'];

export function useOfficeNavigation(filters: OfficeFilters) {
    return useListNavigation(filters, { url: offices().url, toQuery, only: RELOADED });
}
