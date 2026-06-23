<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerStoreController extends Controller
{
    public function __construct(private readonly StoreService $stores) {}

    /**
     * Onboarding (no store yet) or edit form (store exists) — same page,
     * the Vue component branches on whether `store` is null (§7 one store
     * per seller).
     */
    public function show(Request $request): Response
    {
        return Inertia::render('seller/store/Show', [
            'store' => $request->user()->store,
        ]);
    }

    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Store::class);

        $this->stores->createForUser($request->user(), $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Store created.')]);

        return to_route('seller.store.show');
    }

    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $this->authorize('update', $store);

        $this->stores->update($store, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Store updated.')]);

        return to_route('seller.store.show');
    }
}
