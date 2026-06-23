<?php

namespace Tests\Feature\Catalog;

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
