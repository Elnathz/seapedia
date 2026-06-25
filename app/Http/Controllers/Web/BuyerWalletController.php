<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTopupRequest;
use App\Models\Topup;
use App\Services\TopupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyerWalletController extends Controller
{
    public function __construct(private readonly TopupService $topups) {}

    public function show(Request $request): Response
    {
        $wallet = $request->user()->wallet;

        return Inertia::render('buyer/wallet/Show', [
            'wallet' => ['balance' => $wallet->balance],
            'transactions' => $wallet->transactions()->latest('id')->paginate(20),
            'minTopupAmount' => config('payment.topup.min_amount'),
        ]);
    }

    public function store(StoreTopupRequest $request): RedirectResponse
    {
        $topup = $this->topups->create($request->user(), $request->validated()['amount']);

        return to_route('buyer.wallet.topup.show', $topup);
    }

    /**
     * The PSP-sim "processing" page (§ Sprint 6 T2) — polled by the
     * frontend until the gateway resolves the top-up.
     */
    public function topup(Topup $topup): Response
    {
        $this->authorize('view', $topup);

        $topup = $this->topups->checkStatus($topup);

        return Inertia::render('buyer/wallet/topup/Show', ['topup' => $topup]);
    }
}
