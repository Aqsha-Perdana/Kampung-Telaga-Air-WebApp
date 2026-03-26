import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

const setupRaw = execFileSync('php', ['tests/e2e/setup_booking_e2e.php'], {
  encoding: 'utf8',
});
const setup = JSON.parse(setupRaw.trim());

test('visitor can log in, book a package, and complete Stripe test payment', async ({ page }) => {
  test.setTimeout(240000);

  await page.goto('/visitor/login-visitor');

  await page.getByPlaceholder('name@email.com').fill(setup.email);
  await page.locator('input[type="password"]').fill(setup.password);
  await page.getByRole('button', { name: /login/i }).click();

  await page.waitForURL(/127\.0\.0\.1:8010\/$/);

  await page.goto('/tour-package/PKT00001');
  await expect(page.getByRole('heading', { name: /paket cihuy/i })).toBeVisible();

  await page.locator('#addToCartForm input[name="jumlah_peserta"]').fill('1');
  await page.locator('#addToCartForm input[name="tanggal_keberangkatan"]').fill('2026-04-15');
  await page.locator('#addToCartForm textarea[name="catatan"]').fill('Playwright E2E booking flow');

  const addToCartResponsePromise = page.waitForResponse(response =>
    response.request().method() === 'POST' &&
    response.url().includes('/cart') &&
    response.status() === 200
  );
  await page.locator('#addToCartForm button[type="submit"]').click();
  const addToCartResponse = await addToCartResponsePromise;
  await expect.poll(async () => (await addToCartResponse.json()).success).toBe(true);

  await page.goto('/cart');
  await expect(page.getByRole('heading', { name: /shopping cart/i })).toBeVisible();
  await expect(page.locator('body')).toContainText('Paket Cihuy');

  await page.getByRole('link', { name: /proceed to checkout/i }).click();
  await page.waitForURL(/\/checkout$/);

  await expect(page.getByRole('heading', { name: /checkout/i })).toBeVisible();
  await page.locator('#customer_phone').fill('08123456789');
  await page.locator('#customer_address').fill('Playwright Test Address');

  const stripeFrame = page.frameLocator('iframe[title*="Secure card payment input frame"]');
  await stripeFrame.getByPlaceholder('Card number').fill('4242424242424242');
  await stripeFrame.getByPlaceholder('MM / YY').fill('1234');
  await stripeFrame.getByPlaceholder('CVC').fill('123');

  const postalInput = stripeFrame.getByPlaceholder('ZIP');
  if (await postalInput.count()) {
    await postalInput.fill('93350');
  }

  await page.locator('#submit-button').click();

  await expect(page.getByRole('heading', { name: /payment successful/i })).toBeVisible({ timeout: 150000 });
  await expect(page).toHaveURL(/\/checkout\/success\?order_id=/);
  await expect(page.locator('body')).toContainText('Credit/Debit Card (Stripe)');
  await expect(page.locator('body')).toContainText('Your Redemption Code');
});
