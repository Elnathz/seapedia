<?php

namespace App\Models;

use App\Enums\WalletDirection;
use App\Enums\WalletTransactionType;
use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $wallet_id
 * @property WalletTransactionType $type
 * @property WalletDirection $direction
 * @property int $amount
 * @property int $balance_after
 * @property ?string $reference_type
 * @property ?int $reference_id
 * @property ?string $description
 */
#[Fillable([
    'wallet_id', 'type', 'direction', 'amount', 'balance_after',
    'reference_type', 'reference_id', 'description',
])]
class WalletTransaction extends Model
{
    /** @use HasFactory<WalletTransactionFactory> */
    use HasFactory;

    /**
     * Ledger entries are immutable and append-only — no updated_at column.
     */
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'direction' => WalletDirection::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
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
