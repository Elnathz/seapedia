<?php

namespace Tests\Feature\Api;

use App\Models\Promo;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDiscountApiTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $admin->createToken('admin')->plainTextToken;
    }

    public function test_admin_creates_a_voucher_persisted_with_zero_used_count(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken())
            ->postJson(route('api.v1.admin.vouchers.store'), [
                'code' => 'HEMAT10',
                'type' => 'percentage',
                'value' => 10,
                'max_discount' => 20_000,
                'min_spend' => 50_000,
                'expiry_date' => now()->addMonth()->toDateTimeString(),
                'usage_limit' => 5,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('code', 'HEMAT10');
        $response->assertJsonPath('used_count', 0);
        $this->assertDatabaseHas('vouchers', ['code' => 'HEMAT10', 'used_count' => 0]);
    }

    public function test_admin_creates_a_promo(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken())
            ->postJson(route('api.v1.admin.promos.store'), [
                'code' => 'PROMO20K',
                'type' => 'fixed',
                'value' => 20_000,
                'min_spend' => 100_000,
                'expiry_date' => now()->addMonth()->toDateTimeString(),
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('promos', ['code' => 'PROMO20K']);
    }

    public function test_non_admin_cannot_create_discount_codes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('session')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.admin.vouchers.store'), [
                'code' => 'NOPE',
                'type' => 'fixed',
                'value' => 1_000,
                'expiry_date' => now()->addMonth()->toDateTimeString(),
                'usage_limit' => 1,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('vouchers', ['code' => 'NOPE']);
    }

    public function test_voucher_usage_limit_must_be_at_least_one(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken())
            ->postJson(route('api.v1.admin.vouchers.store'), [
                'code' => 'ZEROLIMIT',
                'type' => 'fixed',
                'value' => 1_000,
                'expiry_date' => now()->addMonth()->toDateTimeString(),
                'usage_limit' => 0,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['usage_limit']);
    }

    public function test_percentage_value_over_100_is_rejected(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken())
            ->postJson(route('api.v1.admin.promos.store'), [
                'code' => 'TOOMUCH',
                'type' => 'percentage',
                'value' => 150,
                'expiry_date' => now()->addMonth()->toDateTimeString(),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['value']);
    }

    public function test_listing_and_viewing_a_promo(): void
    {
        $promo = Promo::factory()->create();

        $token = $this->adminToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.admin.promos.index'))
            ->assertOk()
            ->assertJsonCount(1);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.admin.promos.show', $promo))
            ->assertOk()
            ->assertJsonPath('code', $promo->code);
    }

    public function test_listing_and_viewing_a_voucher(): void
    {
        $voucher = Voucher::factory()->create();

        $token = $this->adminToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.admin.vouchers.index'))
            ->assertOk()
            ->assertJsonCount(1);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.admin.vouchers.show', $voucher))
            ->assertOk()
            ->assertJsonPath('code', $voucher->code);
    }
}
