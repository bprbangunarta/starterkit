import { describe, expect, it } from 'vitest';
import { canReset, toQuery } from '@/components/storage-files/files-query';
import type { FilesFilters } from '@/components/storage-files/types';

const defaults: FilesFilters = { path: '', search: null, sort: 'name', direction: 'asc', per_page: 25 };

describe('file list query', () => {
    it('keeps the top of a storage with the defaults out of the address', () => {
        expect(toQuery(defaults)).toEqual({});
    });

    it('carries the folder, and only what differs from the defaults', () => {
        expect(toQuery({ path: 'Laporan/2026', search: 'ringkas', sort: 'size', direction: 'desc', per_page: 50 }, 2)).toEqual({
            path: 'Laporan/2026',
            search: 'ringkas',
            sort: 'size',
            direction: 'desc',
            per_page: 50,
            page: 2,
        });
    });

    it('offers the reset for a search or a changed order, not for the folder alone', () => {
        expect(canReset({ ...defaults, path: 'Laporan' }, '')).toBe(false);
        expect(canReset(defaults, 'a')).toBe(true);
        expect(canReset({ ...defaults, sort: 'modified' }, '')).toBe(true);
    });
});
