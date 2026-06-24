<?php

namespace App\Http\Controllers\Web;

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
        return Inertia::render('buyer/orders/Index', [
            'orders' => $this->orders->forBuyer($request->user()),
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
