<?php

namespace App\Services\Payment;

use App\Models\Topup;
use RuntimeException;

/**
 * Real iPaymu v2 redirect call (§9.2-9.4): sign the request, POST it, store
 * `gateway_session_id`, return `Data.Url` for the buyer redirect. The real
 * HTTP call and notify webhook are deferred to Sprint 6 — they need the
 * deployed public HTTPS notify URL, which doesn't exist on local Sail.
 * Scaffolded now so the interface has both implementations; PAYMENT_GATEWAY
 * must stay `fake` until then.
 */
class IpaymuGateway implements PaymentGateway
{
    public function createTopup(Topup $topup): array
    {
        throw new RuntimeException(
            'IpaymuGateway is not yet enabled (deferred to Sprint 6). Set PAYMENT_GATEWAY=fake.'
        );
    }
}
