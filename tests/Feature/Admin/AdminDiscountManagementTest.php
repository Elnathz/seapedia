<?php

namespace Tests\Feature\Admin;

use App\Models\Promo;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDiscountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_promo_via_the_web_controller(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.promos.store'), [
            'code' => 'HEMAT10',
            'type' => 'percentage',
            'value' => 10,
            'max_discount' => 20_000,
            'min_spend' => 50_000,
            'expiry_date' => now()->addMonth()->toDateTimeString(),
        ]);

        $response->assertRedirect(route('admin.promos.index'));
        $this->assertDatabaseHas('promos', ['code' => 'HEMAT10']);
    }

    public function test_admin_creates_a_voucher_via_the_web_controller(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'code' => 'VCR5K',
            'type' => 'fixed',
            'value' => 5_000,
            'expiry_date' => now()->addMonth()->toDateTimeString(),
            'usage_limit' => 10,
        ]);

        $response->assertRedirect(route('admin.vouchers.index'));
        $this->assertDatabaseHas('vouchers', ['code' => 'VCR5K', 'used_count' => 0]);
    }

    public function test_admin_toggles_a_promo_active_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $promo = Promo::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->patch(route('admin.promos.toggleActive', $promo));

        $response->assertRedirect();
        $this->assertFalse($promo->refresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.promos.toggleActive', $promo));
        $this->assertTrue($promo->refresh()->is_active);
    }

    public function test_admin_toggles_a_voucher_active_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $voucher = Voucher::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->patch(route('admin.vouchers.toggleActive', $voucher));

        $response->assertRedirect();
        $this->assertFalse($voucher->refresh()->is_active);
    }

    public function test_admin_views_promo_and_voucher_index_and_show_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $promo = Promo::factory()->create();
        $voucher = Voucher::factory()->create();

        $this->actingAs($admin)->get(route('admin.promos.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.promos.show', $promo))->assertOk();
        $this->actingAs($admin)->get(route('admin.vouchers.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.vouchers.show', $voucher))->assertOk();
    }

    public function test_non_admin_cannot_manage_promos_or_vouchers(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $promo = Promo::factory()->create();

        $this->actingAs($user)->get(route('admin.promos.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.promos.store'), [
            'code' => 'NOPE', 'type' => 'fixed', 'value' => 1_000,
            'expiry_date' => now()->addMonth()->toDateTimeString(),
        ])->assertForbidden();
        $this->actingAs($user)->patch(route('admin.promos.toggleActive', $promo))->assertForbidden();
        $this->assertDatabaseMissing('promos', ['code' => 'NOPE']);
    }
}
