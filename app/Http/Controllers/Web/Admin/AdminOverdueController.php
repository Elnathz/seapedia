<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ClockService;
use App\Services\OverdueService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminOverdueController extends Controller
{
    public function __construct(
        private readonly OverdueService $overdue,
        private readonly ClockService $clock,
    ) {}

    public function index(): Response
    {
        $now = $this->clock->now();

        $overdueOrders = Order::query()
            ->whereNull('refunded_at')
            ->where('sla_due_at', '<', $now)
            ->whereIn('status', ['sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim'])
            ->with('buyer:id,name', 'store:id,name')
            ->orderBy('sla_due_at')
            ->paginate(15);

        return Inertia::render('admin/overdue/Index', [
            'orders' => $overdueOrders,
            'eligibleCount' => $this->overdue->eligibleCount(),
            'now' => $now->toIso8601String(),
            'isSimulated' => $this->clock->isSimulated(),
        ]);
    }

    /**
     * Run the overdue auto-refund sweep immediately at the current clock
     * (§5.9) — the direct "Proses refund sekarang" action so overdue orders
     * can be cleared without advancing the simulated day. Idempotent: the
     * sweep re-checks each order under a row lock, so a double-click never
     * double-refunds.
     */
    public function sweep(): RedirectResponse
    {
        $swept = $this->overdue->sweep();

        Inertia::flash('toast', [
            'type' => $swept['refunded_count'] > 0 ? 'success' : 'info',
            'message' => __(':count overdue order(s) refunded.', [
                'count' => $swept['refunded_count'],
            ]),
        ]);

        return back();
    }
}
