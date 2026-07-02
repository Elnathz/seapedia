<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Promo;
use App\Models\Voucher;
use App\Services\DiscountService;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function __construct(private readonly DiscountService $discounts) {}

    /**
     * Level 4 demo codes (§12): active + expired/used-up variants of both
     * promo and voucher so every eligibility path is demoable immediately
     * after `migrate:fresh --seed`.
     */
    public function run(): void
    {
        // --- Vouchers ---
        Voucher::query()->firstOrCreate(
            ['code' => 'HEMAT10'],
            [
                'type' => DiscountType::Percentage,
                'value' => 10,
                'max_discount' => 20_000,
                'min_spend' => null,
                'expiry_date' => now()->addMonths(2),
                'usage_limit' => 5,
                'is_active' => true,
                'used_count' => 0,
            ]
        );

        // Expired voucher — rejected with "expired" message.
        Voucher::query()->firstOrCreate(
            ['code' => 'EXPIRED5K'],
            [
                'type' => DiscountType::Fixed,
                'value' => 5_000,
                'max_discount' => null,
                'min_spend' => null,
                'expiry_date' => now()->subDay(),
                'usage_limit' => 5,
                'is_active' => true,
                'used_count' => 0,
            ]
        );

        // Used-up voucher — rejected with "fully redeemed" message.
        $voucher = Voucher::query()->firstOrCreate(
            ['code' => 'HABIS'],
            [
                'type' => DiscountType::Fixed,
                'value' => 15_000,
                'max_discount' => null,
                'min_spend' => null,
                'expiry_date' => now()->addMonth(),
                'usage_limit' => 1,
                'is_active' => true,
                'used_count' => 1,
            ]
        );
        $voucher->update(['used_count' => 1]);

        // Inactive voucher.
        Voucher::query()->firstOrCreate(
            ['code' => 'NONAKTIF'],
            [
                'type' => DiscountType::Percentage,
                'value' => 20,
                'max_discount' => 30_000,
                'min_spend' => null,
                'expiry_date' => now()->addMonth(),
                'usage_limit' => 10,
                'is_active' => false,
                'used_count' => 0,
            ]
        );

        // --- Promos ---
        Promo::query()->firstOrCreate(
            ['code' => 'PROMO20K'],
            [
                'type' => DiscountType::Fixed,
                'value' => 20_000,
                'max_discount' => null,
                'min_spend' => 100_000,
                'expiry_date' => now()->addMonths(2),
                'is_active' => true,
            ]
        );

        // Expired promo — rejected with "expired" message.
        Promo::query()->firstOrCreate(
            ['code' => 'EXPIREDPROMO'],
            [
                'type' => DiscountType::Percentage,
                'value' => 15,
                'max_discount' => 30_000,
                'min_spend' => null,
                'expiry_date' => now()->subDay(),
                'is_active' => true,
            ]
        );

        // Inactive promo.
        Promo::query()->firstOrCreate(
            ['code' => 'INACTIVE10'],
            [
                'type' => DiscountType::Percentage,
                'value' => 10,
                'max_discount' => 20_000,
                'min_spend' => null,
                'expiry_date' => now()->addMonth(),
                'is_active' => false,
            ]
        );

        // Active promo with no minimum spend.
        Promo::query()->firstOrCreate(
            ['code' => 'HEMAT50'],
            [
                'type' => DiscountType::Fixed,
                'value' => 5_000,
                'max_discount' => null,
                'min_spend' => null,
                'expiry_date' => now()->addWeeks(3),
                'is_active' => true,
            ]
        );
    }
}
