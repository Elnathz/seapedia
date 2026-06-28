<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ClockService;
use App\Services\OverdueService;
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
        ]);
    }
}
