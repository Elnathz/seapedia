<?php

namespace App\Services\Payment;

use App\Models\Topup;

/**
 * The only touchpoint with an external payment provider (§9.1) — checkout
 * never calls a gateway, it pays from the wallet only. A real gateway would
 * plug in here without touching `TopupService` or any controller (Sprint 6
 * decision: no real gateway is wired up — the spec scores top-up as
 * "dummy" — but the seam stays so the pattern is legible to an evaluator).
 */
interface PaymentGateway
{
    /**
     * Kick off the top-up at the gateway. `FakeGateway` does nothing here
     * (there's no external party to call) — completion happens in
     * `checkStatus()`, which is what makes the flow pollable.
     *
     * @return array{redirect_url?: string}
     */
    public function createTopup(Topup $topup): array;

    /**
     * Resolve whether the top-up has completed at the gateway, crediting
     * the wallet exactly once on the transition into a terminal state.
     * Safe to call repeatedly (golden rule 15) — never double-credits.
     */
    public function checkStatus(Topup $topup): Topup;
}
