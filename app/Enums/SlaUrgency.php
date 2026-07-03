<?php

namespace App\Enums;

use Carbon\CarbonImmutable;

/**
 * How close an active order is to its SLA deadline (`sla_due_at`). Once an
 * order passes that deadline the overdue sweep auto-refunds it to
 * `Dikembalikan` (§6C, OverdueService) — so a near-deadline order is a sale
 * about to be lost. This is the "near-cancel" signal the seller and driver
 * views float to the top.
 *
 * The threshold lives here on the server (golden rule 17), never in Vue: one
 * simulated day-tick (§5.7). Because admin time only ever jumps a whole tick
 * at a time (seapedia:advance-day), any active order with under one tick of
 * slack could be swept on the very next advance — that is Critical.
 */
enum SlaUrgency: string
{
    case Overdue = 'overdue';
    case Critical = 'critical';
    case Normal = 'normal';

    /** One simulated day-tick in seconds (§5.7: 1 tick = 1 day). */
    private const int TICK_SECONDS = 86_400;

    /**
     * Classify an order by its deadline. Final orders (already Selesai /
     * Dikembalikan) are never at risk, so they are always Normal.
     */
    public static function forDueDate(?CarbonImmutable $dueAt, CarbonImmutable $now, bool $isFinal): self
    {
        if ($isFinal || $dueAt === null) {
            return self::Normal;
        }

        $seconds = $dueAt->getTimestamp() - $now->getTimestamp();

        return match (true) {
            $seconds <= 0 => self::Overdue,
            $seconds <= self::TICK_SECONDS => self::Critical,
            default => self::Normal,
        };
    }

    /**
     * Whole day-ticks left until the deadline, rounded up (negative once
     * overdue). Presentation hint for a "berakhir dalam N hari" label — the
     * urgency colour comes from the case, this is just the number.
     */
    public static function ticksRemaining(?CarbonImmutable $dueAt, CarbonImmutable $now): int
    {
        if ($dueAt === null) {
            return 0;
        }

        return (int) ceil(($dueAt->getTimestamp() - $now->getTimestamp()) / self::TICK_SECONDS);
    }
}
