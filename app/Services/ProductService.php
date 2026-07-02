<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    private const IMAGE_DIRECTORY = 'products';

    public function createForStore(Store $store, array $data): Product
    {
        return DB::transaction(function () use ($store, $data) {
            $hasVariants = $data['has_variants'] ?? false;

            // Calculate base price, stock and weight from variants if has_variants
            $basePrice = $hasVariants ? (int) min(array_column($data['variants'], 'price')) : $data['price'];
            $totalStock = $hasVariants ? (int) array_sum(array_column($data['variants'], 'stock')) : $data['stock'];
            $baseWeight = $hasVariants ? (int) min(array_column($data['variants'], 'weight')) : ($data['weight'] ?? null);

            $product = Product::create([
                'store_id' => $store->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'price' => $basePrice,
                'stock' => $totalStock,
                'weight' => $baseWeight,
                'image_path' => null, // Will update after images are processed
                'is_active' => true,
            ]);

            $createdVariants = [];
            // Handle variants
            if ($hasVariants && ! empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $createdVariants[] = $product->variants()->create([
                        'variant_type' => null,
                        'name' => $variantData['name'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'weight' => $variantData['weight'],
                        'is_active' => true,
                    ]);
                }
            }

            // Handle images
            if (! empty($data['images'])) {
                foreach ($data['images'] as $index => $image) {
                    $path = $this->storeImage($image);

                    // Find if any variant uses this image
                    $variantId = null;
                    if ($hasVariants && ! empty($data['variants'])) {
                        foreach ($data['variants'] as $vIndex => $variantData) {
                            if (isset($variantData['image_index']) && (int) $variantData['image_index'] === $index) {
                                $variantId = $createdVariants[$vIndex]->id;
                                break;
                            }
                        }
                    }

                    $product->images()->create([
                        'product_variant_id' => $variantId,
                        'image_path' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);

                    if ($index === 0) {
                        $product->update(['image_path' => $path]);
                    }
                }
            }

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $hasVariants = $data['has_variants'] ?? false;

            $basePrice = $hasVariants ? (int) min(array_column($data['variants'], 'price')) : $data['price'];
            $totalStock = $hasVariants ? (int) array_sum(array_column($data['variants'], 'stock')) : $data['stock'];
            $baseWeight = $hasVariants ? (int) min(array_column($data['variants'], 'weight')) : ($data['weight'] ?? null);

            $product->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $data['name'] === $product->name
                    ? $product->slug
                    : $this->uniqueSlug($data['name'], $product->id),
                'description' => $data['description'] ?? null,
                'price' => $basePrice,
                'stock' => $totalStock,
                'weight' => $baseWeight,
            ]);

            // Handle deleted images
            if (! empty($data['deleted_image_ids'])) {
                $imagesToDelete = $product->images()->whereIn('id', $data['deleted_image_ids'])->get();
                foreach ($imagesToDelete as $img) {
                    $this->deleteImage($img->image_path);
                    $img->delete();
                }
            }

            // Handle variants update (sync)
            $existingVariantIds = [];
            $createdVariants = [];

            if ($hasVariants && ! empty($data['variants'])) {
                foreach ($data['variants'] as $vIndex => $variantData) {
                    if (! empty($variantData['id'])) {
                        // Update existing
                        $variant = $product->variants()->find($variantData['id']);
                        if ($variant) {
                            $variant->update([
                                'name' => $variantData['name'],
                                'price' => $variantData['price'],
                                'stock' => $variantData['stock'],
                                'weight' => $variantData['weight'],
                            ]);
                            $existingVariantIds[] = $variant->id;
                            $createdVariants[$vIndex] = $variant;
                        }
                    } else {
                        // Create new
                        $variant = $product->variants()->create([
                            'variant_type' => null,
                            'name' => $variantData['name'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            'weight' => $variantData['weight'],
                            'is_active' => true,
                        ]);
                        $existingVariantIds[] = $variant->id;
                        $createdVariants[$vIndex] = $variant;
                    }
                }
            }

            // Delete removed variants
            $product->variants()->whereNotIn('id', $existingVariantIds)->delete();

            // Handle new images
            if (! empty($data['images'])) {
                $maxSortOrder = $product->images()->max('sort_order') ?? -1;

                foreach ($data['images'] as $index => $image) {
                    $path = $this->storeImage($image);
                    $sortOrder = $maxSortOrder + 1 + $index;

                    // Note: mapping new images to variants during update can be tricky if the UI mixes existing/new images
                    // We map by image_index if provided
                    $variantId = null;
                    if ($hasVariants && ! empty($data['variants'])) {
                        foreach ($data['variants'] as $vIndex => $variantData) {
                            if (isset($variantData['image_index']) && (int) $variantData['image_index'] === $index) {
                                if (isset($createdVariants[$vIndex])) {
                                    $variantId = $createdVariants[$vIndex]->id;
                                }
                                break;
                            }
                        }
                    }

                    $product->images()->create([
                        'product_variant_id' => $variantId,
                        'image_path' => $path,
                        'is_primary' => false, // Set to false initially, we can re-evaluate primary image later
                        'sort_order' => $sortOrder,
                    ]);
                }
            }

            // Ensure primary image exists and is set on product
            $primaryImage = $product->images()->orderBy('sort_order')->first();
            if ($primaryImage) {
                $product->images()->whereNot('id', $primaryImage->id)->update(['is_primary' => false]);
                $primaryImage->update(['is_primary' => true]);
                $product->update(['image_path' => $primaryImage->image_path]);
            } else {
                $product->update(['image_path' => null]);
            }

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        foreach ($product->images as $img) {
            $this->deleteImage($img->image_path);
        }
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
