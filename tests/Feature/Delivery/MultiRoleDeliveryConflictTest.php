<?php

namespace Tests\Feature\Delivery;

use App\Enums\DeliveryMethod;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * §MULTI-ROLE CONFLICT GUARD 2 — courier == buyer
 * §MULTI-ROLE CONFLICT GUARD 3 — courier == seller
 *
 * Verifies that a driver who also owns the Buyer or Seller role cannot
 * take a delivery job where they are a party on the other side of the
 * transaction. Both the Policy layer (→ 403) and the Service layer
 * (ValidationException inside the lock) are tested.
 */
class MultiRoleDeliveryConflictTest extends TestCase
{
    use RefreshDatabase;

    private function attachRole(User $user, RoleName $role): void
    {
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => $role->value])->id,
        );
    }

    private function actingAsRole(User $user, RoleName $role)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => $role->value]);
    }

    /**
     * Creates an available delivery job for the given store.
     * The buyer is always a fresh, unrelated user so we control who the buyer is.
     */
    private function availableJobForStore(Store $store, ?User $buyer = null): array
    {
        if ($buyer === null) {
            $buyer = User::factory()->create();
            $this->attachRole($buyer, RoleName::Buyer);
        }

        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'price' => 50_000,
            'stock' => 10,
        ]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);
        $order = app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);

        // Process the order as the seller to make the delivery available
        $seller = $store->user;
        $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        $delivery = Delivery::query()->where('order_id', $order->id)->firstOrFail();

        return [$delivery, $order, $buyer];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Guard 3: courier == seller owner
    // ─────────────────────────────────────────────────────────────────────

    /**
     * A user who owns both Seller and Driver roles and is the seller of the
     * store that fulfilled the order must NOT be able to take the delivery job
     * via the web route (Policy returns false → 403).
     */
    public function test_seller_cannot_take_delivery_for_own_store_via_web(): void
    {
        $sellerDriver = User::factory()->create();
        $this->attachRole($sellerDriver, RoleName::Seller);
        $this->attachRole($sellerDriver, RoleName::Driver);

        $store = Store::factory()->create(['user_id' => $sellerDriver->id]);

        [$delivery] = $this->availableJobForStore($store);

        $response = $this->actingAsRole($sellerDriver, RoleName::Driver)
            ->post(route('driver.jobs.take', $delivery));

        $response->assertForbidden();
        $this->assertNull($delivery->refresh()->driver_id);
    }

    /**
     * Direct service-layer call (simulates Postman hitting the API) must also
     * be rejected with a ValidationException, even after the Policy gate.
     */
    public function test_seller_cannot_take_delivery_for_own_store_via_service(): void
    {
        $sellerDriver = User::factory()->create();
        $this->attachRole($sellerDriver, RoleName::Seller);
        $this->attachRole($sellerDriver, RoleName::Driver);

        $store = Store::factory()->create(['user_id' => $sellerDriver->id]);

        [$delivery] = $this->availableJobForStore($store);

        $this->expectException(ValidationException::class);
        app(DeliveryService::class)->take($delivery, $sellerDriver);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Guard 2: courier == buyer
    // ─────────────────────────────────────────────────────────────────────

    /**
     * A user who owns both Buyer and Driver roles and is the buyer of the
     * order must NOT be able to take the delivery job via the web route (→ 403).
     */
    public function test_buyer_cannot_take_delivery_for_own_order_via_web(): void
    {
        $buyerDriver = User::factory()->create();
        $this->attachRole($buyerDriver, RoleName::Buyer);
        $this->attachRole($buyerDriver, RoleName::Driver);

        $seller = User::factory()->create();
        $this->attachRole($seller, RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);

        // The buyerDriver is the buyer who places the order
        [$delivery] = $this->availableJobForStore($store, $buyerDriver);

        $response = $this->actingAsRole($buyerDriver, RoleName::Driver)
            ->post(route('driver.jobs.take', $delivery));

        $response->assertForbidden();
        $this->assertNull($delivery->refresh()->driver_id);
    }

    /**
     * Same guard checked at the service layer.
     */
    public function test_buyer_cannot_take_delivery_for_own_order_via_service(): void
    {
        $buyerDriver = User::factory()->create();
        $this->attachRole($buyerDriver, RoleName::Buyer);
        $this->attachRole($buyerDriver, RoleName::Driver);

        $seller = User::factory()->create();
        $this->attachRole($seller, RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);

        [$delivery] = $this->availableJobForStore($store, $buyerDriver);

        $this->expectException(ValidationException::class);
        app(DeliveryService::class)->take($delivery, $buyerDriver);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Regression: unrelated driver is NOT blocked
    // ─────────────────────────────────────────────────────────────────────

    /**
     * A driver who has no relationship to this order (not the seller, not the
     * buyer) must be able to take the job normally — no regression introduced.
     */
    public function test_unrelated_driver_can_still_take_the_job(): void
    {
        $seller = User::factory()->create();
        $this->attachRole($seller, RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);

        [$delivery] = $this->availableJobForStore($store);

        $driver = User::factory()->create();
        $this->attachRole($driver, RoleName::Driver);

        $response = $this->actingAsRole($driver, RoleName::Driver)
            ->post(route('driver.jobs.take', $delivery));

        $response->assertRedirect(route('driver.jobs.show', $delivery));
        $this->assertSame($driver->id, $delivery->refresh()->driver_id);
    }
}
