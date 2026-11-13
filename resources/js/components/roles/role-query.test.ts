import { describe, expect, it } from 'vitest';
import { canReset, toQuery } from '@/components/roles/role-query';
import type { RoleFilters } from '@/components/roles/types';

const defaults: RoleFilters = { search: null, sort: 'name', direction: 'asc', per_page: 25 };

describe('role list query', () => {
    it('keeps the default view out of the address', () => {
        expect(toQuery(defaults)).toEqual({});
    });

    it('writes only what differs from the defaults', () => {
        expect(toQuery({ search: 'ana', sort: 'users', direction: 'desc', per_page: 50 }, 3)).toEqual({
            search: 'ana',
            sort: 'users',
            direction: 'desc',
            per_page: 50,
            page: 3,
        });
    });

    it('offers the reset for a search, typed or applied, or a changed order', () => {
        expect(canReset(defaults, '')).toBe(false);
        expect(canReset(defaults, 'an')).toBe(true);
        expect(canReset({ ...defaults, search: 'an' }, 'an')).toBe(true);
        expect(canReset({ ...defaults, sort: 'created' }, '')).toBe(true);
    });
});
