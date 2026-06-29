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
            'role' => $data['role'] ?? null,
            'comment' => $data['comment'],
        ]);
    }

    public function paginated(int $perPage = 10, ?string $role = null, ?int $rating = null): LengthAwarePaginator
    {
        return AppReview::query()
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($rating, fn ($q) => $q->where('rating', $rating))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function recent(int $limit = 6): Collection
    {
        return AppReview::query()
            ->latest()
            ->limit($limit)
            ->get(['id', 'reviewer_name', 'rating', 'role', 'comment', 'created_at']);
    }
}
