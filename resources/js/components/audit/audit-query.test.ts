import { describe, expect, it } from 'vitest';
import { canReset, hasActiveFilters, toQuery } from '@/components/audit/audit-query';
import type { AuditFilters } from '@/components/audit/types';

const defaults: AuditFilters = { search: null, module: null, result: null, from: null, to: null, sort: 'time', direction: 'desc', per_page: 25 };

describe('toQuery', () => {
    it('is empty for the default view, so its address stays clean', () => {
        expect(toQuery(defaults)).toEqual({});
    });

    it('carries every filter that is set', () => {
        const query = toQuery({ ...defaults, search: 'masuk', module: 'Autentikasi', result: 'failed', from: '2026-03-01', to: '2026-03-31' });

        expect(query).toEqual({ search: 'masuk', module: 'Autentikasi', result: 'failed', from: '2026-03-01', to: '2026-03-31' });
    });

    it('carries sort, direction, and page size only when they differ from the defaults', () => {
        expect(toQuery({ ...defaults, sort: 'outcome', direction: 'asc', per_page: 50 })).toEqual({ sort: 'outcome', direction: 'asc', per_page: 50 });
    });

    it('carries the page only after the first', () => {
        expect(toQuery(defaults, 1)).toEqual({});
        expect(toQuery(defaults, 3)).toEqual({ page: 3 });
    });
});

describe('hasActiveFilters', () => {
    it('ignores sorting and page size', () => {
        expect(hasActiveFilters({ ...defaults, sort: 'event', direction: 'asc', per_page: 100 })).toBe(false);
    });

    it('is true when any filter is set', () => {
        expect(hasActiveFilters({ ...defaults, from: '2026-03-01' })).toBe(true);
    });
});

describe('canReset', () => {
    it('is false for the default view', () => {
        expect(canReset(defaults, '')).toBe(false);
    });

    it('is true when a filter, the search text, or the order differs from the default', () => {
        expect(canReset({ ...defaults, result: 'failed' }, '')).toBe(true);
        expect(canReset(defaults, 'masuk')).toBe(true);
        expect(canReset({ ...defaults, sort: 'event' }, '')).toBe(true);
        expect(canReset({ ...defaults, direction: 'asc' }, '')).toBe(true);
    });
});
