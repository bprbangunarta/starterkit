import { describe, expect, it } from 'vitest';
import type { UserFilters } from '@/components/users/types';
import { canReset, hasActiveFilters, toQuery, toStatus } from '@/components/users/user-query';

const defaults: UserFilters = { search: null, role: null, office: null, status: 'active', sort: 'name', direction: 'asc', per_page: 25 };

describe('user list query', () => {
    it('keeps the default view out of the address', () => {
        expect(toQuery(defaults)).toEqual({});
    });

    it('writes the status only when it is not the default', () => {
        expect(toQuery({ ...defaults, status: 'deleted' })).toEqual({ status: 'deleted' });
        expect(toQuery({ ...defaults, status: 'all' }, 2)).toEqual({ status: 'all', page: 2 });
    });

    it('counts a status other than active as a filter and offers the reset', () => {
        expect(hasActiveFilters({ ...defaults, status: 'deleted' })).toBe(true);
        expect(canReset({ ...defaults, status: 'deleted' }, '')).toBe(true);
        expect(canReset(defaults, '')).toBe(false);
    });

    it('writes the office filter, and counts it as a filter with a reset', () => {
        expect(toQuery({ ...defaults, office: '3' })).toEqual({ office: '3' });
        expect(toQuery({ ...defaults, office: 'none' })).toEqual({ office: 'none' });
        expect(hasActiveFilters({ ...defaults, office: '3' })).toBe(true);
        expect(canReset({ ...defaults, office: 'none' }, '')).toBe(true);
    });

    it('reads a combobox value as a status and falls back to active', () => {
        expect(toStatus('deleted')).toBe('deleted');
        expect(toStatus('all')).toBe('all');
        expect(toStatus(null)).toBe('active');
        expect(toStatus('hapus')).toBe('active');
    });
});
