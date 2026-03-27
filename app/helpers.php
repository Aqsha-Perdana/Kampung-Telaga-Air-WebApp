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
