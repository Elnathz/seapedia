<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromoRequest;
use App\Models\Promo;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PromoController extends Controller
{
    public function __construct(private readonly DiscountService $discounts) {}

    #[OA\Get(
        path: '/api/v1/admin/promos',
        tags: ['Admin Discounts'],
        summary: 'List all promos',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of promos'),
            new OA\Response(response: 403, description: 'Not an admin'),
        ],
    )]
    public function index(): JsonResponse
    {
        return response()->json(Promo::query()->latest('id')->get());
    }

    #[OA\Post(
        path: '/api/v1/admin/promos',
        tags: ['Admin Discounts'],
        summary: 'Generate a new promo code',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Promo created'),
            new OA\Response(response: 403, description: 'Not an admin'),
        ],
    )]
    public function store(StorePromoRequest $request): JsonResponse
    {
        return response()->json($this->discounts->createPromo($request->validated()), 201);
    }

    #[OA\Get(
        path: '/api/v1/admin/promos/{promo}',
        tags: ['Admin Discounts'],
        summary: 'View a single promo',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Promo detail'),
            new OA\Response(response: 403, description: 'Not an admin'),
        ],
    )]
    public function show(Promo $promo): JsonResponse
    {
        return response()->json($promo);
    }
}
