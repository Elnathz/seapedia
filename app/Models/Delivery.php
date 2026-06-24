<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Carbon\CarbonImmutable;
use Database\Factories\DeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property ?int $driver_id
 * @property DeliveryStatus $status
 * @property ?CarbonImmutable $taken_at
 * @property ?CarbonImmutable $completed_at
 * @property int $earning_amount
 */
#[Fillable(['order_id', 'driver_id', 'status', 'taken_at', 'completed_at', 'earning_amount'])]
class Delivery extends Model
{
    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => DeliveryStatus::class,
            'taken_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'earning_amount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
