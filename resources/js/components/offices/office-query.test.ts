import { describe, expect, it } from 'vitest';
import { canReset, toQuery } from '@/components/offices/office-query';
import type { OfficeFilters } from '@/components/offices/types';

const defaults: OfficeFilters = { search: null, sort: 'name', direction: 'asc', per_page: 25 };

describe('office list query', () => {
    it('keeps the default view out of the address', () => {
        expect(toQuery(defaults)).toEqual({});
    });

    it('writes only what differs from the defaults', () => {
        expect(toQuery({ search: 'utara', sort: 'latitude', direction: 'desc', per_page: 50 }, 2)).toEqual({
            search: 'utara',
            sort: 'latitude',
            direction: 'desc',
            per_page: 50,
            page: 2,
        });
    });

    it('offers the reset for a search, typed or applied, or a changed order', () => {
        expect(canReset(defaults, '')).toBe(false);
        expect(canReset(defaults, 'ut')).toBe(true);
        expect(canReset({ ...defaults, search: 'ut' }, 'ut')).toBe(true);
        expect(canReset({ ...defaults, direction: 'desc' }, '')).toBe(true);
    });
});
