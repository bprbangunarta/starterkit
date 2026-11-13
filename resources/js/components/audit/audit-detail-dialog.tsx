import { ScrollText } from 'lucide-react';
import { actorLabel, subjectLabel } from '@/components/audit/audit-labels';
import { changeRows } from '@/components/audit/changes';
import type { ChangeRow } from '@/components/audit/changes';
import type { AuditEntry } from '@/components/audit/types';
import { DataTable } from '@/components/ui/data-table';
import type { Column } from '@/components/ui/data-table';
import { DialogBody, Modal } from '@/components/ui/dialog';
import { formatDateTime } from '@/lib/format';

const CHANGE_COLUMNS: Column<ChangeRow>[] = [
    { key: 'field', header: 'Kolom', className: 'font-medium', cell: (row) => row.label },
    { key: 'before', header: 'Sebelum', className: 'break-all text-muted', cell: (row) => row.before },
    { key: 'after', header: 'Sesudah', className: 'break-all', cell: (row) => row.after },
];

function Overview({ entry }: { entry: AuditEntry }) {
    const actor = entry.causer ? `${entry.causer.name} · ${entry.causer.username ?? 'tanpa username'}` : actorLabel(entry);
    const items: [string, string][] = [
        ['Waktu', formatDateTime(entry.created_at)],
        ['Pengguna', actor],
        ['Modul', entry.module ?? '–'],
        ['Data', subjectLabel(entry)],
        ['Hasil', entry.result.label],
        ['Keterangan', entry.description],
        ...entry.details.map((detail): [string, string] => [detail.label, detail.value]),
        ...(entry.reason ? [['Alasan', entry.reason] as [string, string]] : []),
        ['Alamat IP', entry.ip_address ?? '–'],
        ['Permintaan', [entry.method, entry.path].filter(Boolean).join(' ') || '–'],
        ['Peramban', entry.user_agent ?? '–'],
        ['ID permintaan', entry.request_id ?? '–'],
    ];

    return (
        <dl className="grid grid-cols-[7rem_1fr] gap-x-3 gap-y-1">
            {items.map(([label, value]) => (
                <div key={label} className="contents">
                    <dt className="text-xs text-muted">{label}</dt>
                    <dd className="break-all">{value}</dd>
                </div>
            ))}
        </dl>
    );
}

function Changes({ entry }: { entry: AuditEntry }) {
    const rows = changeRows(entry.changes);

    if (rows.length === 0) {
        return <p className="mt-3 text-sm text-muted">Tidak ada perubahan data pada catatan ini.</p>;
    }

    return (
        <div className="mt-3 max-h-64 overflow-y-auto rounded-md border border-line">
            <DataTable
                bare
                dense
                columns={CHANGE_COLUMNS}
                rows={rows}
                rowKey={(row) => row.field}
                empty={{ icon: <ScrollText />, title: 'Tidak ada perubahan kolom' }}
            />
        </div>
    );
}

export function AuditDetailDialog({ entry, onClose }: { entry: AuditEntry | null; onClose: () => void }) {
    return (
        <Modal
            wide
            open={entry !== null}
            onOpenChange={(open) => !open && onClose()}
            title={entry ? `Entri #${entry.id}` : 'Entri'}
            description={entry?.event.label ?? undefined}
        >
            {entry && (
                <DialogBody className="text-sm">
                    <Overview entry={entry} />
                    <Changes entry={entry} />
                </DialogBody>
            )}
        </Modal>
    );
}
