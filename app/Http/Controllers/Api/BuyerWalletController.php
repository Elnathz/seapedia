<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTopupRequest;
use App\Services\TopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BuyerWalletController extends Controller
{
    public function __construct(private readonly TopupService $topups) {}

    #[OA\Get(
        path: '/api/v1/buyer/wallet',
        tags: ['Buyer Wallet'],
        summary: "Get the buyer's wallet balance and transaction history",
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Wallet balance and paginated transactions'),
        ],
    )]
    public function show(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet;

        return response()->json([
            'balance' => $wallet->balance,
            'transactions' => $wallet->transactions()->latest('id')->paginate(20),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/buyer/wallet/topup',
        tags: ['Buyer Wallet'],
        summary: 'Create a top-up (the fake gateway credits instantly)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Topup created and processed'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreTopupRequest $request): JsonResponse
    {
        $topup = $this->topups->create($request->user(), $request->validated()['amount']);

        return response()->json($topup, 201);
    }
}
