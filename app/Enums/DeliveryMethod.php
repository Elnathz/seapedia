<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Instant = 'instant';
    case NextDay = 'next_day';
    case Regular = 'regular';

    /**
     * Base delivery fee in IDR (§5.4). The final fee adds a distance and a
     * weight component (DeliveryFeeService); the base keeps every method
     * distinct even at 0 km / minimum weight, satisfying spec line 278.
     */
    public function fee(): int
    {
        return match ($this) {
            self::Instant => 20_000,
            self::NextDay => 10_000,
            self::Regular => 5_000,
        };
    }

    /**
     * Per-kilometre rate in IDR (§5.4). Also differs per method, so a faster
     * method is more expensive at the same distance.
     */
    public function ratePerKm(): int
    {
        return match ($this) {
            self::Instant => 2_500,
            self::NextDay => 1_500,
            self::Regular => 1_000,
        };
    }

    /**
     * SLA in day-ticks (§5.4, locked) — one tick = one simulated day (§5.7).
     */
    public function slaTicks(): int
    {
        return match ($this) {
            self::Instant => 1,
            self::NextDay => 2,
            self::Regular => 4,
        };
    }
}
