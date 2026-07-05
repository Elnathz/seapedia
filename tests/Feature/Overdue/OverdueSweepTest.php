<?php

namespace Tests\Feature\Overdue;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ClockService;
use App\Services\DeliveryService;
use App\Services\OverdueService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OverdueSweepTest extends TestCase
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

    /**
     * Places an order and stamps its sla_due_at in the past, regardless of
     * its delivery method's normal SLA window, so sweep tests don't have to
     * juggle ClockService::advance() ticks against slaTicks().
     */
    private function overdueOrder(User $buyer, Store $store, int $price = 50_000, ?string $voucherCode = null): Order
    {
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => $price, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $order = app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular, null, $voucherCode);
        $order->update(['sla_due_at' => now()->subDay()]);

        return $order->refresh();
    }

    public function test_an_overdue_order_is_refunded_stock_restored_and_seller_balance_untouched(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);

        $order = $this->overdueOrder($buyer, $store, 50_000);
        $product = $order->items->first()->product;
        $stockBeforeRefund = $product->stock;
        $balanceBeforeRefund = $buyer->wallet->refresh()->balance;

        $result = app(OverdueService::class)->sweep();

        $this->assertSame(1, $result['refunded_count']);

        $order->refresh();
        $this->assertSame(OrderStatus::Dikembalikan, $order->status);
        $this->assertNotNull($order->refunded_at);
        $this->assertTrue(
            $order->statusHistories()->where('status', OrderStatus::Dikembalikan)->exists(),
        );

        $this->assertSame($balanceBeforeRefund + $order->grand_total, $buyer->wallet->refresh()->balance);
        $this->assertSame($stockBeforeRefund + 1, $product->refresh()->stock);
        $this->assertSame(0, $seller->wallet->refresh()->balance);
    }

    public function test_running_the_sweep_twice_does_not_double_refund_or_double_restore(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);

        $order = $this->overdueOrder($buyer, $store, 50_000);
        $product = $order->items->first()->product;

        app(OverdueService::class)->sweep();
        $balanceAfterFirstSweep = $buyer->wallet->refresh()->balance;
        $stockAfterFirstSweep = $product->refresh()->stock;
        $refundedAtAfterFirstSweep = $order->refresh()->refunded_at;

        $result = app(OverdueService::class)->sweep();

        $this->assertSame(0, $result['refunded_count']);
        $this->assertSame($balanceAfterFirstSweep, $buyer->wallet->refresh()->balance);
        $this->assertSame($stockAfterFirstSweep, $product->refresh()->stock);
        $this->assertEquals($refundedAtAfterFirstSweep, $order->refresh()->refunded_at);
    }

    public function test_a_not_yet_overdue_order_is_left_untouched(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);
        $order = app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);

        $result = app(OverdueService::class)->sweep();

        $this->assertSame(0, $result['refunded_count']);
        $order->refresh();
        $this->assertSame(OrderStatus::SedangDikemas, $order->status);
        $this->assertNull($order->refunded_at);
    }

    public function test_a_completed_order_is_never_swept(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);
        $driver = $this->userWithRole(RoleName::Driver);

        // Overdue but already completed: process -> take -> complete before sweeping.
        $order = $this->overdueOrder($buyer, $store, 50_000);
        $this->actingAsRole($seller, RoleName::Seller)->post(route('seller.orders.process', $order));
        $delivery = Delivery::query()->where('order_id', $order->id)->firstOrFail();
        $this->actingAsRole($driver, RoleName::Driver)->post(route('driver.jobs.take', $delivery));
        $this->actingAsRole($driver, RoleName::Driver)->post(route('driver.jobs.complete', $delivery));

        $sellerBalanceBeforeSweep = $seller->wallet->refresh()->balance;

        $result = app(OverdueService::class)->sweep();

        $this->assertSame(0, $result['refunded_count']);
        $order->refresh();
        $this->assertSame(OrderStatus::PesananSelesai, $order->status);
        $this->assertNull($order->refunded_at);
        $this->assertSame($sellerBalanceBeforeSweep, $seller->wallet->refresh()->balance);
    }

    public function test_sweeping_an_in_transit_order_cancels_its_delivery_and_frees_the_driver(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);
        $driver = $this->userWithRole(RoleName::Driver);

        // Order is taken by the driver (Sedang Dikirim) but goes overdue
        // before it is completed.
        $order = $this->overdueOrder($buyer, $store, 50_000);
        $this->actingAsRole($seller, RoleName::Seller)->post(route('seller.orders.process', $order));
        $delivery = Delivery::query()->where('order_id', $order->id)->firstOrFail();
        $this->actingAsRole($driver, RoleName::Driver)->post(route('driver.jobs.take', $delivery));

        $this->assertSame(DeliveryStatus::Taken, $delivery->refresh()->status);
        $sellerBalanceBeforeSweep = $seller->wallet->refresh()->balance;

        $result = app(OverdueService::class)->sweep();

        $this->assertSame(1, $result['refunded_count']);
        $this->assertSame(OrderStatus::Dikembalikan, $order->refresh()->status);

        // Delivery is cancelled, not left dangling as Taken.
        $this->assertSame(DeliveryStatus::Cancelled, $delivery->refresh()->status);

        // Driver is no longer holding this job, freeing a slot under the
        // active-jobs cap to claim a new one.
        $this->assertSame(0, app(DeliveryService::class)->activeJobCountFor($driver));

        // No payout leaked to the seller for the undelivered order.
        $this->assertSame($sellerBalanceBeforeSweep, $seller->wallet->refresh()->balance);
    }

    public function test_voucher_used_count_is_unchanged_after_a_refund(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);
        $voucher = Voucher::factory()->create(['min_spend' => null]);

        $this->overdueOrder($buyer, $store, 50_000, $voucher->code);
        $this->assertSame(1, $voucher->refresh()->used_count);

        app(OverdueService::class)->sweep();

        $this->assertSame(1, $voucher->refresh()->used_count);
    }

    public function test_reports_exclude_a_refunded_order(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);

        $this->overdueOrder($buyer, $store, 50_000);
        app(OverdueService::class)->sweep();

        $buyerReport = app(ReportService::class)->buyerSpending($buyer);
        $sellerReport = app(ReportService::class)->sellerIncome($store);

        $this->assertSame(0, $buyerReport['order_count']);
        $this->assertSame(0, $buyerReport['total_spent']);
        $this->assertSame(0, $sellerReport['order_count']);
        $this->assertSame(0, $sellerReport['total_income']);
    }

    public function test_admin_sweeps_overdue_orders_via_the_endpoint_without_advancing_time(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);
        $admin = User::factory()->create(['is_admin' => true]);

        $order = $this->overdueOrder($buyer, $store, 50_000);
        $balanceBeforeRefund = $buyer->wallet->refresh()->balance;
        $clockBefore = app(ClockService::class)->now();

        $this->actingAs($admin)->post(route('admin.overdue.sweep'))->assertRedirect();

        $this->assertSame(OrderStatus::Dikembalikan, $order->refresh()->status);
        $this->assertSame($balanceBeforeRefund + $order->grand_total, $buyer->wallet->refresh()->balance);
        // The direct sweep must not move the clock — that's the whole point.
        $this->assertSame($clockBefore->toDateString(), app(ClockService::class)->now()->toDateString());
    }

    public function test_a_non_admin_cannot_trigger_the_overdue_sweep(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.overdue.sweep'))->assertForbidden();
    }

    public function test_advancing_the_day_via_artisan_runs_the_sweep_end_to_end(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);

        $order = $this->overdueOrder($buyer, $store, 50_000);
        $balanceBeforeRefund = $buyer->wallet->refresh()->balance;

        Artisan::call('seapedia:advance-day');

        $this->assertSame(OrderStatus::Dikembalikan, $order->refresh()->status);
        $this->assertSame($balanceBeforeRefund + $order->grand_total, $buyer->wallet->refresh()->balance);
        $this->assertNotNull(app(ClockService::class)->now());
    }
}
