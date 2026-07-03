<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyerAddressController extends Controller
{
    public function __construct(private readonly AddressService $addresses) {}

    public function index(Request $request): Response
    {
        return Inertia::render('buyer/addresses/Index', [
            'addresses' => $request->user()
                ->addresses()
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $this->addresses->createForUser($request->user(), $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Address created.')]);

        // Return to wherever the form was submitted from — the addresses page
        // or an inline modal on checkout — so adding an address never yanks the
        // buyer out of their current flow.
        return back(fallback: route('buyer.addresses.index'));
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $this->addresses->update($address, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Address updated.')]);

        return to_route('buyer.addresses.index');
    }

    public function setDefault(Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $this->addresses->setDefault($address);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Default address updated.')]);

        return to_route('buyer.addresses.index');
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);

        $this->addresses->delete($address);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Address deleted.')]);

        return to_route('buyer.addresses.index');
    }
}
