<?php

namespace Tests\Feature\Checkout;

use App\Enums\DiscountType;
use App\Models\Promo;
use App\Models\Voucher;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailableDiscountsTest extends TestCase
{
    use RefreshDatabase;

    private function service(): DiscountService
    {
        return app(DiscountService::class);
    }

    public function test_it_lists_only_live_codes(): void
    {
        Promo::factory()->create(['code' => 'LIVE']);
        Promo::factory()->create(['code' => 'OFF', 'is_active' => false]);
        Promo::factory()->expired()->create(['code' => 'OLD']);

        Voucher::factory()->create(['code' => 'VLIVE']);
        Voucher::factory()->usedUp()->create(['code' => 'VFULL']);
        Voucher::factory()->expired()->create(['code' => 'VOLD']);

        $result = $this->service()->availableFor(100_000);
        $promoCodes = array_column($result['promos'], 'code');
        $voucherCodes = array_column($result['vouchers'], 'code');

        $this->assertContains('LIVE', $promoCodes);
        $this->assertNotContains('OFF', $promoCodes);
        $this->assertNotContains('OLD', $promoCodes);

        $this->assertContains('VLIVE', $voucherCodes);
        $this->assertNotContains('VFULL', $voucherCodes);
        $this->assertNotContains('VOLD', $voucherCodes);
    }

    public function test_it_flags_ineligible_codes_and_computes_savings(): void
    {
        Promo::factory()->create([
            'code' => 'BIG',
            'type' => DiscountType::Fixed,
            'value' => 20_000,
            'min_spend' => 200_000,
        ]);

        // Below the minimum spend: surfaced but gated, offering nothing.
        $below = collect($this->service()->availableFor(100_000)['promos'])
            ->firstWhere('code', 'BIG');
        $this->assertFalse($below['eligible']);
        $this->assertSame(0, $below['amount']);

        // Once the subtotal clears the minimum, the fixed amount is offered.
        $ok = collect($this->service()->availableFor(200_000)['promos'])
            ->firstWhere('code', 'BIG');
        $this->assertTrue($ok['eligible']);
        $this->assertSame(20_000, $ok['amount']);
    }

    public function test_vouchers_expose_redemption_progress(): void
    {
        Voucher::factory()->create([
            'code' => 'HALF',
            'usage_limit' => 10,
            'used_count' => 6,
        ]);

        $voucher = collect($this->service()->availableFor(100_000)['vouchers'])
            ->firstWhere('code', 'HALF');

        $this->assertSame(10, $voucher['usage_limit']);
        $this->assertSame(6, $voucher['used_count']);
        $this->assertSame(4, $voucher['remaining']);
    }
}
