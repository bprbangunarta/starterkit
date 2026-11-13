import { TONES } from '@/components/guideline/guideline-data';
import type { BadgeTone } from '@/components/ui/misc';

export type Row = { id: number; name: string; status: BadgeTone; amount: number };
export type Draft = { id: number | null; name: string; status: BadgeTone; amount: string };
export type Order = { sort: string; direction: 'asc' | 'desc' };

type View = { search: string; status: string | null; order: Order; page: number; perPage: number };

export const EMPTY_DRAFT: Draft = { id: null, name: '', status: 'success', amount: '' };

const NAMES = ['Alfa', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta', 'Iota', 'Kappa', 'Lambda', 'Sigma'];
const STATUSES: BadgeTone[] = ['success', 'warning', 'danger', 'info', 'neutral'];

export const INITIAL_ROWS: Row[] = NAMES.map((name, index) => ({
    id: index + 1,
    name: `Proyek ${name}`,
    status: STATUSES[index % STATUSES.length] ?? 'neutral',
    amount: 870_000 + ((index * 3_730_000) % 12_000_000),
}));

export function toDraft(row: Row): Draft {
    return { id: row.id, name: row.name, status: row.status, amount: String(row.amount) };
}

const compare: Record<string, (a: Row, b: Row) => number> = {
    name: (a, b) => a.name.localeCompare(b.name, 'id'),
    status: (a, b) => TONES.indexOf(a.status) - TONES.indexOf(b.status),
    amount: (a, b) => a.amount - b.amount,
};

/** In-memory filter, sort, and paging. A list paged by the server does this there, with whitelisted sort keys. */
export function pageOfRows(rows: Row[], { search, status, order, page, perPage }: View) {
    const matching = rows
        .filter((r) => r.name.toLowerCase().includes(search.trim().toLowerCase()) && (status === null || r.status === status))
        .sort((a, b) => (compare[order.sort]?.(a, b) ?? 0) * (order.direction === 'asc' ? 1 : -1));
    const lastPage = Math.max(1, Math.ceil(matching.length / perPage));
    const current = Math.min(page, lastPage);

    return { matching, lastPage, current, visible: matching.slice((current - 1) * perPage, current * perPage) };
}
