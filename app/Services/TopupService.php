<?php

namespace App\Services;

use App\Enums\PaymentGatewayType;
use App\Enums\TopupStatus;
use App\Models\Topup;
use App\Models\User;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Str;

class TopupService
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    /**
     * Create a pending top-up and hand it to the configured gateway
     * (§9.3). `FakeGateway` credits the wallet synchronously; `IpaymuGateway`
     * would return a redirect URL (Sprint 6).
     */
    public function create(User $user, int $amount): Topup
    {
        $topup = Topup::create([
            'wallet_id' => $user->wallet->id,
            'amount' => $amount,
            'status' => TopupStatus::Pending,
            'gateway' => PaymentGatewayType::from(config('payment.gateway')),
            'gateway_reference' => (string) Str::uuid(),
        ]);

        $this->gateway->createTopup($topup);

        return $topup->refresh();
    }
}
