<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Instant = 'instant';
    case NextDay = 'next_day';
    case Regular = 'regular';

    /**
     * Delivery fee in IDR (§5.4, locked).
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
