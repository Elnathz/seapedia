<?php

namespace Tests\Feature\Cart;

use App\Enums\RoleName;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartManagementTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );

        return $user;
    }

    private function actingAsBuyer(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value]);
    }

    public function test_adding_from_a_different_store_is_rejected_with_422(): void
    {
        $buyer = $this->buyer();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $productA = Product::factory()->create(['store_id' => $storeA->id, 'price' => 10_000]);
        $productB = Product::factory()->create(['store_id' => $storeB->id, 'price' => 20_000]);

        $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $productA->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $productB->id,
            'quantity' => 1,
        ]);

        $response->assertInvalid(['store']);
        $this->assertSame(1, CartItem::query()->count());
    }

    public function test_clear_and_add_replaces_the_cart_with_the_new_store(): void
    {
        $buyer = $this->buyer();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $productA = Product::factory()->create(['store_id' => $storeA->id]);
        $productB = Product::factory()->create(['store_id' => $storeB->id]);

        $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $productA->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $productB->id,
            'quantity' => 2,
            'replace' => true,
        ]);

        $response->assertSessionDoesntHaveErrors();

        $items = CartItem::query()->get();
        $this->assertCount(1, $items);
        $this->assertSame($productB->id, $items->first()->product_id);
        $this->assertSame(2, $items->first()->quantity);
    }

    public function test_updating_quantity_changes_the_line_subtotal(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 15_000]);

        $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $item = CartItem::query()->where('product_id', $product->id)->first();

        $this->actingAsBuyer($buyer)->put(route('buyer.cart.update', $item), [
            'quantity' => 3,
        ]);

        $item->refresh();
        $this->assertSame(3, $item->quantity);
        $this->assertSame(45_000, $item->price_snapshot * $item->quantity);
    }

    public function test_cross_user_cart_item_update_is_forbidden(): void
    {
        $owner = $this->buyer();
        $other = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $this->actingAsBuyer($owner)->post(route('buyer.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $item = CartItem::query()->where('product_id', $product->id)->first();

        $response = $this->actingAsBuyer($other)->put(route('buyer.cart.update', $item), [
            'quantity' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_removing_the_last_item_frees_the_cart_store_lock(): void
    {
        $buyer = $this->buyer();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $productA = Product::factory()->create(['store_id' => $storeA->id]);
        $productB = Product::factory()->create(['store_id' => $storeB->id]);

        $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $productA->id,
            'quantity' => 1,
        ]);
        $item = CartItem::query()->where('product_id', $productA->id)->first();

        $this->actingAsBuyer($buyer)->delete(route('buyer.cart.destroy', $item));

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.cart.store'), [
            'product_id' => $productB->id,
            'quantity' => 1,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertSame($productB->id, CartItem::query()->first()->product_id);
    }
}
