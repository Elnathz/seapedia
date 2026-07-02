<?php

namespace Tests\Feature\Checkout;

use App\Enums\DeliveryMethod;
use App\Enums\RegionTier;
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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DeliverySurchargeTest extends TestCase
{
    use RefreshDatabase;

    private const ORIGIN = [
        'province' => 'Jawa Tengah',
        'city' => 'Kota Semarang',
        'district' => 'Tembalang',
        'village' => 'Bulusan',
    ];

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

    private function storeInSemarang(): Store
    {
        return Store::factory()
            ->origin(...array_values(self::ORIGIN))
            ->create();
    }

    private function address(User $user, array $region): Address
    {
        return Address::factory()->create([
            'user_id' => $user->id,
            'is_default' => true,
            ...$region,
        ]);
    }

    /**
     * @return array<string, array{0: array<string,?string>, 1: string, 2: int}>
     */
    public static function tierProvider(): array
    {
        // [destination region, expected tier value, expected surcharge]
        return [
            'same village' => [self::ORIGIN, 'same_village', 0],
            'same district' => [[...self::ORIGIN, 'village' => 'Sumurboto'], 'same_district', 2_000],
            'same city' => [[...self::ORIGIN, 'district' => 'Banyumanik', 'village' => 'Pedalangan'], 'same_city', 5_000],
            'same province' => [['province' => 'Jawa Tengah', 'city' => 'Kabupaten Semarang', 'district' => 'Ungaran', 'village' => 'Bandarjo'], 'same_province', 10_000],
            'interregional' => [['province' => 'DKI Jakarta', 'city' => 'Jakarta Selatan', 'district' => 'Kebayoran Baru', 'village' => 'Melawai'], 'interregional', 20_000],
        ];
    }

    /**
     * @param  array<string,?string>  $region
     */
    #[DataProvider('tierProvider')]
    public function test_preview_adds_the_region_surcharge_on_top_of_the_base_fee(array $region, string $expectedTier, int $expectedSurcharge): void
    {
        $buyer = $this->buyer();
        $store = $this->storeInSemarang();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = $this->address($buyer, $region);

        app(CartService::class)->addItem($buyer, $product, null, 2);

        $preview = app(CheckoutService::class)->preview(
            $buyer->refresh(),
            DeliveryMethod::Regular,
            address: $address,
        );

        $this->assertSame($expectedTier, $preview['region_tier']);
        $this->assertSame(5_000, $preview['delivery_base_fee']);
        $this->assertSame($expectedSurcharge, $preview['delivery_surcharge']);
        $this->assertSame(5_000 + $expectedSurcharge, $preview['delivery_fee']);

        // grand_total = taxable_base + tax + delivery_fee (surcharge not taxed).
        $this->assertSame(100_000, $preview['subtotal']);
        $this->assertSame(12_000, $preview['tax_amount']);
        $this->assertSame(112_000 + 5_000 + $expectedSurcharge, $preview['grand_total']);
    }

    public function test_commit_charges_the_surcharged_fee_and_matches_the_preview(): void
    {
        $buyer = $this->buyer();
        $store = $this->storeInSemarang();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        // Same district as the store origin → +2,000 surcharge.
        $address = $this->address($buyer, [...self::ORIGIN, 'village' => 'Sumurboto']);

        app(CartService::class)->addItem($buyer, $product, null, 2);

        $preview = app(CheckoutService::class)->preview(
            $buyer->refresh(),
            DeliveryMethod::Regular,
            address: $address,
        );

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'regular',
        ]);

        $response->assertRedirect(route('buyer.cart.index'));

        $order = Order::query()->firstOrFail();
        $this->assertSame(7_000, $order->delivery_fee);
        $this->assertSame(119_000, $order->grand_total);

        // The buyer is charged exactly what the preview quoted.
        $this->assertSame($preview['delivery_fee'], $order->delivery_fee);
        $this->assertSame($preview['grand_total'], $order->grand_total);
        $this->assertSame(5_000_000 - 119_000, $buyer->wallet->refresh()->balance);
    }

    public function test_store_without_an_origin_charges_base_fee_only(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create(); // no origin region
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = $this->address($buyer, ['province' => 'DKI Jakarta', 'city' => 'Jakarta Selatan']);

        app(CartService::class)->addItem($buyer, $product, null, 2);

        $preview = app(CheckoutService::class)->preview(
            $buyer->refresh(),
            DeliveryMethod::Instant,
            address: $address,
        );

        $this->assertSame('same_village', $preview['region_tier']);
        $this->assertSame(0, $preview['delivery_surcharge']);
        $this->assertSame(20_000, $preview['delivery_fee']);
    }

    public function test_delivery_fee_service_resolves_the_tier_ladder(): void
    {
        $service = app(DeliveryFeeService::class);
        $store = $this->storeInSemarang();

        $sameCity = new Address([...self::ORIGIN, 'district' => 'Gunungpati', 'village' => 'Sekaran']);

        $this->assertSame(RegionTier::SameCity, $service->tier($store, $sameCity));
        // Base Regular (5,000) + same-city surcharge (5,000).
        $this->assertSame(10_000, $service->fee(DeliveryMethod::Regular, $store, $sameCity));
        // Null address → no comparable destination → base fee only.
        $this->assertSame(RegionTier::SameVillage, $service->tier($store, null));
    }
}
