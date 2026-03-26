import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import {
  loginVisitor,
  loginAdmin,
  addPackageToCart,
  completeStripeCheckout,
  requestRefundFromOrderHistory,
} from './e2e-helpers.js';

function formatRM(amount) {
  return `RM ${Number(amount).toFixed(2)}`;
}

function getSnapshot(orderId) {
  const raw = execFileSync('php', ['tests/e2e/get_order_financial_snapshot.php', orderId], {
    encoding: 'utf8',
  });

  return JSON.parse(raw.trim());
}

test('paid order shows snapshot-consistent figures in sales detail and financial reports', async ({ page }) => {
  test.setTimeout(420000);

  await loginVisitor(page);
  await addPackageToCart(page, '2026-04-21', 'Paid financial UI verification');
  const orderId = await completeStripeCheckout(page);
  const snapshot = getSnapshot(orderId);

  await loginAdmin(page);
  await page.goto(`/admin/sales/${orderId}`);

  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText(formatRM(snapshot.base_amount));
  await expect(page.locator('body')).toContainText(formatRM(snapshot.vendor_total));
  await expect(page.locator('body')).toContainText(formatRM(snapshot.original_profit));

  const today = new Date().toISOString().slice(0, 10);
  await page.goto(`/admin/financial-reports?start_date=${today}&end_date=${today}`);
  await expect(page.locator('body')).toContainText('Detailed Transaction Impact');
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText(formatRM(snapshot.base_amount));
  await expect(page.locator('body')).toContainText(formatRM(snapshot.vendor_total));
  await expect(page.locator('body')).toContainText(formatRM(snapshot.reported_profit_impact));
});

test('refunded order shows snapshot and report-impact figures correctly in admin UI', async ({ page }) => {
  test.setTimeout(480000);
  const refundReason = 'Refunded financial UI verification';

  await loginVisitor(page);
  await addPackageToCart(page, '2026-04-22', 'Refunded financial UI verification');
  const orderId = await completeStripeCheckout(page);
  await requestRefundFromOrderHistory(page, orderId, refundReason);

  await loginAdmin(page);
  await page.goto(`/admin/sales/${orderId}`);
  await page.getByRole('button', { name: /approve refund/i }).click();
  await expect(page.locator('#approveRefundModal.show')).toBeVisible();

  const approveResponsePromise = page.waitForResponse(response =>
    response.request().method() === 'POST' &&
    response.url().includes(`/admin/sales/${orderId}/refund/approve`)
  );
  await page.locator('#approveRefundModal form').evaluate(form => form.requestSubmit());
  await approveResponsePromise;
  await expect(page.locator('body')).toContainText('Refund Completed');

  const snapshot = getSnapshot(orderId);

  await expect(page.locator('body')).toContainText(formatRM(snapshot.base_amount));
  await expect(page.locator('body')).toContainText(formatRM(snapshot.vendor_total));
  await expect(page.locator('body')).toContainText(formatRM(snapshot.original_profit));
  await expect(page.locator('body')).toContainText('Current Reported Profit Impact');
  await expect(page.locator('body')).toContainText(formatRM(snapshot.reported_profit_impact));

  const today = new Date().toISOString().slice(0, 10);
  await page.goto(`/admin/financial-reports?start_date=${today}&end_date=${today}`);
  await expect(page.locator('body')).toContainText('Detailed Transaction Impact');
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Refunded');
  await expect(page.locator('body')).toContainText(formatRM(0));
  await expect(page.locator('body')).toContainText(formatRM(snapshot.reported_profit_impact));
});