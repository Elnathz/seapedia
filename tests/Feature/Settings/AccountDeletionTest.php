<?php

namespace Tests\Feature\Settings;

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['name' => RoleName::Buyer->value])->id);

        return $user;
    }

    public function test_deletion_soft_deletes_and_anonymizes(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $fresh = User::withTrashed()->find($user->id);
        $this->assertSame('Pengguna Terhapus', $fresh->name);
        $this->assertStringContainsString('@seapedia.invalid', $fresh->email);
        $this->assertGuest();
    }

    public function test_deletion_frees_the_original_email(): void
    {
        $user = $this->buyer();
        $email = $user->email;

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password']);

        // No row — active or trashed — still holds the original email.
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    public function test_deletion_blocked_by_active_order(): void
    {
        $user = $this->buyer();
        Order::factory()->create([
            'buyer_id' => $user->id,
            'status' => OrderStatus::SedangDikemas,
        ]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHasErrors('password');

        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_deletion_blocked_by_wallet_balance(): void
    {
        $user = $this->buyer();
        $user->wallet->update(['balance' => 50_000]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHasErrors('password');

        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_deletion_rejects_wrong_password(): void
    {
        $user = $this->buyer();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->assertNull($user->fresh()->deleted_at);
    }
}
