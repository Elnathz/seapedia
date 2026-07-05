<?php

namespace Tests\Feature\Store;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreManagementTest extends TestCase
{
    use RefreshDatabase;

    private function seller(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => RoleName::Seller->value]);
        $user->roles()->attach($role->id);

        return $user;
    }

    private function actingAsSeller(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Seller->value]);
    }

    public function test_seller_with_no_store_sees_onboarding(): void
    {
        $seller = $this->seller();

        $response = $this->actingAsSeller($seller)->get(route('seller.store.show'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('store', null));
    }

    public function test_seller_can_create_a_store(): void
    {
        $seller = $this->seller();

        $response = $this->actingAsSeller($seller)->post(route('seller.store.store'), [
            'name' => 'Toko Berkah',
            'description' => 'Toko kelontong kampus.',
            'origin_latitude' => -7.05,
            'origin_longitude' => 110.44,
        ]);

        $response->assertRedirect(route('seller.store.show'));
        $this->assertDatabaseHas('stores', [
            'user_id' => $seller->id,
            'name' => 'Toko Berkah',
            'slug' => 'toko-berkah',
        ]);
    }

    public function test_seller_who_already_has_a_store_cannot_create_another(): void
    {
        $seller = $this->seller();
        Store::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAsSeller($seller)->post(route('seller.store.store'), [
            'name' => 'Toko Kedua',
            'description' => 'Toko lain.',
            'origin_latitude' => -7.05,
            'origin_longitude' => 110.44,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('stores', ['name' => 'Toko Kedua']);
    }

    public function test_duplicate_store_name_is_rejected(): void
    {
        Store::factory()->create(['name' => 'Toko Berkah']);
        $seller = $this->seller();

        $response = $this->actingAsSeller($seller)->post(route('seller.store.store'), [
            'name' => 'Toko Berkah',
            'description' => 'Toko lain.',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('stores', ['user_id' => $seller->id]);
    }

    public function test_seller_can_update_their_own_store(): void
    {
        $seller = $this->seller();
        $store = Store::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAsSeller($seller)->put(route('seller.store.update', $store), [
            'name' => 'Toko Berkah Baru',
            'description' => 'Deskripsi baru.',
            'origin_latitude' => -7.05,
            'origin_longitude' => 110.44,
        ]);

        $response->assertRedirect(route('seller.store.show'));
        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'Toko Berkah Baru',
        ]);
    }

    public function test_seller_can_set_store_address_and_origin(): void
    {
        $seller = $this->seller();
        $store = Store::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAsSeller($seller)->put(route('seller.store.update', $store), [
            'name' => $store->name,
            'description' => 'Toko lengkap.',
            'full_address' => 'Jl. Prof. Soedarto No. 13, RT 02/RW 05',
            'province' => 'Jawa Tengah',
            'city' => 'Kota Semarang',
            'district' => 'Tembalang',
            'village' => 'Bulusan',
            'postal_code' => '50277',
            'origin_latitude' => -7.0505,
            'origin_longitude' => 110.4381,
        ]);

        $response->assertRedirect(route('seller.store.show'));
        $store->refresh();
        $this->assertSame('Jl. Prof. Soedarto No. 13, RT 02/RW 05', $store->full_address);
        $this->assertSame('Kota Semarang', $store->city);
        $this->assertSame('50277', $store->postal_code);
        $this->assertEqualsWithDelta(-7.0505, (float) $store->origin_latitude, 0.0001);
    }

    public function test_seller_can_upload_a_store_logo(): void
    {
        Storage::fake('public');
        $seller = $this->seller();
        $store = Store::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAsSeller($seller)->put(route('seller.store.update', $store), [
            'name' => $store->name,
            'logo' => UploadedFile::fake()->image('logo.jpg', 400, 400),
            'origin_latitude' => -7.05,
            'origin_longitude' => 110.44,
        ]);

        $response->assertRedirect(route('seller.store.show'));
        $store->refresh();
        $this->assertNotNull($store->logo_path);
        Storage::disk('public')->assertExists($store->logo_path);
    }

    public function test_store_logo_rejects_a_non_image(): void
    {
        Storage::fake('public');
        $seller = $this->seller();
        $store = Store::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAsSeller($seller)->put(route('seller.store.update', $store), [
            'name' => $store->name,
            'logo' => UploadedFile::fake()->create('brochure.pdf', 200, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('logo');
        $this->assertNull($store->refresh()->logo_path);
    }

    public function test_seller_cannot_update_another_sellers_store(): void
    {
        $owner = $this->seller();
        $store = Store::factory()->create(['user_id' => $owner->id]);

        $intruder = $this->seller();

        $response = $this->actingAsSeller($intruder)->put(route('seller.store.update', $store), [
            'name' => 'Diretas',
            'description' => 'x',
            'origin_latitude' => -7.05,
            'origin_longitude' => 110.44,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'name' => $store->name]);
    }
}
