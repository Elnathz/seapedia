<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_create_a_category(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'name' => 'Minuman',
            'icon' => 'CupSoda',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Minuman', 'slug' => 'minuman', 'parent_id' => null]);
    }

    public function test_admin_can_create_a_subcategory(): void
    {
        $parent = Category::factory()->create(['slug' => 'minuman']);

        $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'name' => 'Kopi',
            'parent_id' => $parent->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', ['name' => 'Kopi', 'parent_id' => $parent->id]);
    }

    public function test_admin_can_update_a_category(): void
    {
        $category = Category::factory()->create(['name' => 'Lama', 'slug' => 'lama']);

        $this->actingAs($this->admin())->put(route('admin.categories.update', $category), [
            'name' => 'Baru',
            'icon' => 'Package',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Baru', 'slug' => 'baru']);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['store_id' => $store->id, 'category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $parent))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_non_admin_cannot_manage_categories(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->post(route('admin.categories.store'), ['name' => 'Nakal'])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['name' => 'Nakal']);
    }
}
