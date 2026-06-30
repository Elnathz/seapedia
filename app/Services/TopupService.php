<?php

namespace App\Services;

use App\Enums\PaymentGatewayType;
use App\Enums\TopupStatus;
use App\Models\Topup;
use App\Models\User;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TopupService
{
    public const int MAX_BALANCE = 1_000_000_000;
    
    public function __construct(private readonly PaymentGateway $gateway) {}

    /**
     * Create a pending top-up and kick it off at the gateway (§9.3). Stays
     * `pending` until the buyer's processing page polls `checkStatus()`.
     */
    public function create(User $user, int $amount): Topup
    {
        return DB::transaction(function () use ($user, $amount) {
            $wallet = \App\Models\Wallet::query()->lockForUpdate()->findOrFail($user->wallet->id);

            if ($wallet->balance + $amount > self::MAX_BALANCE) {
                throw ValidationException::withMessages([
                    'amount' => ['Saldo maksimal dompet adalah Rp 1.000.000.000.'],
                ]);
            }

            $topup = Topup::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'status' => TopupStatus::Pending,
                'gateway' => PaymentGatewayType::from(config('payment.gateway')),
                'gateway_reference' => (string) Str::uuid(),
            ]);

            $this->gateway->createTopup($topup);

            return $topup;
        });
    }


    /**
     * Poll the gateway for the current state, resolving (and crediting)
     * the top-up if it's due. Called every time the processing page loads
     * or refreshes — idempotent, so polling never double-credits.
     */
    public function checkStatus(Topup $topup): Topup
    {
        return $this->gateway->checkStatus($topup);
    }
}
