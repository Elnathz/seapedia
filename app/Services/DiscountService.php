<?php

namespace App\Services;

use App\Models\Promo;
use App\Models\Voucher;

class DiscountService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createPromo(array $data): Promo
    {
        return Promo::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createVoucher(array $data): Voucher
    {
        return Voucher::create([...$data, 'used_count' => 0]);
    }
}
