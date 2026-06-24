<?php

namespace App\Models;

use App\Enums\DiscountType;
use Carbon\CarbonImmutable;
use Database\Factories\PromoFactory;
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
 * @property bool $is_active
 */
#[Fillable(['code', 'type', 'value', 'max_discount', 'min_spend', 'expiry_date', 'is_active'])]
class Promo extends Model
{
    /** @use HasFactory<PromoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'integer',
            'max_discount' => 'integer',
            'min_spend' => 'integer',
            'expiry_date' => 'immutable_datetime',
            'is_active' => 'boolean',
        ];
    }
}
