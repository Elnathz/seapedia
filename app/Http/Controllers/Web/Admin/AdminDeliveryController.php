<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDeliveryController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->trim()->value();

        $deliveries = Delivery::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with('driver:id,name', 'order:id,status,store_id,buyer_id', 'order.store:id,name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/deliveries/Index', [
            'deliveries' => $deliveries,
            'filters' => ['status' => $status],
            'statuses' => array_column(DeliveryStatus::cases(), 'value'),
        ]);
    }
}
