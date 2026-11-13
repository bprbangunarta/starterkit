import type { AuditEntry } from '@/components/audit/types';
import type { BadgeTone } from '@/components/ui/misc';

export const RESULT_TONES: Record<string, BadgeTone> = { succeeded: 'success', refused: 'warning', failed: 'danger' };

/** Who acted: the account, a visitor who was not signed in (their address is known), or the system (a command or job). */
export function actorLabel(entry: AuditEntry): string {
    return entry.causer?.name ?? (entry.ip_address ? 'Tamu' : 'Sistem');
}

export function subjectLabel(entry: AuditEntry): string {
    return entry.subject ? `${entry.subject.type} #${entry.subject.id}` : '–';
}
