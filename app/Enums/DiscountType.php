<?php

namespace App\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    /**
     * The §5.3 discount amount for this type against a subtotal, capped by
     * `max_discount` when set. Never exceeds the subtotal itself.
     */
    public function amountFor(int $value, int $subtotal, ?int $maxDiscount): int
    {
        $amount = match ($this) {
            self::Percentage => (int) round($subtotal * $value / 100),
            self::Fixed => $value,
        };

        if ($maxDiscount !== null) {
            $amount = min($amount, $maxDiscount);
        }

        return min($amount, $subtotal);
    }
}
