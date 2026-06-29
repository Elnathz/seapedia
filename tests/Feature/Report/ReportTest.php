<?php

namespace Tests\Feature\Report;

use App\Enums\DeliveryMethod;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(RoleName $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => $role->value])->id,
        );

        return $user;
    }

    private function actingAsRole(User $user, RoleName $role)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => $role->value]);
    }

    private function placeOrder(User $buyer, Store $store, int $price = 50_000): Order
    {
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => $price, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        return app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);
    }

    public function test_buyer_report_is_scoped_to_their_own_orders_and_totals_reconcile(): void
    {
        $buyerA = $this->userWithRole(RoleName::Buyer);
        $buyerB = $this->userWithRole(RoleName::Buyer);
        $store = Store::factory()->create();

        $orderA = $this->placeOrder($buyerA, $store, 50_000);
        $this->placeOrder($buyerB, $store, 100_000);

        $response = $this->actingAsRole($buyerA, RoleName::Buyer)->get(route('buyer.reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('report.order_count', 1)
            ->where('report.total_spent', $orderA->grand_total)
            ->where('report.breakdown.0.status', 'sedang_dikemas')
            ->where('report.breakdown.0.count', 1)
        );
    }

    public function test_seller_report_is_scoped_to_their_own_store_and_totals_reconcile(): void
    {
        $sellerA = $this->userWithRole(RoleName::Seller);
        $sellerB = $this->userWithRole(RoleName::Seller);
        $storeA = Store::factory()->create(['user_id' => $sellerA->id]);
        $storeB = Store::factory()->create(['user_id' => $sellerB->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);
        $driver = $this->userWithRole(RoleName::Driver);

        // Under escrow (Sprint 5 Decision 3), income is only realized once
        // an order reaches Pesanan Selesai — drive order A all the way
        // through the delivery flow so its income shows up in the report.
        $orderA = $this->placeOrder($buyer, $storeA, 50_000);
        $this->placeOrder($buyer, $storeB, 100_000);

        $this->actingAsRole($sellerA, RoleName::Seller)->post(route('seller.orders.process', $orderA));
        $delivery = Delivery::query()->where('order_id', $orderA->id)->firstOrFail();
        $this->actingAsRole($driver, RoleName::Driver)->post(route('driver.jobs.take', $delivery));
        $this->actingAsRole($driver, RoleName::Driver)->post(route('driver.jobs.complete', $delivery));

        $response = $this->actingAsRole($sellerA, RoleName::Seller)->get(route('seller.reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('report.order_count', 1)
            ->where('report.total_income', $orderA->seller_income_amount)
            ->where('report.incoming_count', 0)
            ->where('report.processed_count', 1)
        );
    }

    public function test_seller_report_separates_incoming_from_processed_orders(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);

        $this->placeOrder($buyer, $store);
        $processedOrder = $this->placeOrder($buyer, $store);

        $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $processedOrder));

        $response = $this->actingAsRole($seller, RoleName::Seller)->get(route('seller.reports.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('report.order_count', 2)
            ->where('report.incoming_count', 1)
            ->where('report.processed_count', 1)
        );
    }
}
