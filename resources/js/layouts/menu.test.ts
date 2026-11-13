import { describe, expect, it } from 'vitest';
import { MENU, visibleMenu } from '@/layouts/menu';
import type { MenuLinkItem } from '@/layouts/menu';

const labels = (menu: ReturnType<typeof visibleMenu>) => menu.flatMap((section) => section.items).map((item) => item.label);

describe('visibleMenu', () => {
    it('shows the Super Admin every entry, with the reference pages under their group', () => {
        const menu = visibleMenu(true);
        const group = menu.flatMap((section) => section.items).find((item) => item.label === 'Kelola Referensi');

        expect(labels(menu)).toEqual([
            'Dashboard',
            'User Profile',
            'Kelola Referensi',
            'Kelola Pengguna',
            'Audit Logs',
            'Pengaturan',
            'Penyimpanan',
            'UI Guideline',
        ]);
        expect(group && 'children' in group ? group.children.map((child) => child.label) : []).toEqual(['Data Kantor', 'Data Jabatan']);
    });

    it('leaves out the Super Admin entries, and the group they empty, for everybody else', () => {
        expect(labels(visibleMenu(false))).toEqual(['Dashboard', 'User Profile', 'UI Guideline']);
    });

    it('names the sections', () => {
        expect(visibleMenu(true).flatMap((section) => (section.label ? [section.label] : []))).toEqual(['Administrator', 'Design Sistem']);
    });

    it('sends every link to a page of its own', () => {
        const links: MenuLinkItem[] = MENU.flatMap((section) => section.items).flatMap((item) => ('children' in item ? item.children : [item]));
        const addresses = links.map((link) => link.to);

        expect(new Set(addresses).size).toBe(addresses.length);
    });
});
