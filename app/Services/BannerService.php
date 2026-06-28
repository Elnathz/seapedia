<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    private const IMAGE_DIRECTORY = 'banners';

    /** @return Collection<int, Banner> */
    public function mainActive(): Collection
    {
        return Banner::query()->active()->where('placement', 'main')->orderBy('sort_order')->get();
    }

    /** @return Collection<int, Banner> */
    public function sideActive(): Collection
    {
        return Banner::query()->active()->where('placement', 'side')->orderBy('sort_order')->limit(4)->get();
    }

    /** @return array{main: Collection<int, Banner>, side: Collection<int, Banner>} */
    public function forStorefront(): array
    {
        return ['main' => $this->mainActive(), 'side' => $this->sideActive()];
    }

    /** @return Collection<int, Banner> */
    public function listForAdmin(): Collection
    {
        return Banner::query()->orderBy('placement')->orderBy('sort_order')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, ?UploadedFile $image): Banner
    {
        $data['image_path'] = $image
            ? $image->store(self::IMAGE_DIRECTORY, 'public')
            : ($data['image_path'] ?? '');

        return Banner::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Banner $banner, array $data, ?UploadedFile $image): Banner
    {
        if ($image) {
            $old = $banner->image_path;
            $data['image_path'] = $image->store(self::IMAGE_DIRECTORY, 'public');
            if ($old && ! str_starts_with($old, 'images/')) {
                Storage::disk('public')->delete($old);
            }
        }
        $banner->update($data);

        return $banner->refresh();
    }

    public function delete(Banner $banner): void
    {
        if ($banner->image_path && ! str_starts_with($banner->image_path, 'images/')) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();
    }
}
