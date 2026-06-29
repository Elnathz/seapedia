<?php

namespace Tests\Feature\Api;

use App\Enums\DiscountType;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CartService;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerCheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    private function buyerWithToken(int $balance = 1_000_000): array
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );
        $user->wallet->update(['balance' => $balance]);

        $token = app(RoleService::class)->issueApiToken($user, RoleName::Buyer)->plainTextToken;

        return [$user, $token];
    }

    public function test_api_checkout_happy_path_returns_the_order(): void
    {
        [$buyer, $token] = $this->buyerWithToken();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 2);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.buyer.checkout.store'), [
                'address_id' => $address->id,
                'delivery_method' => 'regular',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('status', 'sedang_dikemas');
        $response->assertJsonPath('grand_total', 117_000);
        $response->assertJsonCount(1, 'items');
    }

    public function test_api_checkout_with_insufficient_balance_returns_422(): void
    {
        [$buyer, $token] = $this->buyerWithToken(balance: 1_000);
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.buyer.checkout.store'), [
                'address_id' => $address->id,
                'delivery_method' => 'regular',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['balance']);
    }

    public function test_api_checkout_preview_returns_money_breakdown(): void
    {
        [$buyer, $token] = $this->buyerWithToken();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100_000, 'stock' => 10]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.buyer.checkout.preview'), [
                'delivery_method' => 'instant',
            ]);

        $response->assertOk();
        $response->assertJsonPath('subtotal', 100_000);
        $response->assertJsonPath('tax_amount', 12_000);
        $response->assertJsonPath('delivery_fee', 20_000);
        $response->assertJsonPath('grand_total', 132_000);
        $response->assertJsonPath('sufficient_balance', true);
    }

    public function test_api_checkout_preview_returns_distinct_promo_and_voucher_lines(): void
    {
        [$buyer, $token] = $this->buyerWithToken();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100_000, 'stock' => 10]);
        $promo = Promo::factory()->create(['type' => DiscountType::Percentage, 'value' => 10, 'max_discount' => null]);
        $voucher = Voucher::factory()->create(['type' => DiscountType::Fixed, 'value' => 5_000, 'max_discount' => null]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.buyer.checkout.preview'), [
                'delivery_method' => 'regular',
                'promo_code' => $promo->code,
                'voucher_code' => $voucher->code,
            ]);

        $response->assertOk();
        $response->assertJsonPath('promo.code', $promo->code);
        $response->assertJsonPath('promo.amount', 10_000);
        $response->assertJsonPath('voucher.code', $voucher->code);
        $response->assertJsonPath('voucher.amount', 5_000);
        $response->assertJsonPath('discount_total', 15_000);
    }

    public function test_api_checkout_preview_returns_a_structured_error_for_an_invalid_code(): void
    {
        [$buyer, $token] = $this->buyerWithToken();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100_000, 'stock' => 10]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.buyer.checkout.preview'), [
                'delivery_method' => 'regular',
                'promo_code' => 'DOES-NOT-EXIST',
            ]);

        $response->assertOk();
        $response->assertJsonPath('promo', null);
        $response->assertJsonPath('promo_error', __('Invalid promo code.'));
        $response->assertJsonPath('discount_total', 0);
    }
}
