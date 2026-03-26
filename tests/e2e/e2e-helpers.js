import { expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

const visitorSetupRaw = execFileSync('php', ['tests/e2e/setup_booking_e2e.php'], {
  encoding: 'utf8',
});
const adminSetupRaw = execFileSync('php', ['tests/e2e/setup_admin_e2e.php'], {
  encoding: 'utf8',
});

export const visitorSetup = JSON.parse(visitorSetupRaw.trim());
export const adminSetup = JSON.parse(adminSetupRaw.trim());

export async function loginVisitor(page) {
  await page.goto('/visitor/login-visitor');
  await page.getByPlaceholder('name@email.com').fill(visitorSetup.email);
  await page.locator('input[type="password"]').fill(visitorSetup.password);
  await page.getByRole('button', { name: /login/i }).click();
  await page.waitForURL(/127\.0\.0\.1:8010\/$/);
}

export async function loginAdmin(page) {
  await page.goto('/admin/login');
  await page.getByPlaceholder('name@email.com').fill(adminSetup.email);
  await page.locator('input[name="password"]').fill(adminSetup.password);
  await page.getByRole('button', { name: /login/i }).click();
  await page.waitForURL(/\/admin\/dashboard$/);
}

export async function addPackageToCart(page, departureDate, note) {
  await page.goto('/tour-package/PKT00001');
  await expect(page.getByRole('heading', { name: /paket cihuy/i })).toBeVisible();

  await page.locator('#addToCartForm input[name="jumlah_peserta"]').fill('1');
  await page.locator('#addToCartForm input[name="tanggal_keberangkatan"]').fill(departureDate);
  await page.locator('#addToCartForm textarea[name="catatan"]').fill(note);

  const addToCartResponsePromise = page.waitForResponse(response =>
    response.request().method() === 'POST' &&
    response.url().includes('/cart') &&
    response.status() === 200
  );
  await page.locator('#addToCartForm button[type="submit"]').click();
  const addToCartResponse = await addToCartResponsePromise;
  await expect.poll(async () => (await addToCartResponse.json()).success).toBe(true);
}

export async function completeStripeCheckout(page) {
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
  return orderId;
}

export async function requestRefundFromOrderHistory(page, orderId, refundReason) {
  await page.goto('/orders');
  await expect(page.getByRole('heading', { name: /order history/i })).toBeVisible();
  await expect(page.locator('body')).toContainText(orderId);

  await page.getByRole('button', { name: /request refund/i }).first().click();
  await expect(page.locator('.modal.show')).toBeVisible();

  await page.locator('.modal.show textarea[name="reason"]').fill(refundReason);
  await page.locator('.modal.show input[name="confirm_refund_fee"]').check();
  await page.locator('.modal.show button[type="submit"]').click();

  await page.waitForURL(/\/orders$/);
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Refund Requested');
  await expect(page.locator('body')).toContainText(`Reason: ${refundReason}`);
}
