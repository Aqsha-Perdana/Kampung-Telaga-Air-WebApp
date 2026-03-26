import { test, expect } from '@playwright/test';
import {
  loginVisitor,
  loginAdmin,
  addPackageToCart,
  completeStripeCheckout,
  requestRefundFromOrderHistory,
} from './e2e-helpers.js';

const refundReason = 'Playwright admin refund rejection flow';
const rejectionReason = 'Booking remains valid because the package date is too close to departure.';

test('admin can reject a refund request and visitor sees paid status restored', async ({ page }) => {
  test.setTimeout(420000);

  await loginVisitor(page);
  await addPackageToCart(page, '2026-04-19', 'Playwright admin refund rejection booking');
  const orderId = await completeStripeCheckout(page);
  await requestRefundFromOrderHistory(page, orderId, refundReason);

  await loginAdmin(page);
  await page.goto(`/admin/sales/${orderId}`);

  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Refund Management');
  await expect(page.locator('body')).toContainText(refundReason);

  await page.getByRole('button', { name: /reject request/i }).click();
  await expect(page.locator('#rejectRefundModal.show')).toBeVisible();
  await page.locator('#rejectRefundModal textarea[name="reason"]').fill(rejectionReason);

  const rejectResponsePromise = page.waitForResponse(response =>
    response.request().method() === 'POST' &&
    response.url().includes(`/admin/sales/${orderId}/refund/reject`)
  );
  await page.locator('#rejectRefundModal form').evaluate(form => form.requestSubmit());
  await rejectResponsePromise;

  await expect(page.locator('.alert.alert-success.alert-dismissible')).toContainText('Refund request rejected');
  await expect(page.locator('body')).toContainText('Latest Rejection Reason');
  await expect(page.locator('body')).toContainText(rejectionReason);
  await expect(page.locator('body')).toContainText('Rejected');

  await page.goto('/orders');
  await expect(page.getByRole('heading', { name: /order history/i })).toBeVisible();
  await expect(page.locator('body')).toContainText(orderId);
  await expect(page.locator('body')).toContainText('Completed');
  await expect(page.locator('body')).toContainText('Previous refund request was rejected.');
  await expect(page.locator('body')).toContainText(rejectionReason);
});

