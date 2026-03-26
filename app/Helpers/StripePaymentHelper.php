<?php

namespace App\Helpers;

use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentHelper
{
    public function createPaymentIntent(int $amountInCents, string $receiptEmail, array $metadata = []): PaymentIntent
    {
        $this->setApiKey();

        return PaymentIntent::create([
            'amount' => $amountInCents,
            'currency' => 'myr',
            'metadata' => $metadata,
            'receipt_email' => $receiptEmail,
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        $this->setApiKey();

        return PaymentIntent::retrieve($paymentIntentId);
    }

    public function cancelPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        $this->setApiKey();

        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        return $paymentIntent->cancel();
    }

    public function constructWebhookEvent(string $payload, ?string $signature): Event
    {
        $this->setApiKey();

        return Webhook::constructEvent(
            $payload,
            $signature ?? '',
            (string) config('services.stripe.webhook.secret')
        );
    }

    private function setApiKey(): void
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));
    }
}
