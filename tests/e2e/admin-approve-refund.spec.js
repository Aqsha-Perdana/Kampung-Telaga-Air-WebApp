import { test, expect } from '@playwright/test';
import {
  loginVisitor,
  loginAdmin,
  addPackageToCart,
  completeStripeCheckout,
  requestRefundFromOrderHistory,
} from './e2e-helpers.js';

const refundReason = 'Playwright admin refund approval flow';

test('admin can approve a refund request and visitor sees refunded status', async ({ page }) => {
  test.setTimeout(420000);

  await loginVisitor(page);
  await addPackageToCart(page, '2026-04-18', 'Playwright admin refund approval booking');
  const orderId = await completeStripeCheckout(page);
  await requestRefundFromOrderHistory(page, orderId, refundReason);

  await loginAdmin(page);
  await page.goto(`/admin/sales/${orderId}`);

  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Refund Management');
  await expect(page.locator('body')).toContainText('Customer Reason');
  await expect(page.locator('body')).toContainText(refundReason);

  await page.getByRole('button', { name: /approve refund/i }).click();
  await expect(page.locator('#approveRefundModal.show')).toBeVisible();

  const approveResponsePromise = page.waitForResponse(response =>
    response.request().method() === 'POST' &&
    response.url().includes(`/admin/sales/${orderId}/refund/approve`)
  );
  await page.locator('#approveRefundModal form').evaluate(form => form.requestSubmit());
  await approveResponsePromise;

  await expect(page.locator('.alert.alert-success.alert-dismissible')).toContainText('Refund approved');
  await expect(page.locator('body')).toContainText('Refund Completed');
  await expect(page.locator('body')).toContainText('Stripe Refund ID');
  await expect(page.locator('body')).toContainText('Succeeded');

  await page.goto('/orders');
  await expect(page.getByRole('heading', { name: /order history/i })).toBeVisible();
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Refunded');
});

