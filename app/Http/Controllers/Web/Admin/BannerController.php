<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function __construct(private readonly BannerService $banners) {}

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
