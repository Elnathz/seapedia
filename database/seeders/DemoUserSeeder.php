<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Demo accounts for Sprint 1's Level 1 world (TDD §12 subset — stores,
     * products, orders, and discounts arrive with their own later sprints).
     * Every account's password is "password" (the factory default).
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@seapedia.test',
            'is_admin' => true,
        ]);

        $seller = User::factory()->create([
            'name' => 'Seller One',
            'username' => 'seller1',
            'email' => 'seller1@seapedia.test',
        ]);
        $this->roleService->assignRoles($seller, [RoleName::Seller->value]);

        for ($i = 2; $i <= 7; $i++) {
            $s = User::factory()->create([
                'name' => "Seller $i",
                'username' => "seller$i",
                'email' => "seller$i@seapedia.test",
            ]);
            $this->roleService->assignRoles($s, [RoleName::Seller->value]);
        }

        $buyer = User::factory()->create([
            'name' => 'Buyer One',
            'username' => 'buyer1',
            'email' => 'buyer1@seapedia.test',
        ]);
        $this->roleService->assignRoles($buyer, [RoleName::Buyer->value]);

        for ($i = 2; $i <= 3; $i++) {
            $b = User::factory()->create([
                'name' => "Buyer $i",
                'username' => "buyer$i",
                'email' => "buyer$i@seapedia.test",
            ]);
            $this->roleService->assignRoles($b, [RoleName::Buyer->value]);
        }

        $driver = User::factory()->create([
            'name' => 'Driver One',
            'username' => 'driver1',
            'email' => 'driver1@seapedia.test',
        ]);
        $this->roleService->assignRoles($driver, [RoleName::Driver->value]);

        $driver2 = User::factory()->create([
            'name' => 'Driver Two',
            'username' => 'driver2',
            'email' => 'driver2@seapedia.test',
        ]);
        $this->roleService->assignRoles($driver2, [RoleName::Driver->value]);

        $multi = User::factory()->create([
            'name' => 'Multi Role',
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
