<?php

namespace Tests\Feature\Cart;

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * §MULTI-ROLE CONFLICT GUARD 1 — buyer == seller
 *
 * These tests verify that a multi-role user (buyer + seller) cannot purchase
 * products from a store they own, regardless of whether the request comes
 * through the UI or directly via the API.
 */
class SelfStorePurchaseGuardTest extends TestCase
{
    use RefreshDatabase;

    /** Creates a user who owns both Buyer and Seller roles. */
    private function multiRoleUser(): User
    {
        $user = User::factory()->create();
        $buyer = Role::query()->firstOrCreate(['name' => RoleName::Buyer->value]);
        $seller = Role::query()->firstOrCreate(['name' => RoleName::Seller->value]);
        $user->roles()->attach([$buyer->id, $seller->id]);

        return $user;
    }

    private function actingAsBuyer(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value]);
    }

    /** A user acting as Buyer CANNOT add a product from their own store to cart. */
    public function test_buyer_cannot_add_own_store_product_to_cart(): void
    {
        $user = $this->multiRoleUser();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000]);

        $response = $this->actingAsBuyer($user)->post(route('buyer.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Must get a validation error on the 'product' field
        $response->assertInvalid(['product']);
    }

    /** A plain buyer (different user) CAN add the same product without issue. */
    public function test_different_buyer_can_add_that_product(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000]);

        $buyer = User::factory()->create();
        $buyer->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionDoesntHaveErrors();
    }

    /**
     * Even with `replace=true` (the "clear & add" flow), adding an own-store
     * product must still be blocked — the guard fires before the store-switch
     * logic.
     */
    public function test_buyer_cannot_bypass_guard_with_replace_flag(): void
    {
        $user = $this->multiRoleUser();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000]);

        $response = $this->actingAsBuyer($user)->post(route('buyer.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'replace' => true,
        ]);

        $response->assertInvalid(['product']);
    }
}
