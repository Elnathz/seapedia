<?php

namespace Database\Seeders;

use App\Models\AppReview;
use Illuminate\Database\Seeder;

class AppReviewSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        AppReview::truncate();
        AppReview::factory()->count(6)->create();
    }
}
