import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 120000,
  expect: {
    timeout: 15000,
  },
  use: {
    baseURL: 'http://127.0.0.1:8010',
    headless: true,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
  },
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8010',
    url: 'http://127.0.0.1:8010',
    reuseExistingServer: true,
    timeout: 120000,
  },
});
