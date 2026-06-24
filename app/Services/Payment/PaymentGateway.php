<?php

namespace App\Services\Payment;

use App\Models\Topup;

/**
 * The only touchpoint with an external payment provider (§9.1) — checkout
 * never calls a gateway, it pays from the wallet only.
 */
interface PaymentGateway
{
    /**
     * Drive a pending top-up to completion. `FakeGateway` credits the
     * wallet immediately; `IpaymuGateway` returns a redirect URL and the
     * wallet is credited later by the notify webhook (Sprint 6).
     *
     * @return array{redirect_url?: string}
     */
    public function createTopup(Topup $topup): array;
}
