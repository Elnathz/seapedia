<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $logo_path
 * @property string|null $description
 * @property string|null $full_address
 * @property string|null $province
 * @property string|null $city
 * @property string|null $district
 * @property string|null $village
 * @property string|null $postal_code
 * @property float|null $origin_latitude
 * @property float|null $origin_longitude
 * @property bool $is_active
 */
#[Fillable(['user_id', 'name', 'slug', 'logo_path', 'description', 'full_address', 'province', 'city', 'district', 'village', 'postal_code', 'origin_latitude', 'origin_longitude', 'is_active'])]
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'origin_latitude' => 'float',
            'origin_longitude' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
