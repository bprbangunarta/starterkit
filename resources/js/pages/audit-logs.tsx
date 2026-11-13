import { Head } from '@inertiajs/react';
import { ScrollText } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { ExportButton } from '@/components/audit/audit-export';
import { AuditDetailDialog } from '@/components/audit/audit-detail-dialog';
import { auditColumns } from '@/components/audit/audit-columns';
import { hasActiveFilters } from '@/components/audit/audit-query';
import { AuditToolbar } from '@/components/audit/audit-toolbar';
import type { AuditEntry, AuditFilters, AuditOption, AuditPage, AuditSortKey } from '@/components/audit/types';
import { useAuditNavigation } from '@/components/audit/use-audit-navigation';
import { DataTable } from '@/components/ui/data-table';
import { PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';
import { PER_PAGE_OPTIONS } from '@/lib/list';

type Props = {
    logs: AuditPage;
    filters: AuditFilters;
    modules: string[];
    results: AuditOption[];
};

export default function AuditLogs({ logs, filters, modules, results }: Props) {
    const [detail, setDetail] = useState<AuditEntry | null>(null);
    const { loading, visit } = useAuditNavigation(filters);

    return (
        <>
            <Head title="Audit Logs" />
            <PageHeader
                title="Audit Logs"
                description="Catatan kejadian autentikasi dan perubahan data pengguna"
                actions={<ExportButton filters={filters} total={logs.meta.total} />}
            />
            <DataTable
                rows={logs.data}
                rowKey={(entry) => entry.id}
                columns={auditColumns()}
                loading={loading}
                onRowClick={setDetail}
                toolbar={<AuditToolbar filters={filters} modules={modules} results={results} onChange={visit} />}
                sort={{
                    sort: filters.sort,
                    direction: filters.direction,
                    onSort: (column) =>
                        visit({
                            sort: column as AuditSortKey,
                            direction: filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc',
                        }),
                }}
                pagination={{
                    meta: logs.meta,
                    perPage: filters.per_page,
                    options: PER_PAGE_OPTIONS,
                    onPage: (page) => visit({}, page),
                    onPerPage: (per_page) => visit({ per_page }),
                }}
                empty={{ icon: <ScrollText />, title: hasActiveFilters(filters) ? 'Tidak ada catatan yang cocok dengan filter' : 'Belum ada catatan' }}
            />
            <AuditDetailDialog entry={detail} onClose={() => setDetail(null)} />
        </>
    );
}

AuditLogs.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
