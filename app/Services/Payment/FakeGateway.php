<?php

namespace App\Services\Payment;

use App\Enums\TopupStatus;
use App\Enums\WalletTransactionType;
use App\Models\Topup;
use App\Services\ClockService;
use App\Services\WalletService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Simulates a payment-gateway round trip without calling out anywhere
 * (§9.2, Sprint 6: no real gateway — the spec scores top-up as "dummy").
 * `createTopup()` only records the attempt; `checkStatus()` is polled by
 * the buyer's processing page and resolves to paid once a short, real-time
 * "processing" window has elapsed — long enough to read as a gateway call,
 * short enough not to stall the demo.
 */
class FakeGateway implements PaymentGateway
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly ClockService $clock,
    ) {}

    public function createTopup(Topup $topup): array
    {
        return [];
    }

    public function checkStatus(Topup $topup): Topup
    {
        return DB::transaction(function () use ($topup) {
            $locked = Topup::query()->lockForUpdate()->findOrFail($topup->id);

            // Idempotent (rule 15): once resolved, every later poll is a
            // no-op — never a double credit.
            if ($locked->processed_at !== null) {
                return $locked;
            }

            // The "processing" delay is real-world UX latency (how long a
            // gateway call would plausibly take), not business time — it is
            // measured against `created_at`, which Eloquent always stamps
            // with the real wall clock. `ClockService::now()` (the
            // simulated day-clock, §5.7) would be incomparable here, so
            // this is a deliberate, narrow exception to golden rule 6.
            $dueAt = CarbonImmutable::parse($locked->created_at)
                ->addSeconds((int) config('payment.topup.processing_seconds'));

            if (CarbonImmutable::now()->lessThan($dueAt)) {
                return $locked;
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

            return $locked->refresh();
        });
    }
}
