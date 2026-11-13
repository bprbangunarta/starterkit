import type { AuditEntry } from '@/components/audit/types';

const LABELS: Record<string, string> = {
    name: 'Nama',
    username: 'Username',
    email: 'Email',
    password: 'Kata sandi',
    roles: 'Jabatan',
    office_id: 'ID kantor',
    email_verified_at: 'Email terverifikasi',
    created_at: 'Dibuat',
    updated_at: 'Diubah',
    deleted_at: 'Dihapus',
};

export type ChangeRow = { field: string; label: string; before: string; after: string };

export function formatChangeValue(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '–';
    }

    if (typeof value === 'string') {
        return value;
    }

    if (Array.isArray(value) && value.every((item) => typeof item === 'string')) {
        return value.length === 0 ? '–' : value.join(', ');
    }

    if (typeof value === 'number' || typeof value === 'boolean') {
        return String(value);
    }

    return JSON.stringify(value);
}

function asRecord(value: unknown): Record<string, unknown> {
    return value !== null && typeof value === 'object' && !Array.isArray(value) ? (value as Record<string, unknown>) : {};
}

/** One row per attribute that appears on either side of the change. */
export function changeRows(changes: AuditEntry['changes']): ChangeRow[] {
    const before = asRecord(changes.old);
    const after = asRecord(changes.attributes);

    return [...new Set([...Object.keys(before), ...Object.keys(after)])].map((field) => ({
        field,
        label: LABELS[field] ?? field,
        before: formatChangeValue(before[field]),
        after: formatChangeValue(after[field]),
    }));
}
