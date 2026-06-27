<?php

namespace Tests\Feature\Product;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function sellerWithStore(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => RoleName::Seller->value]);
        $user->roles()->attach($role->id);
        Store::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function actingAsSeller(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Seller->value]);
    }

    public function test_seller_can_create_a_product_with_an_image(): void
    {
        Storage::fake('public');
        $seller = $this->sellerWithStore();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAsSeller($seller)->post(route('seller.products.store'), [
            'name' => 'Kopi Susu',
            'description' => 'Kopi susu segar.',
            'category_id' => Category::factory()->create()->id,
            'price' => 18000,
            'stock' => 20,
            'image' => $image,
        ]);

        $response->assertRedirect(route('seller.products.index'));
        $product = Product::query()->where('name', 'Kopi Susu')->first();
        $this->assertNotNull($product);
        $this->assertSame(18000, $product->price);
        $this->assertSame(20, $product->stock);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_invalid_price_and_stock_are_rejected(): void
    {
        $seller = $this->sellerWithStore();

        $response = $this->actingAsSeller($seller)->post(route('seller.products.store'), [
            'name' => 'Produk Aneh',
            'price' => -100,
            'stock' => -5,
        ]);

        $response->assertSessionHasErrors(['price', 'stock']);
        $this->assertDatabaseMissing('products', ['name' => 'Produk Aneh']);
    }

    public function test_seller_can_update_their_own_product(): void
    {
        $seller = $this->sellerWithStore();
        $product = Product::factory()->create(['store_id' => $seller->store->id]);

        $response = $this->actingAsSeller($seller)->put(route('seller.products.update', $product), [
            'name' => 'Nama Baru',
            'description' => 'Deskripsi baru.',
            'category_id' => $product->category_id,
            'price' => 25000,
            'stock' => 10,
        ]);

        $response->assertRedirect(route('seller.products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Baru',
            'price' => 25000,
        ]);
    }

    public function test_seller_can_delete_their_own_product(): void
    {
        $seller = $this->sellerWithStore();
        $product = Product::factory()->create(['store_id' => $seller->store->id]);

        $response = $this->actingAsSeller($seller)->delete(route('seller.products.destroy', $product));

        $response->assertRedirect(route('seller.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_cross_seller_product_update_is_forbidden(): void
    {
        $owner = $this->sellerWithStore();
        $product = Product::factory()->create(['store_id' => $owner->store->id]);

        $intruder = $this->sellerWithStore();

        $response = $this->actingAsSeller($intruder)->put(route('seller.products.update', $product), [
            'name' => 'Diretas',
            'category_id' => $product->category_id,
            'price' => 100,
            'stock' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => $product->name]);
    }

    public function test_cross_seller_product_delete_is_forbidden(): void
    {
        $owner = $this->sellerWithStore();
        $product = Product::factory()->create(['store_id' => $owner->store->id]);

        $intruder = $this->sellerWithStore();

        $response = $this->actingAsSeller($intruder)->delete(route('seller.products.destroy', $product));

        $response->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
