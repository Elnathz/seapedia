<?php

namespace Tests\Feature\Address;

use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressManagementTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );

        return $user;
    }

    private function actingAsBuyer(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value]);
    }

    public function test_first_address_is_automatically_the_default(): void
    {
        $buyer = $this->buyer();

        $this->actingAsBuyer($buyer)->post(route('buyer.addresses.store'), [
            'recipient_name' => 'Budi',
            'phone' => '081234567890',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
            'village' => 'Senayan',
            'postal_code' => '12345',
            'full_address' => 'Jl. Merdeka No. 1',
            'latitude' => -6.225,
            'longitude' => 106.8,
        ]);

        $address = Address::query()->where('user_id', $buyer->id)->first();
        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
    }

    public function test_creating_an_address_returns_to_the_referring_page(): void
    {
        $buyer = $this->buyer();

        // Adding an address from the inline checkout modal must land the buyer
        // back on checkout, not yank them to the addresses page.
        $this->actingAsBuyer($buyer)
            ->from(route('buyer.checkout.show'))
            ->post(route('buyer.addresses.store'), [
                'recipient_name' => 'Budi',
                'phone' => '081234567890',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'village' => 'Senayan',
                'postal_code' => '12345',
                'full_address' => 'Jl. Merdeka No. 1',
                'latitude' => -6.225,
                'longitude' => 106.8,
            ])
            ->assertRedirect(route('buyer.checkout.show'));
    }

    public function test_setting_a_new_default_unsets_the_previous_one(): void
    {
        $buyer = $this->buyer();

        $first = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);
        $second = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => false]);

        $this->actingAsBuyer($buyer)->patch(route('buyer.addresses.setDefault', $second));

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);
    }

    public function test_cross_user_address_update_is_forbidden(): void
    {
        $owner = $this->buyer();
        $other = $this->buyer();
        $address = Address::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsBuyer($other)->put(route('buyer.addresses.update', $address), [
            'recipient_name' => 'Hacker',
            'phone' => '080000000000',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Pusat',
            'district' => 'Gambir',
            'village' => 'Cideng',
            'postal_code' => '10000',
            'full_address' => 'Somewhere else',
            'latitude' => -6.17,
            'longitude' => 106.83,
        ]);

        $response->assertForbidden();
        $this->assertNotSame('Hacker', $address->refresh()->recipient_name);
    }

    public function test_cross_user_address_delete_is_forbidden(): void
    {
        $owner = $this->buyer();
        $other = $this->buyer();
        $address = Address::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsBuyer($other)->delete(route('buyer.addresses.destroy', $address));

        $response->assertForbidden();
        $this->assertNotNull($address->refresh());
    }

    public function test_deleting_the_default_address_promotes_another_one(): void
    {
        $buyer = $this->buyer();

        $first = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);
        $second = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => false]);

        $this->actingAsBuyer($buyer)->delete(route('buyer.addresses.destroy', $first));

        $this->assertTrue($second->refresh()->is_default);
    }
}
