<?php

namespace Tests\Feature\Wallet;

use App\Enums\WalletDirection;
use App\Enums\WalletTransactionType;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_increases_balance_and_writes_a_ledger_row(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 10_000]);
        $service = new WalletService;

        $transaction = $service->credit($wallet, 5_000, WalletTransactionType::Topup, 'topup', 1);

        $this->assertSame(15_000, $wallet->refresh()->balance);
        $this->assertSame(WalletDirection::Credit, $transaction->direction);
        $this->assertSame(15_000, $transaction->balance_after);
        $this->assertSame('topup', $transaction->reference_type);
        $this->assertSame(1, $transaction->reference_id);
    }

    public function test_debit_decreases_balance_and_writes_a_ledger_row(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 10_000]);
        $service = new WalletService;

        $transaction = $service->debit($wallet, 4_000, WalletTransactionType::Payment, 'order', 1);

        $this->assertSame(6_000, $wallet->refresh()->balance);
        $this->assertSame(WalletDirection::Debit, $transaction->direction);
        $this->assertSame(6_000, $transaction->balance_after);
    }

    public function test_debit_beyond_balance_is_rejected_without_side_effects(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 1_000]);
        $service = new WalletService;

        try {
            $service->debit($wallet, 2_000, WalletTransactionType::Payment);
            $this->fail('Expected a ValidationException for insufficient balance.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('balance', $e->errors());
        }

        $this->assertSame(1_000, $wallet->refresh()->balance);
        $this->assertSame(0, $wallet->transactions()->count());
    }

    public function test_sequential_credits_and_debits_keep_balance_after_consistent(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 0]);
        $service = new WalletService;

        $service->credit($wallet, 50_000, WalletTransactionType::Topup);
        $service->debit($wallet->refresh(), 20_000, WalletTransactionType::Payment);
        $service->credit($wallet->refresh(), 10_000, WalletTransactionType::Refund);

        $this->assertSame(40_000, $wallet->refresh()->balance);

        $balances = $wallet->transactions()->orderBy('id')->pluck('balance_after')->all();
        $this->assertSame([50_000, 30_000, 40_000], $balances);
    }
}
