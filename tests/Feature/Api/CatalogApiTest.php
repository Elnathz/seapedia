<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_endpoint_returns_only_active_products(): void
    {
        $activeStore = Store::factory()->create(['is_active' => true]);
        $inactiveStore = Store::factory()->create(['is_active' => false]);

        $visible = Product::factory()->create(['store_id' => $activeStore->id, 'is_active' => true]);
        Product::factory()->create(['store_id' => $activeStore->id, 'is_active' => false]);
        Product::factory()->create(['store_id' => $inactiveStore->id, 'is_active' => true]);

        $response = $this->getJson(route('api.v1.catalog.index'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $visible->id);
        $response->assertJsonPath('total', 1);
    }

    public function test_catalog_show_endpoint_returns_product_by_slug(): void
    {
        $store = Store::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['store_id' => $store->id, 'is_active' => true]);

        $response = $this->getJson(route('api.v1.catalog.show', $product->slug));

        $response->assertOk();
        $response->assertJsonPath('id', $product->id);
        $response->assertJsonPath('store.id', $store->id);
    }

    public function test_catalog_show_endpoint_returns_404_for_inactive_product(): void
    {
        $store = Store::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['store_id' => $store->id, 'is_active' => false]);

        $response = $this->getJson(route('api.v1.catalog.show', $product->slug));

        $response->assertNotFound();
    }
}
