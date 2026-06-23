<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    private const IMAGE_DIRECTORY = 'products';

    /**
     * @param  array{name: string, description: ?string, price: int, stock: int}  $data
     */
    public function createForStore(Store $store, array $data, ?UploadedFile $image): Product
    {
        return Product::create([
            'store_id' => $store->id,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image_path' => $image ? $this->storeImage($image) : null,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array{name: string, description: ?string, price: int, stock: int}  $data
     */
    public function update(Product $product, array $data, ?UploadedFile $image): Product
    {
        $imagePath = $product->image_path;

        if ($image) {
            $this->deleteImage($imagePath);
            $imagePath = $this->storeImage($image);
        }

        $product->update([
            'name' => $data['name'],
            'slug' => $data['name'] === $product->name
                ? $product->slug
                : $this->uniqueSlug($data['name'], $product->id),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image_path' => $imagePath,
        ]);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $this->deleteImage($product->image_path);
        $product->delete();
    }

    private function storeImage(UploadedFile $image): string
    {
        return $image->store(self::IMAGE_DIRECTORY, 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
