<?php

namespace Tests\Feature\Checkout;

use App\Enums\DeliveryMethod;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DeliveryFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryFeeTest extends TestCase
{
    use RefreshDatabase;

    // Two points ~5.5 km apart (same latitude, 0.05° of longitude at -7°).
    private const ORIGIN_LAT = -7.0;

    private const ORIGIN_LNG = 110.40;

    private const NEAR_LAT = -7.0;

    private const NEAR_LNG = 110.45;

    private function buyer(int $balance = 5_000_000): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );
        $user->wallet->update(['balance' => $balance]);

        return $user;
    }

    private function actingAsBuyer(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value]);
    }

    public function test_haversine_distance_matches_the_known_separation(): void
    {
        $service = app(DeliveryFeeService::class);
        $store = Store::factory()->originAt(self::ORIGIN_LAT, self::ORIGIN_LNG)->create();
        $address = new Address(['latitude' => self::NEAR_LAT, 'longitude' => self::NEAR_LNG]);

        // ~5.52 km — assert within a tight tolerance.
        $this->assertEqualsWithDelta(5.52, $service->distanceKm($store, $address), 0.1);
    }

    public function test_fee_is_base_plus_distance_plus_weight(): void
    {
        $service = app(DeliveryFeeService::class);
        $store = Store::factory()->originAt(self::ORIGIN_LAT, self::ORIGIN_LNG)->create();
        $address = new Address(['latitude' => self::NEAR_LAT, 'longitude' => self::NEAR_LNG]);

        // 5.52 km → billable 6 km. Weight 1500 g → 1 extra kg → +2,000.
        $fee = $service->breakdown(DeliveryMethod::Regular, $store, $address, 1_500);

        $this->assertSame(6, $fee['billable_km']);
        $this->assertSame(5_000, $fee['base_fee']);          // Regular base
        $this->assertSame(6_000, $fee['distance_fee']);      // 6 km × 1,000
        $this->assertSame(2_000, $fee['weight_fee']);        // 1 kg over the free kg
        $this->assertSame(13_000, $fee['total']);
    }

    public function test_distance_is_capped_and_every_method_stays_distinct(): void
    {
        $service = app(DeliveryFeeService::class);
        // (0,0) → (0,1) ≈ 111 km, well past the 80 km cap.
        $store = Store::factory()->originAt(0.0, 0.0)->create();
        $address = new Address(['latitude' => 0.0, 'longitude' => 1.0]);

        $regular = $service->fee(DeliveryMethod::Regular, $store, $address);
        $nextDay = $service->fee(DeliveryMethod::NextDay, $store, $address);
        $instant = $service->fee(DeliveryMethod::Instant, $store, $address);

        // base + 80 km × rate (weight 0).
        $this->assertSame(5_000 + 80 * 1_000, $regular);
        $this->assertSame(10_000 + 80 * 1_500, $nextDay);
        $this->assertSame(20_000 + 80 * 2_500, $instant);

        // Spec line 278: fee differs per method, even at the distance cap.
        $this->assertTrue($regular < $nextDay && $nextDay < $instant);
    }

    public function test_weight_surcharge_brackets(): void
    {
        $service = app(DeliveryFeeService::class);
        $store = Store::factory()->originAt(self::ORIGIN_LAT, self::ORIGIN_LNG)->create();
        $sameSpot = new Address(['latitude' => self::ORIGIN_LAT, 'longitude' => self::ORIGIN_LNG]);

        // 0 km → fee is base + weight only.
        $this->assertSame(0, $service->breakdown(DeliveryMethod::Regular, $store, $sameSpot, 1_000)['weight_fee']);
        $this->assertSame(2_000, $service->breakdown(DeliveryMethod::Regular, $store, $sameSpot, 1_001)['weight_fee']);
        $this->assertSame(2_000, $service->breakdown(DeliveryMethod::Regular, $store, $sameSpot, 2_000)['weight_fee']);
        $this->assertSame(4_000, $service->breakdown(DeliveryMethod::Regular, $store, $sameSpot, 2_001)['weight_fee']);
    }

    public function test_missing_coordinates_bill_base_fee_only(): void
    {
        $service = app(DeliveryFeeService::class);
        $store = Store::factory()->create(); // no origin coordinates
        $address = new Address(['latitude' => self::NEAR_LAT, 'longitude' => self::NEAR_LNG]);

        $this->assertSame(0.0, $service->distanceKm($store, $address));
        $this->assertSame(5_000, $service->fee(DeliveryMethod::Regular, $store, $address, 500));
    }

    public function test_preview_equals_the_committed_charge(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->originAt(self::ORIGIN_LAT, self::ORIGIN_LNG)->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'price' => 50_000,
            'stock' => 10,
            'weight' => 1_500,
        ]);
        $address = Address::factory()->create([
            'user_id' => $buyer->id,
            'is_default' => true,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
        ]);

        app(CartService::class)->addItem($buyer, $product, null, 2);

        $preview = app(CheckoutService::class)->preview(
            $buyer->refresh(),
            DeliveryMethod::Regular,
            address: $address,
        );

        // 2 × 1,500 g = 3,000 g → 2 extra kg → +4,000. 6 km × 1,000 = 6,000. base 5,000.
        $this->assertSame(6_000, $preview['delivery_distance_fee']);
        $this->assertSame(4_000, $preview['delivery_weight_fee']);
        $this->assertSame(15_000, $preview['delivery_fee']);

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'regular',
        ]);
        $response->assertRedirect(route('buyer.cart.index'));

        $order = Order::query()->firstOrFail();
        $this->assertSame($preview['delivery_fee'], $order->delivery_fee);
        $this->assertSame($preview['grand_total'], $order->grand_total);
        // subtotal 100,000 + tax 12,000 + delivery 15,000.
        $this->assertSame(127_000, $order->grand_total);
    }
}
