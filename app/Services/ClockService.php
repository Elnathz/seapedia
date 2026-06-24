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
}
