<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Setting::query()->firstOrCreate(
            ['key' => 'simulated_now'],
            ['value' => null],
        );
    }
}
