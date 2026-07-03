<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use Carbon\CarbonImmutable;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $code
 * @property int $buyer_id
 * @property int $store_id
 * @property string $ship_recipient
 * @property string $ship_phone
 * @property string $ship_address
 * @property DeliveryMethod $delivery_method
 * @property int $subtotal
 * @property int $discount_total
 * @property ?int $promo_id
 * @property ?int $voucher_id
 * @property int $delivery_fee
 * @property int $tax_amount
 * @property int $grand_total
 * @property int $seller_income_amount
 * @property OrderStatus $status
 * @property CarbonImmutable $created_sim_at
 * @property CarbonImmutable $sla_due_at
 * @property ?CarbonImmutable $paid_at
 * @property ?CarbonImmutable $refunded_at
 */
#[Fillable([
    'code', 'buyer_id', 'store_id', 'ship_recipient', 'ship_phone', 'ship_address',
    'ship_latitude', 'ship_longitude', 'delivery_method', 'subtotal', 'discount_total',
    'promo_id', 'voucher_id', 'delivery_fee', 'delivery_distance_km', 'tax_amount',
    'grand_total', 'seller_income_amount', 'status',
    'created_sim_at', 'sla_due_at', 'paid_at', 'refunded_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'delivery_method' => DeliveryMethod::class,
            'ship_latitude' => 'float',
            'ship_longitude' => 'float',
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'delivery_fee' => 'integer',
            'delivery_distance_km' => 'float',
            'tax_amount' => 'integer',
            'grand_total' => 'integer',
            'seller_income_amount' => 'integer',
            'status' => OrderStatus::class,
            'created_sim_at' => 'immutable_datetime',
            'sla_due_at' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime',
            'refunded_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<Promo, $this>
     */
    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class);
    }

    /**
     * @return BelongsTo<Voucher, $this>
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * @return HasOne<Delivery, $this>
     */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }
}
