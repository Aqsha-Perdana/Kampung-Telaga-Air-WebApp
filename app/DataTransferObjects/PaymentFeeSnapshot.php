<?php

namespace App\DataTransferObjects;

class PaymentFeeSnapshot
{
    public readonly float $grossAmount;
    public readonly float $feeAmount;
    public readonly float $netAmount;

    public function __construct(
        public readonly string $paymentMethod,
        public readonly string $paymentChannel,
        float $grossAmount,
        float $feeAmount,
        ?float $netAmount,
        public readonly string $feeCurrency = 'MYR',
        public readonly string $feeSource = 'actual',
        public readonly mixed $paymentLogResponseData = null
    ) {
        $normalized = resolve_gateway_amounts($grossAmount, $feeAmount, $netAmount);

        $this->grossAmount = $normalized['gross_amount'];
        $this->feeAmount = $normalized['fee_amount'];
        $this->netAmount = $normalized['net_amount'];
    }

    public function toOrderAttributes(): array
    {
        return [
            'payment_channel' => $this->paymentChannel,
            'gateway_fee_amount' => $this->feeAmount,
            'gateway_fee_currency' => $this->feeCurrency,
            'gateway_net_amount' => $this->netAmount,
            'gateway_fee_source' => $this->feeSource,
        ];
    }

    public function toPaymentLogAttributes(): array
    {
        return [
            'payment_channel' => $this->paymentChannel,
            'fee_amount' => $this->feeAmount,
            'fee_currency' => $this->feeCurrency,
            'net_amount' => $this->netAmount,
            'fee_source' => $this->feeSource,
            'response_data' => $this->paymentLogResponseData,
        ];
    }

    public function toRefreshResult(): array
    {
        return [
            'fee_amount' => $this->feeAmount,
            'fee_currency' => $this->feeCurrency,
            'net_amount' => $this->netAmount,
            'payment_channel' => $this->paymentChannel,
            'fee_source' => $this->feeSource,
        ];
    }
}
