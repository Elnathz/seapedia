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
        $promoCode = $request->query('promo_code');
        $voucherCode = $request->query('voucher_code');

        $addresses = $user->addresses()->orderByDesc('is_default')->orderByDesc('id')->get();

        // The previewed delivery surcharge depends on which address the order
        // ships to, so previews are computed for the selected address (default
        // when none is chosen yet). Resolved from the user's own collection, so
        // an address_id that isn't theirs simply falls back to the default.
        $selectedAddress = $request->filled('address_id')
            ? $addresses->firstWhere('id', $request->integer('address_id'))
            : null;
        $selectedAddress ??= $addresses->first();

        // All three delivery methods are previewed up front (the formula is
        // cheap and only delivery_fee differs between them) so switching in
        // the UI never needs another round trip; every figure is still
        // server-computed. Applying a code or changing the address re-requests
        // this page with query params (Inertia partial reload) so the summary
        // stays server-computed.
        $previews = collect(DeliveryMethod::cases())
            ->mapWithKeys(fn (DeliveryMethod $method) => [
                $method->value => $this->checkout->preview($user, $method, $promoCode, $voucherCode, $selectedAddress),
            ]);

        return Inertia::render('buyer/checkout/Show', [
            'cart' => $this->carts->summary($user),
            'addresses' => $addresses,
            'previews' => $previews,
            'selectedAddressId' => $selectedAddress?->id,
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
            $data['promo_code'] ?? null,
            $data['voucher_code'] ?? null,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Order :code placed.', ['code' => $order->code]),
        ]);

        return to_route('buyer.cart.index');
    }
}
