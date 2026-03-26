import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

const setupRaw = execFileSync('php', ['tests/e2e/setup_booking_e2e.php'], {
  encoding: 'utf8',
});
const setup = JSON.parse(setupRaw.trim());

test('visitor can cancel a pending order from order details', async ({ page }) => {
  test.setTimeout(180000);

  await page.goto('/visitor/login-visitor');

  await page.getByPlaceholder('name@email.com').fill(setup.email);
  await page.locator('input[type="password"]').fill(setup.password);
  await page.getByRole('button', { name: /login/i }).click();

  await page.waitForURL(/127\.0\.0\.1:8010\/$/);

  await page.goto('/tour-package/PKT00001');
  await expect(page.getByRole('heading', { name: /paket cihuy/i })).toBeVisible();

  await page.locator('#addToCartForm input[name="jumlah_peserta"]').fill('1');
  await page.locator('#addToCartForm input[name="tanggal_keberangkatan"]').fill('2026-04-16');
  await page.locator('#addToCartForm textarea[name="catatan"]').fill('Playwright pending cancellation flow');

  const addToCartResponsePromise = page.waitForResponse(response =>
    response.request().method() === 'POST' &&
    response.url().includes('/cart') &&
    response.status() === 200
  );
  await page.locator('#addToCartForm button[type="submit"]').click();
  const addToCartResponse = await addToCartResponsePromise;
  await expect.poll(async () => (await addToCartResponse.json()).success).toBe(true);

  await page.goto('/cart');
  await page.getByRole('link', { name: /proceed to checkout/i }).click();
  await page.waitForURL(/\/checkout$/);

  await expect(page.getByRole('heading', { name: /checkout/i })).toBeVisible();
  await page.locator('#customer_phone').fill('08123456789');
  await page.locator('#customer_address').fill('Playwright Test Address');

  const processResponsePromise = page.waitForResponse(response =>
    response.request().method() === 'POST' &&
    response.url().includes('/checkout/process') &&
    response.status() === 200
  );
  await page.locator('#submit-button').click();
  const processResponse = await processResponsePromise;
  const processPayload = await processResponse.json();

  await expect.poll(() => processPayload.success).toBe(true);
  await expect(page.locator('#card-errors')).not.toHaveText('', { timeout: 30000 });

  const orderId = processPayload.order_id;
  await page.goto(`/orders/${orderId}`);

  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Pending');
  await expect(page.getByRole('button', { name: /cancel order/i })).toBeVisible();

  page.once('dialog', dialog => dialog.accept());
  await page.getByRole('button', { name: /cancel order/i }).click();

  await page.waitForURL(/\/orders$/);
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Cancelled');
  await expect(page.locator('body')).toContainText('Failed / Cancelled');
  await expect(page.locator('body')).toContainText('0 Pending');
});
