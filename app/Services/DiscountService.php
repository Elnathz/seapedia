<?php

namespace App\Services;

use App\Models\Promo;
use App\Models\Voucher;

class DiscountService
{
    public function __construct(private readonly ClockService $clock) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPromo(array $data): Promo
    {
        return Promo::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createVoucher(array $data): Voucher
    {
        return Voucher::create([...$data, 'used_count' => 0]);
    }

    public function togglePromoActive(Promo $promo): Promo
    {
        $promo->update(['is_active' => ! $promo->is_active]);

        return $promo;
    }

    public function toggleVoucherActive(Voucher $voucher): Voucher
    {
        $voucher->update(['is_active' => ! $voucher->is_active]);

        return $voucher;
    }

    /**
     * The admin-facing lifecycle label for a code — distinct from the
     * checkout-time eligibility errors in evaluatePromo/evaluateVoucher,
     * but checked against the same `is_active` / `expiry_date` / usage
     * rules so the dashboard never disagrees with what checkout would do.
     */
    public function statusFor(Promo|Voucher $discount): string
    {
        if (! $discount->is_active) {
            return 'inactive';
        }

        if ($discount->expiry_date->isBefore($this->clock->now())) {
            return 'expired';
        }

        if ($discount instanceof Voucher && $discount->remainingUsage() <= 0) {
            return 'used_up';
        }

        return 'active';
    }

    /**
     * Read-only promo lookup against a subtotal — used by both the preview
     * (display only) and commit (promo has no used_count, so no lock needed).
     *
     * @return array{promo: ?Promo, amount: int, error: ?string}
     */
    public function resolvePromo(?string $code, int $subtotal): array
    {
        if ($code === null || $code === '') {
            return ['promo' => null, 'amount' => 0, 'error' => null];
        }

        $promo = Promo::query()->where('code', $code)->first();

        if (! $promo) {
            return ['promo' => null, 'amount' => 0, 'error' => __('Invalid promo code.')];
        }

        [$amount, $error] = $this->evaluatePromo($promo, $subtotal);

        return ['promo' => $error ? null : $promo, 'amount' => $amount, 'error' => $error];
    }

    /**
     * Read-only voucher lookup (no lock) — used by the preview only, where
     * nothing is mutated. Commit uses `lockAndApplyVoucher()` instead.
     *
     * @return array{voucher: ?Voucher, amount: int, error: ?string}
     */
    public function resolveVoucher(?string $code, int $subtotal): array
    {
        if ($code === null || $code === '') {
            return ['voucher' => null, 'amount' => 0, 'error' => null];
        }

        $voucher = Voucher::query()->where('code', $code)->first();

        if (! $voucher) {
            return ['voucher' => null, 'amount' => 0, 'error' => __('Invalid voucher code.')];
        }

        [$amount, $error] = $this->evaluateVoucher($voucher, $subtotal);

        return ['voucher' => $error ? null : $voucher, 'amount' => $amount, 'error' => $error];
    }

    /**
     * The §5.3 combination for the checkout preview: both codes evaluated
     * independently off the original subtotal, capped at the subtotal.
     *
     * @return array{discount_total: int, promo: ?Promo, promo_amount: int, promo_error: ?string, voucher: ?Voucher, voucher_amount: int, voucher_error: ?string}
     */
    public function resolve(?string $promoCode, ?string $voucherCode, int $subtotal): array
    {
        return $this->combine(
            $this->resolvePromo($promoCode, $subtotal),
            $this->resolveVoucher($voucherCode, $subtotal),
            $subtotal,
        );
    }

    /**
     * The commit-time equivalent of `resolve()` (§6): the voucher row is
     * locked and its `used_count` incremented atomically with the
     * eligibility re-check, so a concurrent checkout can never push it past
     * `usage_limit`. Caller must be inside `CheckoutService::commit()`'s
     * transaction. Returns the same shape as `resolve()`.
     *
     * @return array{discount_total: int, promo: ?Promo, promo_amount: int, promo_error: ?string, voucher: ?Voucher, voucher_amount: int, voucher_error: ?string}
     */
    public function applyAtCommit(?string $promoCode, ?string $voucherCode, int $subtotal): array
    {
        return $this->combine(
            $this->resolvePromo($promoCode, $subtotal),
            $this->lockAndApplyVoucher($voucherCode, $subtotal),
            $subtotal,
        );
    }

    /**
     * @return array{voucher: ?Voucher, amount: int, error: ?string}
     */
    private function lockAndApplyVoucher(?string $code, int $subtotal): array
    {
        if ($code === null || $code === '') {
            return ['voucher' => null, 'amount' => 0, 'error' => null];
        }

        $voucher = Voucher::query()->where('code', $code)->lockForUpdate()->first();

        if (! $voucher) {
            return ['voucher' => null, 'amount' => 0, 'error' => __('Invalid voucher code.')];
        }

        [$amount, $error] = $this->evaluateVoucher($voucher, $subtotal);

        if ($error) {
            return ['voucher' => null, 'amount' => 0, 'error' => $error];
        }

        $voucher->increment('used_count');

        return ['voucher' => $voucher->refresh(), 'amount' => $amount, 'error' => null];
    }

    /**
     * @return array{0: int, 1: ?string}
     */
    private function evaluatePromo(Promo $promo, int $subtotal): array
    {
        if (! $promo->is_active) {
            return [0, __('Invalid promo code.')];
        }

        if ($promo->expiry_date->isBefore($this->clock->now())) {
            return [0, __('This promo code has expired.')];
        }

        if ($promo->min_spend !== null && $subtotal < $promo->min_spend) {
            return [0, __('Minimum spend of :amount required for this code.', ['amount' => number_format($promo->min_spend, 0, ',', '.')])];
        }

        return [$promo->type->amountFor($promo->value, $subtotal, $promo->max_discount), null];
    }

    /**
     * @return array{0: int, 1: ?string}
     */
    private function evaluateVoucher(Voucher $voucher, int $subtotal): array
    {
        if (! $voucher->is_active) {
            return [0, __('Invalid voucher code.')];
        }

        if ($voucher->expiry_date->isBefore($this->clock->now())) {
            return [0, __('This voucher code has expired.')];
        }

        if ($voucher->remainingUsage() <= 0) {
            return [0, __('This voucher code has been fully redeemed.')];
        }

        if ($voucher->min_spend !== null && $subtotal < $voucher->min_spend) {
            return [0, __('Minimum spend of :amount required for this code.', ['amount' => number_format($voucher->min_spend, 0, ',', '.')])];
        }

        return [$voucher->type->amountFor($voucher->value, $subtotal, $voucher->max_discount), null];
    }

    /**
     * @param  array{promo: ?Promo, amount: int, error: ?string}  $promoResult
     * @param  array{voucher: ?Voucher, amount: int, error: ?string}  $voucherResult
     * @return array{discount_total: int, promo: ?Promo, promo_amount: int, promo_error: ?string, voucher: ?Voucher, voucher_amount: int, voucher_error: ?string}
     */
    private function combine(array $promoResult, array $voucherResult, int $subtotal): array
    {
        // §5.3: promo is applied first, then the voucher, and the combined
        // discount is capped at the subtotal. Clamp the per-line amounts so
        // the displayed promo/voucher lines always sum to exactly
        // discount_total — the voucher absorbs the cap when promo + voucher
        // would exceed the subtotal, instead of two lines that overstate the
        // real deduction.
        $promoAmount = min($promoResult['amount'], $subtotal);
        $voucherAmount = min($voucherResult['amount'], $subtotal - $promoAmount);

        return [
            'discount_total' => $promoAmount + $voucherAmount,
            'promo' => $promoResult['promo'],
            'promo_amount' => $promoAmount,
            'promo_error' => $promoResult['error'],
            'voucher' => $voucherResult['voucher'],
            'voucher_amount' => $voucherAmount,
            'voucher_error' => $voucherResult['error'],
        ];
    }
}
