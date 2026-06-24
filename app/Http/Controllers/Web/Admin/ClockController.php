<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvanceClockRequest;
use App\Services\ClockService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ClockController extends Controller
{
    public function __construct(private readonly ClockService $clock) {}

    /**
     * Advance the simulated day by one tick (§5.7). Mirrored by
     * `php artisan seapedia:advance-day` for a headless trigger.
     */
    public function advance(AdvanceClockRequest $request): RedirectResponse
    {
        $next = $this->clock->advance(1);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Simulated day advanced to :date.', ['date' => $next->toDateString()]),
        ]);

        return back();
    }
}
