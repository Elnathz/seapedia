<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DemoUserSeeder extends Seeder
{
    public function __construct(private readonly RoleService $roleService) {}

    private function createDemoUser(array $attributes)
    {
        return User::firstWhere('email', $attributes['email']) ?? User::factory()->create($attributes);
    }

    /**
     * Demo accounts for Sprint 1's Level 1 world (TDD §12 subset — stores,
     * products, orders, and discounts arrive with their own later sprints).
     * Every account's password is "password" (the factory default).
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $this->createDemoUser([
            'name' => 'Budi Santoso (Admin)',
            'username' => 'admin',
            'email' => 'admin@seapedia.test',
            'is_admin' => true,
        ]);

        $seller = $this->createDemoUser([
            'name' => $faker->name,
            'username' => 'seller1',
            'email' => 'seller1@seapedia.test',
        ]);
        $this->roleService->assignRoles($seller, [RoleName::Seller->value]);

        for ($i = 2; $i <= 7; $i++) {
            $s = $this->createDemoUser([
                'name' => $faker->name,
                'username' => "seller$i",
                'email' => "seller$i@seapedia.test",
            ]);
            $this->roleService->assignRoles($s, [RoleName::Seller->value]);
        }

        $buyer = $this->createDemoUser([
            'name' => $faker->name,
            'username' => 'buyer1',
            'email' => 'buyer1@seapedia.test',
        ]);
        $this->roleService->assignRoles($buyer, [RoleName::Buyer->value]);

        for ($i = 2; $i <= 3; $i++) {
            $b = $this->createDemoUser([
                'name' => $faker->name,
                'username' => "buyer$i",
                'email' => "buyer$i@seapedia.test",
            ]);
            $this->roleService->assignRoles($b, [RoleName::Buyer->value]);
        }

        $driver = $this->createDemoUser([
            'name' => $faker->name,
            'username' => 'driver1',
            'email' => 'driver1@seapedia.test',
        ]);
        $this->roleService->assignRoles($driver, [RoleName::Driver->value]);

        $driver2 = $this->createDemoUser([
            'name' => $faker->name,
            'username' => 'driver2',
            'email' => 'driver2@seapedia.test',
        ]);
        $this->roleService->assignRoles($driver2, [RoleName::Driver->value]);

        $multi = $this->createDemoUser([
            'name' => $faker->name,
            'username' => 'multi1',
            'email' => 'multi1@seapedia.test',
        ]);
        $this->roleService->assignRoles($multi, [
            RoleName::Buyer->value,
            RoleName::Seller->value,
            RoleName::Driver->value,
        ]);
    }
}
