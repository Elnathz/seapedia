<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_only_returns_active_products_of_active_stores(): void
    {
        $activeStore = Store::factory()->create(['is_active' => true]);
        $inactiveStore = Store::factory()->create(['is_active' => false]);

        $visible = Product::factory()->create(['store_id' => $activeStore->id, 'is_active' => true]);
        Product::factory()->create(['store_id' => $activeStore->id, 'is_active' => false]);
        Product::factory()->create(['store_id' => $inactiveStore->id, 'is_active' => true]);

        $response = $this->get(route('catalog.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('products.data.0.id', $visible->id)
            ->where('products.total', 1)
        );
    }

    public function test_catalog_search_filters_by_name(): void
    {
        $store = Store::factory()->create(['is_active' => true]);
        $match = Product::factory()->create([
            'store_id' => $store->id,
            'is_active' => true,
            'name' => 'Kopi Susu Gula Aren',
        ]);
        Product::factory()->create([
            'store_id' => $store->id,
            'is_active' => true,
            'name' => 'Nasi Goreng Spesial',
        ]);

        $response = $this->get(route('catalog.index', ['q' => 'kopi']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('products.total', 1)
            ->where('products.data.0.id', $match->id)
        );
    }

    public function test_catalog_filters_by_category_subtree(): void
    {
        $store = Store::factory()->create(['is_active' => true]);

        $parent = Category::factory()->create(['slug' => 'minuman']);
        $kopi = Category::factory()->create(['parent_id' => $parent->id, 'slug' => 'kopi']);
        $teh = Category::factory()->create(['parent_id' => $parent->id, 'slug' => 'teh']);
        $other = Category::factory()->create(['slug' => 'elektronik']);

        $kopiProduct = Product::factory()->create(['store_id' => $store->id, 'is_active' => true, 'category_id' => $kopi->id]);
        Product::factory()->create(['store_id' => $store->id, 'is_active' => true, 'category_id' => $teh->id]);
        Product::factory()->create(['store_id' => $store->id, 'is_active' => true, 'category_id' => $other->id]);

        // A parent slug returns its whole subtree (kopi + teh), excluding others.
        $this->get(route('catalog.index', ['category' => 'minuman']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.total', 2)
                ->where('activeCategory.slug', 'minuman')
            );

        // A child slug returns only that leaf's products.
        $this->get(route('catalog.index', ['category' => 'kopi']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.total', 1)
                ->where('products.data.0.id', $kopiProduct->id)
                ->where('activeCategory.parent.slug', 'minuman')
            );
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $store = Store::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['store_id' => $store->id, 'is_active' => false]);

        $response = $this->get(route('catalog.show', $product->slug));

        $response->assertNotFound();
    }

    public function test_product_of_inactive_store_returns_404(): void
    {
        $store = Store::factory()->create(['is_active' => false]);
        $product = Product::factory()->create(['store_id' => $store->id, 'is_active' => true]);

        $response = $this->get(route('catalog.show', $product->slug));

        $response->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $response = $this->get(route('catalog.show', 'tidak-ada'));

        $response->assertNotFound();
    }
}
