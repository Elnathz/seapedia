<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Voucher;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class VoucherController extends Controller
{
    public function __construct(private readonly DiscountService $discounts) {}

    #[OA\Get(
        path: '/api/v1/admin/vouchers',
        tags: ['Admin Discounts'],
        summary: 'List all vouchers',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'List of vouchers')],
    )]
    public function index(): JsonResponse
    {
        return response()->json(Voucher::query()->latest('id')->get());
    }

    #[OA\Post(
        path: '/api/v1/admin/vouchers',
        tags: ['Admin Discounts'],
        summary: 'Generate a new voucher code',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Voucher created'),
            new OA\Response(response: 403, description: 'Not an admin'),
        ],
    )]
    public function store(StoreVoucherRequest $request): JsonResponse
    {
        return response()->json($this->discounts->createVoucher($request->validated()), 201);
    }

    #[OA\Get(
        path: '/api/v1/admin/vouchers/{voucher}',
        tags: ['Admin Discounts'],
        summary: 'View a single voucher',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Voucher detail')],
    )]
    public function show(Voucher $voucher): JsonResponse
    {
        return response()->json($voucher);
    }
}
