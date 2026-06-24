<?php

namespace App\Models;

use App\Enums\PaymentGatewayType;
use App\Enums\TopupStatus;
use Carbon\CarbonImmutable;
use Database\Factories\TopupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $wallet_id
 * @property int $amount
 * @property TopupStatus $status
 * @property PaymentGatewayType $gateway
 * @property ?string $gateway_session_id
 * @property ?string $gateway_reference
 * @property ?array<string, mixed> $raw_response
 * @property ?CarbonImmutable $processed_at
 */
#[Fillable([
    'wallet_id', 'amount', 'status', 'gateway', 'gateway_session_id',
    'gateway_reference', 'raw_response', 'processed_at',
])]
class Topup extends Model
{
    /** @use HasFactory<TopupFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => TopupStatus::class,
            'gateway' => PaymentGatewayType::class,
            'raw_response' => 'array',
            'processed_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
