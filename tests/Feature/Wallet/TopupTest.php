<?php

namespace Tests\Feature\Wallet;

use App\Enums\RoleName;
use App\Enums\TopupStatus;
use App\Models\Role;
use App\Models\Topup;
use App\Models\User;
use App\Services\Payment\FakeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopupTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::factory()->create(['name' => RoleName::Buyer->value])->id,
        );

        return $user;
    }

    private function actingAsBuyer(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value]);
    }

    public function test_fake_topup_credits_wallet_and_writes_a_ledger_entry(): void
    {
        $buyer = $this->buyer();

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.wallet.topup'), [
            'amount' => 50_000,
        ]);

        $response->assertRedirect(route('buyer.wallet.show'));

        $wallet = $buyer->wallet->refresh();
        $this->assertSame(50_000, $wallet->balance);

        $topup = Topup::query()->where('wallet_id', $wallet->id)->first();
        $this->assertNotNull($topup);
        $this->assertSame(TopupStatus::Paid, $topup->status);
        $this->assertNotNull($topup->processed_at);

        $this->assertSame(1, $wallet->transactions()->count());
        $this->assertSame(50_000, $wallet->transactions()->first()->balance_after);
    }

    public function test_amount_below_minimum_is_rejected(): void
    {
        $buyer = $this->buyer();

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.wallet.topup'), [
            'amount' => 100,
        ]);

        $response->assertInvalid(['amount']);
        $this->assertSame(0, $buyer->wallet->refresh()->balance);
    }

    public function test_replaying_an_already_processed_topup_does_not_double_credit(): void
    {
        $buyer = $this->buyer();
        $wallet = $buyer->wallet;

        $topup = Topup::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 75_000,
        ]);

        $gateway = app(FakeGateway::class);
        $gateway->createTopup($topup);
        $gateway->createTopup($topup->refresh());

        $this->assertSame(75_000, $wallet->refresh()->balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }
}
