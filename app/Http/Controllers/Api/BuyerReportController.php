<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BuyerReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    #[OA\Get(
        path: '/api/v1/buyer/reports',
        tags: ['Buyer Reports'],
        summary: "The buyer's spending report: total spent, order count, breakdown by status",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Spending report')],
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->reports->buyerSpending($request->user()));
    }
}
