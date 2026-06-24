<?php

namespace App\Http\Controllers\Web;

use App\Enums\DeliveryMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Address;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly CartService $carts,
    ) {}

    public function show(Request $request): Response
    {
        $user = $request->user();

        // All three delivery methods are previewed up front (the formula is
        // cheap and only delivery_fee differs between them) so switching in
        // the UI never needs another round trip; every figure is still
        // server-computed.
        $previews = collect(DeliveryMethod::cases())
            ->mapWithKeys(fn (DeliveryMethod $method) => [
                $method->value => $this->checkout->preview($user, $method),
            ]);

        return Inertia::render('buyer/checkout/Show', [
            'cart' => $this->carts->summary($user),
            'addresses' => $user->addresses()->orderByDesc('is_default')->orderByDesc('id')->get(),
            'previews' => $previews,
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $address = Address::findOrFail($data['address_id']);
        $this->authorize('update', $address);

        $order = $this->checkout->commit(
            $request->user(),
            $address,
            DeliveryMethod::from($data['delivery_method']),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Order :code placed.', ['code' => $order->code]),
        ]);

        return to_route('buyer.cart.index');
    }
}
