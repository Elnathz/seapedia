<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvanceClockRequest;
use App\Services\ClockService;
use App\Services\OverdueService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ClockController extends Controller
{
    public function __construct(
        private readonly ClockService $clock,
        private readonly OverdueService $overdue,
    ) {}

    /**
     * Advance the simulated day by N ticks (default 1) and run the overdue
     * sweep (§5.7, §5.9). Mirrored by `php artisan seapedia:advance-day` for a
     * headless trigger.
     */
    public function advance(AdvanceClockRequest $request): RedirectResponse
    {
        $days = max(1, (int) $request->integer('days', 1));
        $next = $this->clock->advance($days);
        $swept = $this->overdue->sweep();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Simulated day advanced to :date. :count overdue order(s) refunded.', [
                'date' => $next->toDateString(),
                'count' => $swept['refunded_count'],
            ]),
        ]);

        return back();
    }

    /**
     * Reset the simulated clock back to real "today" (§5.7). Advancing only
     * ever moves time forward, so this is the way back after a demo jump.
     */
    public function reset(): RedirectResponse
    {
        $this->clock->reset();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Simulated clock reset to today (:date).', [
                'date' => $this->clock->now()->toDateString(),
            ]),
        ]);

        return back();
    }
}
