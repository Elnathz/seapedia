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
  delivery_fee = {instant:20000, next_day:10000, regular:5000}
  grand_total = taxable_base + tax_amount + delivery_fee
- Discount applied BEFORE tax. Delivery fee NOT taxed.
- Checkout commit (single DB::transaction):
  1. lockForUpdate each product; assert stock ≥ qty (else 422, no negative stock).
  2. lockForUpdate voucher (if any); assert remaining usage; increment used_count.
  3. lockForUpdate buyer wallet; assert balance ≥ grand_total (else 422); debit; write wallet_transaction(payment).
  4. decrement stock.
  5. create order (Sedang Dikemas) + order_items + status history. Record seller_income_amount.
- Always expose subtotal, discount, delivery_fee, tax_amount, grand_total in the preview response and order detail.
