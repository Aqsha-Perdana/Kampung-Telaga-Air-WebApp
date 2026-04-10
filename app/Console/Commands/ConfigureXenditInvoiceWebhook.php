<?php

namespace App\Console\Commands;

use App\Helpers\XenditPaymentHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ConfigureXenditInvoiceWebhook extends Command
{
    protected $signature = 'xendit:webhook:invoice
                            {--url= : Override the invoice webhook URL}
                            {--show-only : Only display the resolved configuration without calling Xendit}';

    protected $description = 'Show or register the stable invoice webhook URL used by Xendit.';

    public function __construct(private readonly XenditPaymentHelper $xenditPaymentHelper)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $webhookUrl = $this->resolveWebhookUrl();
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $publicUrl = rtrim((string) config('services.xendit.public_url', config('app.url')), '/');

        $this->components->info('Resolved Xendit URLs');
        $this->line('Public URL   : ' . $publicUrl);
        $this->line('Webhook URL  : ' . $webhookUrl);
        $this->line('Success URL  : ' . $publicUrl . route('checkout.success', [], false));
        $this->line('Failure URL  : ' . $publicUrl . route('checkout.failed', [], false));

        if (!Str::startsWith($webhookUrl, 'https://')) {
            $this->warn('Webhook URL is not HTTPS. Xendit webhooks are more reliable when served from a stable HTTPS domain.');
        }

        if ($this->option('show-only')) {
            $this->newLine();
            $this->comment('No API call was made. Use this output to verify your public URL configuration.');

            return self::SUCCESS;
        }

        try {
            $response = $this->xenditPaymentHelper->setWebhookUrl('invoice', $webhookUrl);
        } catch (\Throwable $e) {
            $this->error('Failed to register invoice webhook URL with Xendit: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('Xendit invoice webhook registered successfully.');
        $this->line('Environment  : ' . (string) ($response['environment'] ?? '-'));
        $this->line('Saved URL    : ' . (string) ($response['url'] ?? $webhookUrl));

        $callbackToken = (string) ($response['callback_token'] ?? '');
        if ($callbackToken !== '') {
            $this->line('Callback Token: ' . $callbackToken);

            if ($callbackToken !== (string) config('services.xendit.webhook_token')) {
                $this->warn('Your XENDIT_WEBHOOK_TOKEN in .env does not match the token returned by Xendit. Update it before going live.');
            }
        }

        return self::SUCCESS;
    }

    private function resolveWebhookUrl(): string
    {
        $override = trim((string) $this->option('url'));
        if ($override !== '') {
            return $this->normalizeAbsoluteUrl($override);
        }

        $configuredWebhookUrl = trim((string) config('services.xendit.webhook_url'));
        if ($configuredWebhookUrl !== '') {
            return $this->normalizeAbsoluteUrl($configuredWebhookUrl);
        }

        $publicUrl = trim((string) config('services.xendit.public_url', config('app.url')));
        if ($publicUrl === '') {
            throw new InvalidArgumentException('Set XENDIT_PUBLIC_URL or provide --url to resolve a stable webhook endpoint.');
        }

        return rtrim($this->normalizeAbsoluteUrl($publicUrl), '/') . route('webhook.xendit', [], false);
    }

    private function normalizeAbsoluteUrl(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('The resolved Xendit URL is invalid: ' . $url);
        }

        return rtrim($url, '/');
    }
}
