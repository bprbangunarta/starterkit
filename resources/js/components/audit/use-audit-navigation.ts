import { toQuery } from '@/components/audit/audit-query';
import type { AuditFilters } from '@/components/audit/types';
import { useListNavigation } from '@/hooks/use-list-navigation';
import { auditLogs } from '@/routes';

const RELOADED = ['logs', 'filters'];

export function useAuditNavigation(filters: AuditFilters) {
    return useListNavigation(filters, { url: auditLogs().url, toQuery, only: RELOADED });
}
