<?php

namespace App\Services;

use App\Enums\WalletDirection;
use App\Enums\WalletTransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Credit a wallet and append a ledger row, all under a row lock so
     * concurrent movements on the same wallet never race on `balance`.
     */
    public function credit(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $type, $referenceType, $referenceId, $description) {
            $locked = Wallet::query()->lockForUpdate()->findOrFail($wallet->id);

            $locked->update(['balance' => $locked->balance + $amount]);

            return $locked->transactions()->create([
                'type' => $type,
                'direction' => WalletDirection::Credit,
                'amount' => $amount,
                'balance_after' => $locked->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Debit a wallet and append a ledger row under a row lock; rejects
     * (422) rather than allowing the balance to go negative.
     */
    public function debit(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $type, $referenceType, $referenceId, $description) {
            $locked = Wallet::query()->lockForUpdate()->findOrFail($wallet->id);

            if ($locked->balance < $amount) {
                throw ValidationException::withMessages([
                    'balance' => [__('Insufficient wallet balance.')],
                ]);
            }

            $locked->update(['balance' => $locked->balance - $amount]);

            return $locked->transactions()->create([
                'type' => $type,
                'direction' => WalletDirection::Debit,
                'amount' => $amount,
                'balance_after' => $locked->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }
}
