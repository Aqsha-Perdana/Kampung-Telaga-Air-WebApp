<?php

if (!function_exists('format_ringgit')) {
    function format_ringgit($amount, $showSymbol = true)
    {
        $formatted = number_format($amount, 2);
        return $showSymbol ? 'RM ' . $formatted : $formatted;
    }
}

if (!function_exists('payment_method_label')) {
    function payment_method_label(?string $method): string
    {
        $config = config('payment.methods.' . $method);

        if (is_array($config) && !empty($config['label'])) {
            return (string) $config['label'];
        }

        return strtoupper(str_replace('_', ' ', (string) $method));
    }
}

if (!function_exists('payment_method_icon')) {
    function payment_method_icon(?string $method): string
    {
        $config = config('payment.methods.' . $method);

        if (is_array($config) && !empty($config['icon'])) {
            return (string) $config['icon'];
        }

        return 'wallet2';
    }
}

if (!function_exists('payment_channel_label')) {
    function payment_channel_label(?string $channel): string
    {
        $channel = trim((string) $channel);
        $normalized = strtoupper($channel);

        if ($normalized === '') {
            return '-';
        }

        $labels = [
            'SHOPEEPAY' => 'ShopeePay',
            'GRABPAY' => 'GrabPay',
            'WECHATPAY' => 'WeChat Pay',
            'TOUCHNGO' => "Touch 'n Go",
            'ALIPAY' => 'Alipay',
            'DD_DUITNOW_PAY' => 'DuitNow Pay',
            'EWALLET' => 'eWallet',
            'CARD' => 'Card',
        ];

        if (isset($labels[$normalized])) {
            return $labels[$normalized];
        }

        if (str_starts_with(strtolower($channel), 'card_')) {
            $brand = substr($channel, 5);

            return strtoupper($brand) === 'AMEX'
                ? 'American Express'
                : ucwords(str_replace(['_', '-'], ' ', $brand));
        }

        if (str_starts_with($normalized, 'DD_') && str_ends_with($normalized, '_FPX')) {
            return str_replace(' Fpx', ' FPX', ucwords(strtolower(str_replace('DD_', '', $normalized)), '_'));
        }

        return ucwords(strtolower(str_replace(['_', '-'], ' ', $channel)));
    }
}

if (!function_exists('payment_descriptor')) {
    function payment_descriptor(?string $method, ?string $channel = null): string
    {
        $methodLabel = payment_method_label($method);
        $channelLabel = payment_channel_label($channel);

        if ($channelLabel === '-' || strcasecmp($methodLabel, $channelLabel) === 0) {
            return $methodLabel;
        }

        return $methodLabel . ' - ' . $channelLabel;
    }
}

if (!function_exists('gateway_fee_source_label')) {
    function gateway_fee_source_label(?string $source): string
    {
        return match (strtolower(trim((string) $source))) {
            'actual' => 'Actual',
            'estimated' => 'Estimated',
            default => 'Unknown',
        };
    }
}

if (!function_exists('gateway_fee_source_badge_class')) {
    function gateway_fee_source_badge_class(?string $source): string
    {
        return match (strtolower(trim((string) $source))) {
            'actual' => 'bg-success-subtle text-success',
            'estimated' => 'bg-warning-subtle text-warning',
            default => 'bg-secondary-subtle text-secondary',
        };
    }
}

if (!function_exists('resolve_gateway_amounts')) {
    function resolve_gateway_amounts($grossAmount, $feeAmount = null, $netAmount = null): array
    {
        $gross = round(max(0, (float) ($grossAmount ?? 0)), 2);
        $fee = round(max(0, (float) ($feeAmount ?? 0)), 2);
        $fee = min($fee, $gross);

        $expectedNet = round(max(0, $gross - $fee), 2);
        $storedNet = ($netAmount === null || $netAmount === '')
            ? null
            : round(max(0, (float) $netAmount), 2);

        $net = $storedNet;
        $mismatchResolved = false;

        if ($storedNet === null || abs($storedNet - $expectedNet) > 0.009) {
            $net = $expectedNet;
            $mismatchResolved = $storedNet !== null;
        }

        return [
            'gross_amount' => $gross,
            'fee_amount' => $fee,
            'net_amount' => $net,
            'mismatch_resolved' => $mismatchResolved,
        ];
    }
}

if (!function_exists('payment_fee_config')) {
    function payment_fee_config(?string $method = null): array
    {
        $resolvedMethod = $method ?: config('payment.default', 'stripe');
        $fees = config('payment.methods.' . $resolvedMethod . '.fees', []);

        return [
            'percentage' => (float) ($fees['percentage'] ?? 0),
            'fixed' => (float) ($fees['fixed'] ?? 0),
        ];
    }
}

if (!function_exists('payment_fee_label')) {
    function payment_fee_label(?string $method = null): string
    {
        $resolvedMethod = $method ?: config('payment.default', 'stripe');
        $fees = payment_fee_config($resolvedMethod);

        return number_format($fees['percentage'] * 100, 1) . '% + RM ' . number_format($fees['fixed'], 2);
    }
}

if (!function_exists('package_fee_buffer_percentage')) {
    function package_fee_buffer_percentage(): float
    {
        return (float) config('payment.pricing.fee_buffer_percentage', 0.03);
    }
}

if (!function_exists('package_fee_buffer_label')) {
    function package_fee_buffer_label(): string
    {
        return number_format(package_fee_buffer_percentage() * 100, 1) . '% of package cost';
    }
}
