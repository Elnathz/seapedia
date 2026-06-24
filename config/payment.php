<?php

return [
    /*
     * Which PaymentGateway implementation handles wallet top-ups.
     * `fake` instantly credits the wallet (used in local/dev/demo so the
     * 20-pt checkout flow never depends on iPaymu uptime). `ipaymu` is
     * scaffolded but its real v2 call is deferred to Sprint 6.
     */
    'gateway' => env('PAYMENT_GATEWAY', 'fake'),

    'ipaymu' => [
        'va' => env('IPAYMU_VA'),
        'api_key' => env('IPAYMU_API_KEY'),
        'mode' => env('IPAYMU_MODE', 'sandbox'),
        'return_url' => env('IPAYMU_RETURN_URL'),
        'cancel_url' => env('IPAYMU_CANCEL_URL'),
        'notify_url' => env('IPAYMU_NOTIFY_URL'),
    ],

    'topup' => [
        // No minimum is locked by the TDD; 10,000 IDR is the simplest
        // reasonable floor, documented in the README (§ when unsure).
        'min_amount' => 10_000,
    ],
];
