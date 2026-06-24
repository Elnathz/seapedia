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
            'full_address' => 'Jl. Merdeka No. 1',
        ]);

        $address = Address::query()->where('user_id', $buyer->id)->first();
        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
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
            'full_address' => 'Somewhere else',
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
