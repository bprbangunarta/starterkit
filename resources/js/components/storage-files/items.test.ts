import { describe, expect, it } from 'vitest';
import { crumbsOf, kindOf } from '@/components/storage-files/items';
import type { StorageItem } from '@/components/storage-files/types';

const item = (name: string, type: StorageItem['type'] = 'file'): StorageItem => ({ name, path: name, type, size: type === 'file' ? 1 : null, modified: null });

describe('kindOf', () => {
    it('names a folder and a file by its extension, in any case', () => {
        expect(kindOf(item('Laporan', 'folder')).label).toBe('Folder');
        expect(kindOf(item('ringkasan.PDF')).label).toBe('PDF');
        expect(kindOf(item('Data.xlsx')).label).toBe('Spreadsheet');
        expect(kindOf(item('foto.jpeg')).label).toBe('Gambar');
        expect(kindOf(item('arsip.zip')).label).toBe('Arsip');
    });

    it('calls anything else a plain file', () => {
        expect(kindOf(item('catatan.xyz')).label).toBe('Berkas');
        expect(kindOf(item('tanpa-ekstensi')).label).toBe('Berkas');
    });
});

describe('crumbsOf', () => {
    it('is empty at the top and lists each folder with the path that opens it', () => {
        expect(crumbsOf('')).toEqual([]);
        expect(crumbsOf('Laporan/2026')).toEqual([
            { name: 'Laporan', path: 'Laporan' },
            { name: '2026', path: 'Laporan/2026' },
        ]);
    });
});
