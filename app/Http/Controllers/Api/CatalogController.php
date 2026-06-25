<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'SEAPEDIA API',
    description: 'Campus marketplace API — multi-role (buyer/seller/driver) with Sanctum Bearer auth.',
)]
#[OA\Server(url: 'http://localhost', description: 'Local dev (Sail)')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum token — obtain from POST /api/v1/login',
)]
class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    /**
     * Public catalog (TDD §8 "GET /api/v1/catalog") — same active-only data
     * as the web catalog, both backed by `CatalogService`.
     */
    #[OA\Get(
        path: '/api/v1/catalog',
        tags: ['Catalog'],
        summary: 'List active products in the public catalog',
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: false,
                description: 'Filter by product name (max 200 chars)',
                schema: new OA\Schema(type: 'string', maxLength: 200),
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of active products'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $search = $request->string('q')->value() ?: null;

        return response()->json($this->catalog->index($search));
    }

    /**
     * Public product detail by slug (TDD §8 "GET /api/v1/catalog/{slug}").
     */
    #[OA\Get(
        path: '/api/v1/catalog/{slug}',
        tags: ['Catalog'],
        summary: 'Get a single active product by slug',
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product detail'),
            new OA\Response(response: 404, description: 'Product not found'),
        ],
    )]
    public function show(string $slug): JsonResponse
    {
        $product = $this->catalog->find($slug);

        abort_if($product === null, 404);

        return response()->json($product);
    }
}
