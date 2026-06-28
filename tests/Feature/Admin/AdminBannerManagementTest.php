<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_banners_index(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        Banner::factory()->create(['title' => 'Promo Utama']);

        $response = $this->actingAs($admin)->get(route('admin.banners.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/banners/Index')
            ->has('banners', 1)
        );
    }

    public function test_non_admin_cannot_access_banners(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.banners.index'));

        $response->assertForbidden();
    }

    public function test_admin_stores_a_banner_with_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $image = UploadedFile::fake()->image('banner.jpg', 800, 320);

        $response = $this->actingAs($admin)->post(route('admin.banners.store'), [
            'placement' => 'main',
            'title' => 'Flash Sale',
            'sort_order' => 1,
            'is_active' => '1',
            'image' => $image,
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['title' => 'Flash Sale', 'placement' => 'main']);
        Storage::disk('public')->assertExists(Banner::latest()->first()->image_path);
    }

    public function test_admin_updates_a_banner(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $banner = Banner::factory()->create(['title' => 'Lama']);

        $response = $this->actingAs($admin)->put(route('admin.banners.update', $banner), [
            'placement' => 'side',
            'title' => 'Baru',
            'sort_order' => 2,
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['id' => $banner->id, 'title' => 'Baru', 'placement' => 'side']);
        $this->assertFalse($banner->refresh()->is_active);
    }

    public function test_admin_deletes_a_banner(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $banner = Banner::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.banners.destroy', $banner));

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    public function test_store_requires_image_on_create(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.banners.store'), [
            'placement' => 'main',
            'title' => 'Tanpa Gambar',
        ]);

        $response->assertSessionHasErrors('image');
    }
}
