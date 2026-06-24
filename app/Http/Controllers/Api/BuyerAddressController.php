<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BuyerAddressController extends Controller
{
    public function __construct(private readonly AddressService $addresses) {}

    #[OA\Get(
        path: '/api/v1/buyer/addresses',
        tags: ['Buyer Addresses'],
        summary: "List the buyer's delivery addresses",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'List of addresses')],
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->addresses()->orderByDesc('is_default')->orderByDesc('id')->get(),
        );
    }

    #[OA\Post(
        path: '/api/v1/buyer/addresses',
        tags: ['Buyer Addresses'],
        summary: 'Create a delivery address',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Address created')],
    )]
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addresses->createForUser($request->user(), $request->validated());

        return response()->json($address, 201);
    }

    #[OA\Put(
        path: '/api/v1/buyer/addresses/{address}',
        tags: ['Buyer Addresses'],
        summary: 'Update a delivery address',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Address updated'),
            new OA\Response(response: 403, description: 'Not the owner'),
        ],
    )]
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        return response()->json($this->addresses->update($address, $request->validated()));
    }

    #[OA\Patch(
        path: '/api/v1/buyer/addresses/{address}/default',
        tags: ['Buyer Addresses'],
        summary: 'Set an address as the default',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Default address updated')],
    )]
    public function setDefault(Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        return response()->json($this->addresses->setDefault($address));
    }

    #[OA\Delete(
        path: '/api/v1/buyer/addresses/{address}',
        tags: ['Buyer Addresses'],
        summary: 'Delete a delivery address',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 204, description: 'Address deleted')],
    )]
    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $this->addresses->delete($address);

        return response()->json(null, 204);
    }
}
