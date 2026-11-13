import type { AuditEntry } from '@/components/audit/types';

/** An audit entry as the server sends it, with only the given fields changed. */
export function auditEntry(overrides: Partial<AuditEntry> = {}): AuditEntry {
    return {
        id: 1,
        created_at: '2026-03-10T08:15:00+07:00',
        event: { value: 'updated', label: 'Diubah' },
        result: { value: 'succeeded', label: 'Berhasil' },
        description: 'Pengguna diubah',
        module: 'Pengguna',
        causer: { id: 7, name: 'Rina Wulandari', username: 'rina.w' },
        subject: { type: 'Pengguna', id: 12 },
        ip_address: '10.0.0.5',
        request_id: '3f1c2d7e-0000-4000-8000-000000000001',
        method: 'POST',
        path: '/pengguna/12',
        user_agent: 'UjiBrowser/1.0',
        reason: null,
        details: [],
        changes: { old: { username: 'lama', password: '[disembunyikan]' }, attributes: { username: 'baru', password: '[disembunyikan]' } },
        ...overrides,
    };
}
