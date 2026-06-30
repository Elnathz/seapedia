<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function __construct(private readonly BannerService $banners) {}

    public function searchCategories(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = $data['q'] ?? '';

        $categories = \App\Models\Category::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'slug']);

        return response()->json($categories);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = $data['q'] ?? '';

        $products = \App\Models\Product::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'slug']);

        return response()->json($products);
    }

    public function searchStores(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = $data['q'] ?? '';

        $stores = \App\Models\Store::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'slug']);

        return response()->json($stores);
    }

    public function index(): Response
    {
        return Inertia::render('admin/banners/Index', ['banners' => $this->banners->listForAdmin()]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $this->banners->create($request->safe()->except('image'), $request->file('image'));
        Inertia::share('flash', ['toast' => ['type' => 'success', 'message' => 'Banner berhasil ditambahkan.']]);

        return to_route('admin.banners.index');
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $this->banners->update($banner, $request->safe()->except('image'), $request->file('image'));
        Inertia::share('flash', ['toast' => ['type' => 'success', 'message' => 'Banner berhasil diperbarui.']]);

        return to_route('admin.banners.index');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->banners->delete($banner);
        Inertia::share('flash', ['toast' => ['type' => 'success', 'message' => 'Banner berhasil dihapus.']]);

        return to_route('admin.banners.index');
    }
}
