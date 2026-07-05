<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\CarbonImmutable;

class ClockService
{
    /**
     * The only source of "now" for business logic (§5.7) — returns the
     * simulated clock if an admin has advanced it, otherwise real time.
     * Centralizing this here is what makes time-simulation (Sprint 5)
     * trivial: nothing else ever reads the wall clock directly.
     */
    public function now(): CarbonImmutable
    {
        $simulatedNow = Setting::query()->where('key', 'simulated_now')->value('value');

        return $simulatedNow ? CarbonImmutable::parse($simulatedNow) : CarbonImmutable::now();
    }

    /**
     * Advance the simulated clock by N ticks (§5.7: 1 tick = 1 day),
     * initializing from real time if no admin has touched it yet. Safe to
     * call repeatedly (golden rule 15) — it only ever moves time forward.
     */
    public function advance(int $ticks = 1): CarbonImmutable
    {
        $next = $this->now()->addDays($ticks);

        Setting::query()->updateOrCreate(
            ['key' => 'simulated_now'],
            ['value' => $next->toDateTimeString()],
        );

        return $next;
    }

    /**
     * Drop the simulated clock so `now()` returns real wall-time again — the
     * "kembali ke hari ini" reset. `advance()` only ever moves forward, so this
     * is the sole way back to today. Safe to call when nothing is simulated.
     */
    public function reset(): void
    {
        Setting::query()->where('key', 'simulated_now')->delete();
    }

    /**
     * Whether an admin has advanced the clock (so `now()` is simulated, not
     * real time). Drives the admin UI's "simulasi aktif" badge + reset button.
     */
    public function isSimulated(): bool
    {
        return Setting::query()->where('key', 'simulated_now')->exists();
    }
}
