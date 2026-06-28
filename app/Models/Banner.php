<?php

namespace App\Models;

use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $placement
 * @property string $image_path
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $badge_label
 * @property string|null $cta_label
 * @property string|null $cta_url
 * @property int $sort_order
 * @property bool $is_active
 */
#[Fillable(['placement', 'image_path', 'title', 'subtitle', 'badge_label', 'cta_label', 'cta_url', 'sort_order', 'is_active'])]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @param Builder<Banner> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
