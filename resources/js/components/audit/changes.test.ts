import { describe, expect, it } from 'vitest';
import { changeRows, formatChangeValue } from '@/components/audit/changes';

describe('formatChangeValue', () => {
    it('shows a dash for an empty value', () => {
        expect([null, undefined, ''].map(formatChangeValue)).toEqual(['–', '–', '–']);
    });

    it('shows text, numbers, and booleans as they are', () => {
        expect(['baru', 12, false].map(formatChangeValue)).toEqual(['baru', '12', 'false']);
    });

    it('shows a list of names on one line and an empty list as a dash', () => {
        expect(formatChangeValue(['Peninjau', 'Guest'])).toBe('Peninjau, Guest');
        expect(formatChangeValue([])).toBe('–');
    });

    it('shows an object as JSON, never as [object Object]', () => {
        expect(formatChangeValue({ a: 1 })).toBe('{"a":1}');
    });
});

describe('changeRows', () => {
    it('lists every attribute from either side with its label', () => {
        const rows = changeRows({ old: { username: 'lama', email: 'a' }, attributes: { username: 'baru', deleted_at: 'x' } });

        expect(rows).toEqual([
            { field: 'username', label: 'Username', before: 'lama', after: 'baru' },
            { field: 'email', label: 'Email', before: 'a', after: '–' },
            { field: 'deleted_at', label: 'Dihapus', before: '–', after: 'x' },
        ]);
    });

    it('falls back to the column name when it has no label', () => {
        expect(changeRows({ old: [], attributes: { kolom_baru: 1 } })[0]?.label).toBe('kolom_baru');
    });

    it('treats the empty array PHP sends for an empty side as no changes', () => {
        expect(changeRows({ old: [], attributes: [] })).toEqual([]);
    });
});
