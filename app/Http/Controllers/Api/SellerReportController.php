<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SellerReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    #[OA\Get(
        path: '/api/v1/seller/reports',
        tags: ['Seller Reports'],
        summary: "The seller's income report: total income, incoming/processed counts, breakdown by status",
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Income report'),
            new OA\Response(response: 404, description: 'Seller has no store yet'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        abort_if($store === null, 404);

        return response()->json($this->reports->sellerIncome($store));
    }
}
