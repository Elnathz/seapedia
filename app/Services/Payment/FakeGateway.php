<?php

namespace App\Services\Payment;

use App\Enums\TopupStatus;
use App\Enums\WalletTransactionType;
use App\Models\Topup;
use App\Services\ClockService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

/**
 * Instantly marks the top-up paid and credits the wallet (§9.2) — used in
 * local/dev/demo via PAYMENT_GATEWAY=fake so the 20-pt checkout flow never
 * depends on iPaymu uptime.
 */
class FakeGateway implements PaymentGateway
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly ClockService $clock,
    ) {}

    public function createTopup(Topup $topup): array
    {
        DB::transaction(function () use ($topup) {
            $locked = Topup::query()->lockForUpdate()->findOrFail($topup->id);

            // Idempotent (rule 15): a replayed call on an already-processed
            // top-up is a no-op, never a double credit.
            if ($locked->processed_at !== null) {
                return;
            }

            $this->wallets->credit(
                $locked->wallet,
                $locked->amount,
                WalletTransactionType::Topup,
                'topup',
                $locked->id,
            );

            $locked->update([
                'status' => TopupStatus::Paid,
                'processed_at' => $this->clock->now(),
            ]);
        });

        return [];
    }
}
