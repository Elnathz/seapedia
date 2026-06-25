<?php

namespace App\Services;

use App\Models\AppReview;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AppReviewService
{
    /**
     * @param  array{reviewer_name: string, rating: int, comment: string}  $data
     */
    public function submit(array $data, ?int $userId): AppReview
    {
        return AppReview::create([
            'user_id' => $userId,
            'reviewer_name' => $data['reviewer_name'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    public function paginated(int $perPage = 10): LengthAwarePaginator
    {
        return AppReview::query()
            ->latest()
            ->paginate($perPage);
    }

    public function recent(int $limit = 6): Collection
    {
        return AppReview::query()
            ->latest()
            ->limit($limit)
            ->get(['id', 'reviewer_name', 'rating', 'comment', 'created_at']);
    }
}
