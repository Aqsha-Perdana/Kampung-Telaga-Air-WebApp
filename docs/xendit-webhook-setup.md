# Xendit Webhook Setup

This project supports a stable public URL for Xendit, separate from the local `APP_URL` used during development.

## Environment variables

Add these values to your environment:

```env
XENDIT_PUBLIC_URL=https://payments.your-domain.com
XENDIT_WEBHOOK_URL=https://payments.your-domain.com/webhook/xendit
```

Notes:

- `XENDIT_PUBLIC_URL` is used for Xendit success and failure redirects.
- `XENDIT_WEBHOOK_URL` is optional. If omitted, the app derives it from `XENDIT_PUBLIC_URL + /webhook/xendit`.
- Keep `APP_URL` pointed to your local app if you still develop locally.

## Register the webhook URL

To verify the resolved URLs without changing anything:

```bash
php artisan xendit:webhook:invoice --show-only
```

To register the invoice webhook URL with Xendit:

```bash
php artisan xendit:webhook:invoice
```

Or override the URL directly:

```bash
php artisan xendit:webhook:invoice --url=https://payments.your-domain.com/webhook/xendit
```

## Recommended setup

For better stability, use a fixed HTTPS domain or tunnel with a reserved hostname instead of a temporary `ngrok` URL.

- Production: use your real HTTPS domain.
- Staging: use a stable subdomain such as `payments-staging.your-domain.com`.
- Local development: avoid depending on temporary callback URLs for normal testing if you can route Xendit callbacks through a stable staging endpoint.
