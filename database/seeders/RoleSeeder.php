<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (RoleName::cases() as $role) {
            Role::query()->firstOrCreate(['name' => $role->value]);
        }
    }
}
