import { expect } from '@playwright/test';
import type { Page } from '@playwright/test';

/** The demo account of the seeders (`SEED_DEMO_ACCOUNTS`): a Super Admin with the factory's default password. */
export async function signIn(page: Page): Promise<void> {
    await page.goto('/login');
    await page.getByPlaceholder('Email atau Username').fill('superadmin');
    await page.getByPlaceholder('************************').fill('password');
    await page.getByRole('button', { name: 'Masuk' }).click();
    await expect(page.getByRole('navigation', { name: 'Menu utama' })).toBeVisible();
}
