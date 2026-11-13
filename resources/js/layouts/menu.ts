import { Database, HardDrive, LayoutDashboard, Palette, ScrollText, SlidersHorizontal, UserRound, Users } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { auditLogs, dashboard, offices, profile, roles, settings, storage, uiGuideline, users } from '@/routes';

export type MenuLinkItem = {
    label: string;
    to: string;
    /** Submenu entries have no icon: they get a plain dot. */
    icon?: LucideIcon;
    end?: boolean;
    /** Shown only to the Super Admin; the route itself is limited too (D-0029). */
    superAdminOnly?: boolean;
};

/** A menu entry is a link, or a parent with submenu links (opened and closed by a button). */
export type MenuItem = MenuLinkItem | { label: string; icon: LucideIcon; children: MenuLinkItem[] };

/**
 * The sidebar: add an entry here for every page. The naming, label length, order, and submenu rules are in `.ai/rules/frontend-react.md` (Menu),
 * held by `layouts/menu-names.test.ts` and `npm run standards`.
 */
export const MENU: { label?: string; items: MenuItem[] }[] = [
    { items: [{ label: 'Dashboard', to: dashboard().url, icon: LayoutDashboard, end: true }] },
    { items: [{ label: 'User Profile', to: profile().url, icon: UserRound }] },
    {
        label: 'Administrator',
        items: [
            {
                label: 'Kelola Referensi',
                icon: Database,
                children: [
                    { label: 'Data Kantor', to: offices().url, superAdminOnly: true },
                    { label: 'Data Jabatan', to: roles().url, superAdminOnly: true },
                ],
            },
            { label: 'Kelola Pengguna', to: users().url, icon: Users, superAdminOnly: true },
        ],
    },

    {
        label: 'Design Sistem',
        items: [
            { label: 'Audit Logs', to: auditLogs().url, icon: ScrollText, superAdminOnly: true },
            { label: 'Pengaturan', to: settings().url, icon: SlidersHorizontal, superAdminOnly: true },
            { label: 'Penyimpanan', to: storage().url, icon: HardDrive, superAdminOnly: true },
            { label: 'UI Guideline', to: uiGuideline().url, icon: Palette },
        ],
    },
];

/** The menu a person may see: entries for the Super Admin are left out for everybody else, and so are parents and sections left empty by that. */
export function visibleMenu(isSuperAdmin: boolean): typeof MENU {
    const allowed = (item: MenuLinkItem) => isSuperAdmin || !item.superAdminOnly;

    return MENU.map((section) => ({
        ...section,
        items: section.items.flatMap((item): MenuItem[] => {
            if (!('children' in item)) {
                return allowed(item) ? [item] : [];
            }

            const children = item.children.filter(allowed);

            return children.length > 0 ? [{ ...item, children }] : [];
        }),
    })).filter((section) => section.items.length > 0);
}
