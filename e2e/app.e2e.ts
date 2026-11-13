/// <reference types="node" />
import { expect, test } from '@playwright/test';
import { signIn } from './sign-in';

test('an unknown address shows the full-screen 404', async ({ page }) => {
    await page.goto('/tidak-ada');

    await expect(page.getByRole('heading', { name: 'Halaman tidak ditemukan' })).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Menu utama' })).toHaveCount(0);
});

test('a visitor without a session is sent to the sign-in page', async ({ page }) => {
    await page.goto('/audit-logs');

    await expect(page).toHaveURL(/\/login$/);
});

test('signs in and reads the audit log', async ({ page }) => {
    await signIn(page);

    await page.getByRole('link', { name: 'Audit Logs' }).click();
    await expect(page.getByRole('heading', { name: 'Audit Logs' })).toBeVisible();
    await expect(page.getByRole('row').nth(1)).toBeVisible();

    await page.getByPlaceholder('Cari pengguna, kejadian, modul, IP…').fill('Masuk');
    await expect(page.getByRole('row', { name: /Masuk/ }).first()).toBeVisible();
});

test('Audit Logs: the date range calendar is styled and picks a range', async ({ page }) => {
    await signIn(page);
    await page.getByRole('link', { name: 'Audit Logs' }).click();

    await page.getByRole('button', { name: 'Rentang tanggal' }).click();
    // Unstyled, the day cells stretch far past the 28px of the compact calendar.
    const cell = await page.getByRole('gridcell').first().boundingBox();
    expect(cell?.width).toBeLessThan(40);

    await page.locator('.rdp-day_button').filter({ hasText: /^3$/ }).click();
    await page.locator('.rdp-day_button').filter({ hasText: /^9$/ }).click();
    await expect(page.getByRole('button', { name: 'Rentang tanggal', exact: true })).toContainText('–');
});

test('the theme menu switches to dark, remembers it after a reload, and follows the system again', async ({ page }) => {
    await page.emulateMedia({ colorScheme: 'light' });
    await signIn(page);
    const html = page.locator('html');

    await page.getByRole('button', { name: 'Tema' }).click();
    await page.getByRole('menuitem', { name: 'Gelap' }).click();
    await expect(html).toHaveAttribute('data-theme', 'dark');
    await expect(page.locator('body')).toHaveCSS('background-color', 'oklch(0.17 0.012 265)');

    await page.reload();
    await expect(html).toHaveAttribute('data-theme', 'dark');

    await page.getByRole('button', { name: 'Tema' }).click();
    await page.getByRole('menuitem', { name: 'Ikuti sistem' }).click();
    await expect(html).not.toHaveAttribute('data-theme', /.+/);
});

test('with no saved choice the page follows a dark system setting', async ({ page }) => {
    await page.emulateMedia({ colorScheme: 'dark' });
    await page.goto('/login');

    await expect(page.locator('body')).toHaveCSS('background-color', 'oklch(0.17 0.012 265)');
});
