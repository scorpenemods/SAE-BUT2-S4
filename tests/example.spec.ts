import { test, expect } from '@playwright/test';

test('has title', async ({ page }) => {
  await page.goto('https://localhost/view/offer/list.php');
  await expect(page).toHaveTitle(/Le Petit Stage/);
});

test('searchbar is visible', async ({ page }) => {
  await page.goto('https://localhost/view/offer/list.php');
  await expect(page.locator('id=search-filter')).toBeVisible();
});

test('create offer is visible', async ({ page }) => {
  await page.goto('https://localhost/view/offer/list.php');
  await expect(page.locator('id=create')).toBeVisible();
});

test('pagination is visible', async ({ page }) => {
  await page.goto('https://localhost/view/offer/list.php');
  await expect(page.locator('id=pagination')).toBeVisible();
});

test('filter menu is invisible', async ({ page }) => {
  await page.goto('https://localhost/view/offer/list.php');
  await expect(page.locator('id=filter-panel-content')).not.toBeInViewport();
});

test('filter menu is visible', async ({ page }) => {
  await page.goto('https://localhost/view/offer/list.php');
  await page.click('id=openFilter')
  await expect(page.locator('id=filter-panel-content')).toBeInViewport();
});