# Sprint 4 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Promo + Voucher resources + admin API + orders discount FKs — `feat(discount): add promo and voucher resources with admin endpoints`
- [x] T2 · DiscountService + checkout preview/commit integration (locked) — `feat(discount): apply promo and voucher in a locked checkout`
- [x] T3 · Checkout UI: discount code entry + summary distinction — `feat(checkout): add discount code entry to the checkout UI`
- [x] T4 · Seller process order (Sedang Dikemas → Menunggu Pengirim) — `feat(order): add seller process action to advance order status`
- [ ] T5 · Buyer spending report + Seller income report — `feat(report): add buyer spending and seller income reports`
- [ ] T6 · Seed demo discount codes + README — `feat(db): seed demo discount codes and document level 4 rules`

## Deferred to final sprint (owner's decision)

- Marketplace homepage (replace Welcome splash) + real product photos + product re-theme.
  Assets already in `public/images/`. Sprint 4 keeps the existing campus seed + placeholders.

## Tests

- [x] Admin creates voucher/promo → persisted; non-admin → 403; invalid type/value/usage rejected (T1)
- [x] Expired promo/voucher rejected; voucher remaining=0 rejected; min_spend not met rejected (T2)
- [x] Promo+voucher combine = min(promo+voucher, subtotal) off original subtotal; capped at subtotal (T2)
- [x] Concurrent checkouts on a 1-remaining voucher → one succeeds, one rejected, used_count never exceeds limit (T2)
- [x] Checkout preview returns distinct promo/voucher data for valid codes, structured error for invalid (T3)
- [x] Owning seller processes → status advanced + history; cross-seller → 403; double-process → 422 (T4)
- [ ] Buyer report scoped to own orders; seller report scoped to own store; totals reconcile (T5)

## Visual QA (Playwright MCP) — build first; 360/768/1280/1920, both locales, light + dark

- [ ] Checkout: promo + voucher fields, apply, distinct summary lines, inline error, PPN on discounted base
- [ ] Seller order detail: process button + confirm dialog + timeline update (both sides)
- [ ] Buyer spending report: stat cards + breakdown table + empty state
- [ ] Seller income report: stat cards + processed/incoming counts + empty state

## Notes / deviations recorded

(Filled in during implementation.)
