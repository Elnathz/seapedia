<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(AppReviewSeeder::class);
        $this->call(DemoUserSeeder::class);
        $this->call(StoreProductSeeder::class);
        $this->call(DiscountSeeder::class);
        $this->call(BuyerDemoSeeder::class);
        $this->call(DeliverySeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }
}
