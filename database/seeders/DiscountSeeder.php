<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Services\DiscountService;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function __construct(private readonly DiscountService $discounts) {}

    /**
     * Level 4 demo codes (§12): one active voucher, one active promo, and
     * one expired code of each kind so the "rejected with a clear message"
     * path is demoable too.
     */
    public function run(): void
    {
        $this->discounts->createVoucher([
            'code' => 'HEMAT10',
            'type' => DiscountType::Percentage,
            'value' => 10,
            'max_discount' => 20_000,
            'min_spend' => null,
            'expiry_date' => now()->addMonths(2),
            'usage_limit' => 5,
            'is_active' => true,
        ]);

        $this->discounts->createVoucher([
            'code' => 'EXPIRED5K',
            'type' => DiscountType::Fixed,
            'value' => 5_000,
            'max_discount' => null,
            'min_spend' => null,
            'expiry_date' => now()->subDay(),
            'usage_limit' => 5,
            'is_active' => true,
        ]);

        $this->discounts->createPromo([
            'code' => 'PROMO20K',
            'type' => DiscountType::Fixed,
            'value' => 20_000,
            'max_discount' => null,
            'min_spend' => 100_000,
            'expiry_date' => now()->addMonths(2),
            'is_active' => true,
        ]);

        $this->discounts->createPromo([
            'code' => 'EXPIREDPROMO',
            'type' => DiscountType::Percentage,
            'value' => 15,
            'max_discount' => 30_000,
            'min_spend' => null,
            'expiry_date' => now()->subDay(),
            'is_active' => true,
        ]);
    }
}
