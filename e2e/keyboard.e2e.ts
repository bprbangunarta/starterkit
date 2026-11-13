import { expect, test } from '@playwright/test';
import { signIn } from './sign-in';

test('a drawer opens with Enter, Escape closes it and focus returns to the button', async ({ page }) => {
    await signIn(page);
    await page.goto('/ui-guideline');

    const opener = page.getByRole('button', { name: 'Buka laci (Drawer)' });
    await opener.focus();
    await page.keyboard.press('Enter');
    await expect(page.getByRole('dialog', { name: 'Contoh Laci' })).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(page.getByRole('dialog')).toHaveCount(0);
    await expect(opener).toBeFocused();
});

test('the action menu works by keyboard and gives focus back', async ({ page }) => {
    await signIn(page);
    await page.goto('/ui-guideline');

    const trigger = page.getByRole('button', { name: 'Aksi', exact: true });
    await trigger.focus();
    await page.keyboard.press('Enter');
    // Opening by keyboard puts focus on the first item.
    await expect(page.getByRole('menuitem', { name: 'Ubah' })).toBeFocused();

    await page.keyboard.press('Escape');
    await expect(page.getByRole('menuitem')).toHaveCount(0);
    await expect(trigger).toBeFocused();
});

test('an accordion section opens and closes with the keyboard', async ({ page }) => {
    await signIn(page);
    await page.goto('/ui-guideline');

    const section = page.getByRole('button', { name: 'Kapan komponen baru dibuat?' });
    await section.focus();
    await expect(section).toHaveAttribute('aria-expanded', 'false');
    await page.keyboard.press('Enter');
    await expect(section).toHaveAttribute('aria-expanded', 'true');
    await page.keyboard.press('Space');
    await expect(section).toHaveAttribute('aria-expanded', 'false');
});

test('a combobox is operated with arrows and Enter', async ({ page }) => {
    await signIn(page);
    await page.goto('/audit-logs');

    await page.getByRole('combobox', { name: 'Filter hasil' }).focus();
    await page.keyboard.press('Enter');
    await expect(page.getByRole('listbox')).toBeVisible();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter');
    await expect(page.getByRole('listbox')).toHaveCount(0);
    await expect(page.getByRole('combobox', { name: 'Filter hasil' })).not.toContainText('Hasil');
});
