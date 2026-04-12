<?php

return [
    'default' => env('DEFAULT_PAYMENT_METHOD', 'stripe'),

    'pricing' => [
        'fee_buffer_percentage' => (float) env('PACKAGE_FEE_BUFFER_PERCENTAGE', 0.03),
    ],

    'methods' => [
        'stripe' => [
            'label' => 'Credit/Debit Card (Stripe)',
            'short_label' => 'Stripe',
            'icon' => 'credit-card',
            'fees' => [
                'percentage' => (float) env('STRIPE_FEE_PERCENTAGE', 0.035),
                'fixed' => (float) env('STRIPE_FEE_FIXED', 2.0),
            ],
        ],
        'xendit' => [
            'label' => 'Xendit Checkout',
            'short_label' => 'Xendit',
            'icon' => 'wallet2',
            'fees' => [
                'percentage' => (float) env('XENDIT_FEE_PERCENTAGE', 0.0),
                'fixed' => (float) env('XENDIT_FEE_FIXED', 0.0),
            ],
            'reporting_fee_rules' => [
                'default' => [
                    'percentage' => (float) env('XENDIT_REPORTING_DEFAULT_PERCENTAGE', 0.0),
                    'fixed' => (float) env('XENDIT_REPORTING_DEFAULT_FIXED', 0.0),
                    'minimum' => (float) env('XENDIT_REPORTING_DEFAULT_MINIMUM', 0.0),
                ],
                'channels' => [
                    'GRABPAY' => [
                        'percentage' => (float) env('XENDIT_GRABPAY_FEE_PERCENTAGE', 0.013),
                        'fixed' => 0.0,
                        'minimum' => 0.0,
                    ],
                    'SHOPEEPAY' => [
                        'percentage' => (float) env('XENDIT_SHOPEEPAY_FEE_PERCENTAGE', 0.013),
                        'fixed' => 0.0,
                        'minimum' => 0.0,
                    ],
                    'WECHATPAY' => [
                        'percentage' => (float) env('XENDIT_WECHATPAY_FEE_PERCENTAGE', 0.013),
                        'fixed' => 0.0,
                        'minimum' => 0.0,
                    ],
                    'DD_DUITNOW_PAY' => [
                        'percentage' => (float) env('XENDIT_DUITNOW_PAY_FEE_PERCENTAGE', 0.005),
                        'fixed' => (float) env('XENDIT_DUITNOW_PAY_FEE_FIXED', 0.0),
                        'minimum' => (float) env('XENDIT_DUITNOW_PAY_FEE_MINIMUM', 0.50),
                    ],
                ],
            ],
            'refund_rules' => [
                'default' => [
                    'enabled' => false,
                    'partial_refund' => false,
                    'validity_days' => 0,
                ],
                'channels' => [
                    'MY_TOUCHNGO' => [
                        'enabled' => true,
                        'partial_refund' => true,
                        'validity_days' => 30,
                    ],
                    'TOUCHNGO' => [
                        'enabled' => true,
                        'partial_refund' => true,
                        'validity_days' => 30,
                    ],
                    'MY_SHOPEEPAY' => [
                        'enabled' => true,
                        'partial_refund' => true,
                        'validity_days' => 365,
                    ],
                    'SHOPEEPAY' => [
                        'enabled' => true,
                        'partial_refund' => true,
                        'validity_days' => 365,
                    ],
                    'MY_GRABPAY' => [
                        'enabled' => true,
                        'partial_refund' => true,
                        'validity_days' => 365,
                    ],
                    'GRABPAY' => [
                        'enabled' => true,
                        'partial_refund' => true,
                        'validity_days' => 365,
                    ],
                    'MY_WECHATPAY' => [
                        'enabled' => true,
                        'partial_refund' => false,
                        'validity_days' => 365,
                    ],
                    'WECHATPAY' => [
                        'enabled' => true,
                        'partial_refund' => false,
                        'validity_days' => 365,
                    ],
                ],
            ],
            'invoice_duration' => (int) env('XENDIT_INVOICE_DURATION', 3600),
        ],
    ],
];
