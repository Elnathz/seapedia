<?php

namespace Tests\Feature\Store;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStorePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_page_lists_only_that_stores_active_products(): void
    {
        $store = Store::factory()->create(['is_active' => true]);
        $otherStore = Store::factory()->create(['is_active' => true]);

        $visible = Product::factory()->create(['store_id' => $store->id, 'is_active' => true]);
        Product::factory()->create(['store_id' => $store->id, 'is_active' => false]);
        Product::factory()->create(['store_id' => $otherStore->id, 'is_active' => true]);

        $response = $this->get(route('stores.show', $store->slug));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('store.id', $store->id)
            ->has('store.products', 1)
            ->where('store.products.0.id', $visible->id)
        );
    }

    public function test_inactive_store_returns_404(): void
    {
        $store = Store::factory()->create(['is_active' => false]);

        $response = $this->get(route('stores.show', $store->slug));

        $response->assertNotFound();
    }

    public function test_unknown_store_slug_returns_404(): void
    {
        $response = $this->get(route('stores.show', 'tidak-ada'));

        $response->assertNotFound();
    }
}
