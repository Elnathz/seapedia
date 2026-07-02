---
name: money-and-checkout
description: Use when implementing or editing checkout preview, checkout commit, discount application, tax, delivery fee, or wallet charge. Encodes the locked money math and transaction safety.
---
# Money & checkout rules (LOCKED — TDD §5.1–5.4, §6)

- Money is integer IDR. No floats.
- Order of math:
  subtotal = Σ(price_snapshot × qty)
  discount_total = min(promo + voucher, subtotal)   # both off original subtotal
  taxable_base = subtotal − discount_total
  tax_amount = round(taxable_base × 0.12)            # PPN base = discounted subtotal
  base_fee = {instant:20000, next_day:10000, regular:5000}
  delivery_fee = base_fee + region_surcharge         # DeliveryFeeService, see TDD §5.4a
  grand_total = taxable_base + tax_amount + delivery_fee
- Discount applied BEFORE tax. Delivery fee (base + surcharge) NOT taxed.
- region_surcharge = DeliveryFeeService->fee(method, store.origin, buyer.address) − base_fee.
  Tier ladder (region strings, no coords): same_village 0 / same_district 2000 / same_city 5000 /
  same_province 10000 / interregional 20000. 0 when the store has no recorded origin.
  Compute it identically in preview() and commit() (pass the same address) so the quote == the charge.
- Checkout commit (single DB::transaction):
  1. lockForUpdate each product; assert stock ≥ qty (else 422, no negative stock).
  2. lockForUpdate voucher (if any); assert remaining usage; increment used_count.
  3. lockForUpdate buyer wallet; assert balance ≥ grand_total (else 422); debit; write wallet_transaction(payment).
  4. decrement stock.
  5. create order (Sedang Dikemas) + order_items + status history. Record seller_income_amount.
- Always expose subtotal, discount, delivery_fee, tax_amount, grand_total in the preview response and order detail.
