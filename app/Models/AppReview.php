<?php

namespace App\Models;

use Database\Factories\AppReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $reviewer_name
 * @property int $rating
 * @property string $comment
 */
#[Fillable(['user_id', 'reviewer_name', 'rating', 'comment'])]
class AppReview extends Model
{
    /** @use HasFactory<AppReviewFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
