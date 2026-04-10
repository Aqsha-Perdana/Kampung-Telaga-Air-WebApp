<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentGatewayService
{
    public function __construct(
        private readonly StripeOrderService $stripeOrderService,
        private readonly XenditOrderService $xenditOrderService
    ) {
    }

    public function createPayment(Order $order, array $context = []): array
    {
        return $this->driver((string) $order->payment_method)->createPayment($order, $context);
    }

    public function syncPendingOrder(Order $order): void
    {
        $this->driver((string) $order->payment_method)->syncPendingOrder($order);
    }

    public function handleWebhook(string $paymentMethod, Request $request): void
    {
        $this->driver($paymentMethod)->handleWebhook($request);
    }

    public function cancelPendingPayment(Order $order): bool
    {
        return $this->driver((string) $order->payment_method)->cancelPendingPayment($order);
    }

    private function driver(string $paymentMethod): StripeOrderService|XenditOrderService
    {
        $paymentMethod = trim($paymentMethod) !== '' ? $paymentMethod : (string) config('payment.default', 'stripe');

        return match ($paymentMethod) {
            'stripe' => $this->stripeOrderService,
            'xendit' => $this->xenditOrderService,
            default => throw new InvalidArgumentException('Unsupported payment method: ' . $paymentMethod),
        };
    }
}
