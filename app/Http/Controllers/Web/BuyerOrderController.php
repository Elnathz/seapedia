<?php

namespace App\Http\Controllers\Web;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyerOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $orderStatus = $status ? OrderStatus::tryFrom($status) : null;

        return Inertia::render('buyer/orders/Index', [
            'orders' => $this->orders->forBuyer($request->user(), 10, $orderStatus),
            'currentStatus' => $orderStatus?->value,
        ]);
    }

    public function show(int $order): Response
    {
        $fullOrder = $this->orders->findForBuyer($order);

        abort_if($fullOrder === null, 404);

        $this->authorize('view', $fullOrder);

        return Inertia::render('buyer/orders/Show', ['order' => $fullOrder]);
    }
}
