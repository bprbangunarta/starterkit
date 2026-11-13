import { actorLabel, RESULT_TONES, subjectLabel } from '@/components/audit/audit-labels';
import type { AuditEntry } from '@/components/audit/types';
import type { Column } from '@/components/ui/data-table';
import { Badge } from '@/components/ui/misc';
import { formatDateTime } from '@/lib/format';

export function auditColumns(): Column<AuditEntry>[] {
    return [
        { key: 'time', header: 'Waktu', sort: 'time', className: 'whitespace-nowrap', cell: (entry) => formatDateTime(entry.created_at) },
        { key: 'host', header: 'Host', sort: 'host', className: 'whitespace-nowrap', cell: (entry) => entry.ip_address ?? '–' },
        { key: 'user', header: 'Pengguna', sort: 'user', cell: (entry) => actorLabel(entry) },
        { key: 'module', header: 'Modul', sort: 'module', className: 'whitespace-nowrap', cell: (entry) => entry.module ?? '–' },
        { key: 'event', header: 'Kejadian', sort: 'event', cell: (entry) => entry.event.label ?? entry.event.value ?? '–' },
        { key: 'record', header: 'Data', sort: 'record', cell: (entry) => subjectLabel(entry) },
        {
            key: 'outcome',
            header: 'Hasil',
            sort: 'outcome',
            cell: (entry) => <Badge tone={RESULT_TONES[entry.result.value] ?? 'neutral'}>{entry.result.label}</Badge>,
        },
    ];
}
