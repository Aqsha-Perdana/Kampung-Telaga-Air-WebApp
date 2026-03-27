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
