// @vitest-environment node
/// <reference types="node" />
import { existsSync, readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';
import { MENU } from '@/layouts/menu';
import type { MenuLinkItem } from '@/layouts/menu';

const routes = readFileSync(new URL('../../../routes/web.php', import.meta.url), 'utf8');

/** Every link the sidebar shows, submenu links included. */
const links: MenuLinkItem[] = MENU.flatMap((section) => section.items).flatMap((item) => ('children' in item ? item.children : [item]));

/** The page file a menu address stands for: `/` is the dashboard, `/audit-logs` is `audit-logs`. */
const pageName = (address: string) => (address === '/' ? 'dashboard' : address.slice(1));

describe('menu name, address, route, and page file', () => {
    it('reads the menu links', () => {
        expect(links.length).toBeGreaterThanOrEqual(2);
    });

    it('gives every menu page a file named like its address (/audit-logs is pages/audit-logs.tsx)', () => {
        const missing = links.filter((link) => !existsSync(new URL(`../pages/${pageName(link.to)}.tsx`, import.meta.url))).map((link) => link.to);

        expect(missing, 'menu addresses without a page file of the same name in pages/').toEqual([]);
    });

    it('registers every menu page as a named route in routes/web.php with the same name', () => {
        // Anything may sit between the call and `->name(...)` (middleware, constraints), as long as it is one statement.
        const registered = (link: MenuLinkItem) => new RegExp(`Route::\\w+\\('${link.to}'[^;]*->name\\('${pageName(link.to)}'\\)`).test(routes);
        const missing = links.filter((link) => !registered(link)).map((link) => link.to);

        expect(missing, 'menu addresses without a route of the same name in routes/web.php').toEqual([]);
    });
});
