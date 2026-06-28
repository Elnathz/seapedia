<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('q')->trim()->value();
        $status = $request->string('status')->trim()->value();

        $orders = Order::query()
            ->when($search, fn ($q) => $q->where('id', 'like', "%{$search}%"))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with('buyer:id,name', 'store:id,name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
            'filters' => ['q' => $search, 'status' => $status],
            'statuses' => array_column(OrderStatus::cases(), 'value'),
        ]);
    }
}
