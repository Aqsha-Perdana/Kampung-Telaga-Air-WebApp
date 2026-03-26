import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

const setupRaw = execFileSync('php', ['tests/e2e/setup_booking_e2e.php'], {
  encoding: 'utf8',
});
const setup = JSON.parse(setupRaw.trim());

const refundReason = 'Playwright refund request for paid order';

test('visitor can request a refund for a paid order from order history', async ({ page }) => {
  test.setTimeout(300000);

  await page.goto('/visitor/login-visitor');

  await page.getByPlaceholder('name@email.com').fill(setup.email);
  await page.locator('input[type="password"]').fill(setup.password);
  await page.getByRole('button', { name: /login/i }).click();

  await page.waitForURL(/127\.0\.0\.1:8010\/$/);

  await page.goto('/tour-package/PKT00001');
  await expect(page.getByRole('heading', { name: /paket cihuy/i })).toBeVisible();

  await page.locator('#addToCartForm input[name="jumlah_peserta"]').fill('1');
  await page.locator('#addToCartForm input[name="tanggal_keberangkatan"]').fill('2026-04-17');
  await page.locator('#addToCartForm textarea[name="catatan"]').fill('Playwright refund request flow');

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

  const orderId = new URL(page.url()).searchParams.get('order_id');
  expect(orderId).toBeTruthy();

  await page.goto('/orders');
  await expect(page.getByRole('heading', { name: /order history/i })).toBeVisible();
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.getByRole('button', { name: /request refund/i }).first()).toBeVisible();

  await page.getByRole('button', { name: /request refund/i }).first().click();
  await expect(page.locator('.modal.show')).toBeVisible();

  await page.locator('.modal.show textarea[name="reason"]').fill(refundReason);
  await page.locator('.modal.show input[name="confirm_refund_fee"]').check();
  await page.locator('.modal.show button[type="submit"]').click();

  await page.waitForURL(/\/orders$/);
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Refund Requested');
  await expect(page.locator('body')).toContainText(`Reason: ${refundReason}`);
  await expect(page.locator('body')).toContainText('1 Refund Review');
  await expect(page.getByRole('button', { name: /refund requested/i })).toBeDisabled();
});
