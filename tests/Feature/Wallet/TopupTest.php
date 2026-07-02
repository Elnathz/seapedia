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
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );

        return $user;
    }

    private function actingAsBuyer(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value]);
    }

    public function test_creating_a_topup_redirects_to_the_processing_page_while_still_pending(): void
    {
        $buyer = $this->buyer();

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.wallet.topup'), [
            'amount' => 50_000,
        ]);

        $topup = Topup::query()->where('wallet_id', $buyer->wallet->id)->first();
        $this->assertNotNull($topup);
        $response->assertRedirect(route('buyer.wallet.topup.show', $topup));

        $this->assertSame(TopupStatus::Pending, $topup->status);
        $this->assertSame(0, $buyer->wallet->refresh()->balance);
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

    public function test_amount_above_the_maximum_is_rejected(): void
    {
        $buyer = $this->buyer();

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.wallet.topup'), [
            'amount' => 100_000_001,
        ]);

        $response->assertInvalid(['amount']);
        $this->assertSame(0, $buyer->wallet->refresh()->balance);
    }

    public function test_amounts_at_the_min_and_max_bounds_are_accepted(): void
    {
        foreach ([5_000, 100_000_000] as $amount) {
            $buyer = $this->buyer();

            $response = $this->actingAsBuyer($buyer)->post(route('buyer.wallet.topup'), [
                'amount' => $amount,
            ]);

            $response->assertValid();
            $this->assertNotNull(
                Topup::query()
                    ->where('wallet_id', $buyer->wallet->id)
                    ->where('amount', $amount)
                    ->first(),
            );
        }
    }

    public function test_the_processing_page_resolves_to_paid_once_the_delay_elapses_and_credits_exactly_once(): void
    {
        config(['payment.topup.processing_seconds' => 0]);

        $buyer = $this->buyer();
        $wallet = $buyer->wallet;
        $topup = Topup::factory()->create(['wallet_id' => $wallet->id, 'amount' => 75_000]);

        $response = $this->actingAsBuyer($buyer)->get(route('buyer.wallet.topup.show', $topup));

        $response->assertOk();
        $this->assertSame(75_000, $wallet->refresh()->balance);
        $this->assertSame(TopupStatus::Paid, $topup->refresh()->status);
        $this->assertNotNull($topup->processed_at);
        $this->assertSame(1, $wallet->transactions()->count());

        // Refreshing the processing page again (the frontend's poll) must
        // never double-credit an already-resolved top-up.
        $this->actingAsBuyer($buyer)->get(route('buyer.wallet.topup.show', $topup));

        $this->assertSame(75_000, $wallet->refresh()->balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }

    public function test_the_processing_page_stays_pending_before_the_delay_elapses(): void
    {
        config(['payment.topup.processing_seconds' => 30]);

        $buyer = $this->buyer();
        $topup = Topup::factory()->create(['wallet_id' => $buyer->wallet->id, 'amount' => 75_000]);

        $this->actingAsBuyer($buyer)->get(route('buyer.wallet.topup.show', $topup))->assertOk();

        $this->assertSame(TopupStatus::Pending, $topup->refresh()->status);
        $this->assertSame(0, $buyer->wallet->refresh()->balance);
    }

    public function test_a_buyer_cannot_view_another_buyers_topup(): void
    {
        $owner = $this->buyer();
        $topup = Topup::factory()->create(['wallet_id' => $owner->wallet->id]);
        $other = $this->buyer();

        $response = $this->actingAsBuyer($other)->get(route('buyer.wallet.topup.show', $topup));

        $response->assertForbidden();
    }

    public function test_replaying_check_status_on_an_already_processed_topup_does_not_double_credit(): void
    {
        config(['payment.topup.processing_seconds' => 0]);

        $buyer = $this->buyer();
        $wallet = $buyer->wallet;
        $topup = Topup::factory()->create(['wallet_id' => $wallet->id, 'amount' => 40_000]);

        $gateway = app(FakeGateway::class);
        $gateway->checkStatus($topup);
        $gateway->checkStatus($topup->refresh());

        $this->assertSame(40_000, $wallet->refresh()->balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }
}
