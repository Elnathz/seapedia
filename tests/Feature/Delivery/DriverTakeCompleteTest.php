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

        app(CartService::class)->addItem($buyer, $product, 1);
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

    public function test_a_driver_with_an_active_job_is_refused_a_second_take(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $delivery1 = $this->availableJob($store);
        $delivery2 = $this->availableJob($store);
        $driver = $this->userWithRole(RoleName::Driver);

        app(DeliveryService::class)->take($delivery1, $driver);

        $this->expectException(ValidationException::class);
        app(DeliveryService::class)->take($delivery2, $driver);
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
}
