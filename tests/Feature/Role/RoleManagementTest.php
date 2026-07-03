<?php

namespace Tests\Feature\Role;

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<RoleName>  $roles
     */
    private function userWithRoles(array $roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::factory()->create(['name' => $role->value])->id);
        }

        return $user;
    }

    public function test_cannot_remove_last_role(): void
    {
        $user = $this->userWithRoles([RoleName::Buyer]);

        $this->actingAs($user)
            ->delete(route('role.destroy', ['role' => RoleName::Buyer->value]))
            ->assertSessionHasErrors('role');

        $this->assertTrue($user->fresh()->hasRole(RoleName::Buyer));
    }

    public function test_removing_seller_role_soft_hides_store_and_keeps_orders(): void
    {
        $user = $this->userWithRoles([RoleName::Buyer, RoleName::Seller]);
        $store = Store::factory()->create(['user_id' => $user->id]);
        $order = Order::factory()->create([
            'store_id' => $store->id,
            'status' => OrderStatus::PesananSelesai,
        ]);

        $this->actingAs($user)
            ->delete(route('role.destroy', ['role' => RoleName::Seller->value]))
            ->assertRedirect(route('role.select'));

        $this->assertFalse($user->fresh()->hasRole(RoleName::Seller));
        $this->assertSoftDeleted('stores', ['id' => $store->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_cannot_remove_seller_role_with_active_orders(): void
    {
        $user = $this->userWithRoles([RoleName::Buyer, RoleName::Seller]);
        $store = Store::factory()->create(['user_id' => $user->id]);
        Order::factory()->create([
            'store_id' => $store->id,
            'status' => OrderStatus::MenungguPengirim,
        ]);

        $this->actingAs($user)
            ->delete(route('role.destroy', ['role' => RoleName::Seller->value]))
            ->assertSessionHasErrors('role');

        $this->assertTrue($user->fresh()->hasRole(RoleName::Seller));
        $this->assertNull($store->fresh()->deleted_at);
    }

    public function test_add_role_attaches_a_role_not_owned(): void
    {
        $user = $this->userWithRoles([RoleName::Buyer]);
        Role::factory()->create(['name' => RoleName::Driver->value]);

        $this->actingAs($user)
            ->post(route('role.add'), ['role' => RoleName::Driver->value])
            ->assertRedirect(route('role.select'));

        $this->assertTrue($user->fresh()->hasRole(RoleName::Driver));
    }

    public function test_cannot_add_a_role_already_owned(): void
    {
        $user = $this->userWithRoles([RoleName::Buyer]);

        $this->actingAs($user)
            ->post(route('role.add'), ['role' => RoleName::Buyer->value])
            ->assertSessionHasErrors('role');
    }

    public function test_re_adding_seller_restores_the_soft_hidden_store(): void
    {
        $user = $this->userWithRoles([RoleName::Buyer, RoleName::Seller]);
        $store = Store::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('role.destroy', ['role' => RoleName::Seller->value]));
        $this->assertSoftDeleted('stores', ['id' => $store->id]);

        // Re-authenticate with a fresh instance so the request re-resolves
        // roles from the DB (as it would in production) rather than a stale cache.
        $this->actingAs($user->fresh())->post(route('role.add'), ['role' => RoleName::Seller->value]);

        $this->assertTrue($user->fresh()->hasRole(RoleName::Seller));
        $this->assertNull($store->fresh()->deleted_at);
    }
}
