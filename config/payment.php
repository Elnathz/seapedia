<?php

return [
    /*
     * Which PaymentGateway implementation handles wallet top-ups. `fake` is
     * the only one wired up (Sprint 6 decision: the spec scores top-up as
     * "dummy" and awards zero points for a real gateway, so no real one is
     * built — see PaymentGateway's docblock for the seam it would plug into).
     */
    'gateway' => env('PAYMENT_GATEWAY', 'fake'),

    'topup' => [
        // No bounds are locked by the spec (top-up is a dummy flow). 5,000 IDR
        // floor and 100,000,000 IDR ceiling per transaction, documented in the
        // README (§ when unsure).
        'min_amount' => 5_000,

        // Ceiling for a single top-up — guards against an absurd one-shot credit.
        'max_amount' => 100_000_000,

        // How long FakeGateway::checkStatus() keeps a top-up "processing"
        // before resolving it to paid — long enough to read as a real
        // gateway round trip on the polling UI, short enough not to stall
        // the demo.
        'processing_seconds' => env('TOPUP_PROCESSING_SECONDS', 3),
    ],
];
