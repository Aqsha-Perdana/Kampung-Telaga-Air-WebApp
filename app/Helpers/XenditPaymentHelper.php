<?php

namespace App\Helpers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

class XenditPaymentHelper
{
    public function createInvoice(array $payload): array
    {
        return $this->client()
            ->post('/v2/invoices', $payload)
            ->throw()
            ->json();
    }

    public function getInvoice(string $invoiceId): array
    {
        return $this->client()
            ->get('/v2/invoices/' . urlencode($invoiceId))
            ->throw()
            ->json();
    }

    public function listTransactions(array $query = []): array
    {
        return $this->client()
            ->get('/transactions', array_filter($query, function ($value) {
                return $value !== null && $value !== '' && $value !== [];
            }))
            ->throw()
            ->json();
    }

    public function setWebhookUrl(string $type, string $url): array
    {
        return $this->client()
            ->post('/callback_urls/' . urlencode($type), [
                'url' => $url,
            ])
            ->throw()
            ->json();
    }

    public function verifyWebhookToken(?string $token): void
    {
        $expectedToken = (string) config('services.xendit.webhook_token');

        if ($expectedToken === '') {
            return;
        }

        if (!hash_equals($expectedToken, (string) $token)) {
            throw new UnexpectedValueException('Invalid Xendit callback token.');
        }
    }

    private function client(): PendingRequest
    {
        $secretKey = (string) config('services.xendit.secret_key');

        if ($secretKey === '') {
            throw new InvalidArgumentException('Xendit is not configured for this environment yet.');
        }

        return Http::baseUrl((string) config('services.xendit.base_url'))
            ->withBasicAuth($secretKey, '')
            ->acceptJson();
    }
}
