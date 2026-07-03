<?php

namespace Tests\Feature\Delivery;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Enums\WalletTransactionType;
use App\Models\Address;
use App\Models\Delivery;
use App\Models\Order;
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

class DriverTakeCompleteTest extends TestCase
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
     * Places and processes an order so it lands as an `available` delivery,
     * ready for a driver to take (mirrors T1's auto-create).
     */
    private function availableJob(Store $store): Delivery
    {
        $buyer = $this->userWithRole(RoleName::Buyer);
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);
        $order = app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);

        $seller = $store->user;
        $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        return Delivery::query()->where('order_id', $order->id)->firstOrFail();
    }

    public function test_a_driver_takes_an_available_job_and_advances_the_order(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $delivery = $this->availableJob($store);
        $driver = $this->userWithRole(RoleName::Driver);

        $response = $this->actingAsRole($driver, RoleName::Driver)
            ->post(route('driver.jobs.take', $delivery));

        $response->assertRedirect(route('driver.jobs.show', $delivery));
        $delivery->refresh();
        $this->assertSame(DeliveryStatus::Taken, $delivery->status);
        $this->assertSame($driver->id, $delivery->driver_id);
        $this->assertSame(OrderStatus::SedangDikirim, $delivery->order->status);
    }

    public function test_a_new_driver_is_capped_at_one_active_job(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $driver = $this->userWithRole(RoleName::Driver);
        $service = app(DeliveryService::class);

        // With no on-time history the driver carries a single order at a time.
        $this->assertSame(1, $service->maxActiveJobsFor($driver));

        $service->take($this->availableJob($store), $driver);
        $this->assertSame(1, $service->activeJobCountFor($driver));
    }

    public function test_a_driver_is_refused_a_take_beyond_the_cap(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $driver = $this->userWithRole(RoleName::Driver);
        $service = app(DeliveryService::class);

        // Fresh-driver cap is 1: take one, the next is refused.
        $service->take($this->availableJob($store), $driver);
        $extra = $this->availableJob($store);

        try {
            $service->take($extra, $driver);
            $this->fail('Expected the over-cap take to be rejected.');
        } catch (ValidationException) {
            $this->assertNull($extra->refresh()->driver_id);
            $this->assertSame(1, $service->activeJobCountFor($driver));
        }
    }

    public function test_capacity_grows_with_on_time_completions(): void
    {
        $driver = $this->userWithRole(RoleName::Driver);
        $service = app(DeliveryService::class);

        $this->assertSame(1, $service->maxActiveJobsFor($driver));

        $this->seedOnTimeCompletions($driver, 15);
        $this->assertSame(2, $service->maxActiveJobsFor($driver));

        $this->seedOnTimeCompletions($driver, 15); // 30 on-time total
        $this->assertSame(3, $service->maxActiveJobsFor($driver));
    }

    private function seedOnTimeCompletions(User $driver, int $count): void
    {
        $now = now();

        for ($i = 0; $i < $count; $i++) {
            $order = Order::factory()->create([
                'status' => OrderStatus::PesananSelesai,
                'sla_due_at' => $now->copy()->addDays(3),
            ]);
            Delivery::factory()->create([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'status' => DeliveryStatus::Completed,
                'completed_at' => $now,
            ]);
        }
    }

    /**
     * The web (Inertia) route deliberately swallows the 409 conflict into a
     * graceful redirect + flashed toast (plan UX direction: "disappears /
     * shows 'Sudah diambil' rather than erroring loudly") — a raw 409 HTML
     * error page would also collide with Inertia's own reserved use of 409
     * for asset-version reloads. The literal HTTP 409 is asserted at the
     * API layer instead (DriverJobApiTest), and at the service layer
     * (DeliveryConcurrencyTest) where ConflictHttpException is thrown.
     */
    public function test_a_second_driver_taking_an_already_taken_job_is_redirected_with_a_toast(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $delivery = $this->availableJob($store);
        $driverA = $this->userWithRole(RoleName::Driver);
        $driverB = $this->userWithRole(RoleName::Driver);

        $this->actingAsRole($driverA, RoleName::Driver)
            ->post(route('driver.jobs.take', $delivery));

        $response = $this->actingAsRole($driverB, RoleName::Driver)
            ->post(route('driver.jobs.take', $delivery));

        $response->assertRedirect(route('driver.jobs.index'));
        $this->assertSame($driverA->id, $delivery->refresh()->driver_id);
    }

    public function test_completing_a_job_pays_the_driver_and_releases_seller_escrow(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $delivery = $this->availableJob($store);
        $driver = $this->userWithRole(RoleName::Driver);

        app(DeliveryService::class)->take($delivery, $driver);

        $order = $delivery->order;
        $expectedEarning = intdiv($order->delivery_fee * 80, 100);

        // Escrow: still untouched right after take(), before completion.
        $this->assertSame(0, $seller->wallet->refresh()->balance);

        $response = $this->actingAsRole($driver, RoleName::Driver)
            ->post(route('driver.jobs.complete', $delivery));

        $response->assertRedirect(route('driver.jobs.show', $delivery));
        $delivery->refresh();
        $order->refresh();

        $this->assertSame(DeliveryStatus::Completed, $delivery->status);
        $this->assertSame($expectedEarning, $delivery->earning_amount);
        $this->assertSame(OrderStatus::PesananSelesai, $order->status);
        $this->assertSame($expectedEarning, $driver->wallet->refresh()->balance);
        $this->assertSame($order->seller_income_amount, $seller->wallet->refresh()->balance);

        $this->assertSame(1, $driver->wallet->transactions()->where('type', WalletTransactionType::Earning->value)->count());
        $this->assertSame(1, $seller->wallet->transactions()->where('type', WalletTransactionType::Income->value)->count());
    }

    public function test_a_different_driver_cannot_complete_someone_elses_job(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $delivery = $this->availableJob($store);
        $driverA = $this->userWithRole(RoleName::Driver);
        $driverB = $this->userWithRole(RoleName::Driver);

        app(DeliveryService::class)->take($delivery, $driverA);

        $response = $this->actingAsRole($driverB, RoleName::Driver)
            ->post(route('driver.jobs.complete', $delivery));

        $response->assertForbidden();
        $this->assertSame(DeliveryStatus::Taken, $delivery->refresh()->status);
    }

    public function test_completing_an_already_completed_job_does_not_double_pay(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $delivery = $this->availableJob($store);
        $driver = $this->userWithRole(RoleName::Driver);

        app(DeliveryService::class)->take($delivery, $driver);
        app(DeliveryService::class)->complete($delivery, $driver);

        $balanceAfterFirstComplete = $driver->wallet->refresh()->balance;

        $this->expectException(ValidationException::class);

        try {
            app(DeliveryService::class)->complete($delivery->refresh(), $driver);
        } finally {
            $this->assertSame($balanceAfterFirstComplete, $driver->wallet->refresh()->balance);
        }
    }

    public function test_checkout_does_not_move_the_seller_balance_only_completion_does(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $delivery = $this->availableJob($store);

        $this->assertSame(0, $seller->wallet->refresh()->balance);

        $driver = $this->userWithRole(RoleName::Driver);
        app(DeliveryService::class)->take($delivery, $driver);
        $this->assertSame(0, $seller->wallet->refresh()->balance);

        app(DeliveryService::class)->complete($delivery, $driver);
        $this->assertGreaterThan(0, $seller->wallet->refresh()->balance);
    }

    public function test_the_job_detail_exposes_coordinates_for_the_route_map(): void
    {
        $store = Store::factory()->create([
            'origin_latitude' => -6.9,
            'origin_longitude' => 107.6,
        ]);
        $order = Order::factory()->create([
            'store_id' => $store->id,
            'status' => OrderStatus::MenungguPengirim,
            'ship_latitude' => -6.2,
            'ship_longitude' => 106.8,
        ]);
        $delivery = Delivery::factory()->create([
            'order_id' => $order->id,
            'status' => DeliveryStatus::Available,
        ]);
        $driver = $this->userWithRole(RoleName::Driver);

        $this->actingAsRole($driver, RoleName::Driver)
            ->get(route('driver.jobs.show', $delivery))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('job.order.store.origin_latitude', -6.9)
                ->where('job.order.store.origin_longitude', 107.6)
                ->where('job.order.ship_latitude', -6.2)
                ->where('job.order.ship_longitude', 106.8)
            );
    }
}
