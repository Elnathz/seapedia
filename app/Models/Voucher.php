<?php

namespace App\Models;

use App\Enums\DiscountType;
use Carbon\CarbonImmutable;
use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property DiscountType $type
 * @property int $value
 * @property ?int $max_discount
 * @property ?int $min_spend
 * @property CarbonImmutable $expiry_date
 * @property int $usage_limit
 * @property int $used_count
 * @property bool $is_active
 */
#[Fillable([
    'code', 'type', 'value', 'max_discount', 'min_spend', 'expiry_date',
    'usage_limit', 'used_count', 'is_active',
])]
class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'integer',
            'max_discount' => 'integer',
            'min_spend' => 'integer',
            'expiry_date' => 'immutable_datetime',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function remainingUsage(): int
    {
        return max(0, $this->usage_limit - $this->used_count);
    }
}
