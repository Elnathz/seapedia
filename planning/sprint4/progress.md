# Sprint 4 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Promo + Voucher resources + admin API + orders discount FKs — `feat(discount): add promo and voucher resources with admin endpoints`
- [x] T2 · DiscountService + checkout preview/commit integration (locked) — `feat(discount): apply promo and voucher in a locked checkout`
- [x] T3 · Checkout UI: discount code entry + summary distinction — `feat(checkout): add discount code entry to the checkout UI`
- [x] T4 · Seller process order (Sedang Dikemas → Menunggu Pengirim) — `feat(order): add seller process action to advance order status`
- [x] T5 · Buyer spending report + Seller income report — `feat(report): add buyer spending and seller income reports`
- [x] T6 · Seed demo discount codes + README — `feat(db): seed demo discount codes and document level 4 rules`

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
- [x] Buyer report scoped to own orders; seller report scoped to own store; totals reconcile (T5)

## Visual QA (Playwright MCP) — build first; 360/768/1280/1920, both locales, light + dark

- [x] Checkout: promo + voucher fields, apply, distinct summary lines, inline error, PPN on discounted base —
  verified interactively at 360/1280, light+dark, ID: applying `PROMO20K`+`HEMAT10` together produces
  separate "Diskon Promo"/"Diskon Voucher" lines and a PPN recompute matching the §5.3/§5.2 math by hand
  (subtotal 108.000 → discount 30.800 → PPN 9.264 → total 91.464); expired codes (`EXPIREDPROMO`/`EXPIRED5K`)
  show inline per-field errors with the total left unchanged.
- [x] Seller order detail: process button + confirm dialog + timeline update (both sides) — full click-through
  at 1280 light: confirm dialog text, "Ya, Proses" advances Sedang Dikemas → Menunggu Pengirim, a new
  "Diproses oleh penjual" history row appears immediately, the button disappears post-process; verified the
  same order on the buyer side (EN locale, 1920, dark) shows the identical timeline with the status values
  staying Indonesian while all other chrome translates.
- [x] Buyer spending report: stat cards + breakdown table + empty state — non-empty (buyer1, totals reconcile
  with the seeded discounted order) and empty (multi1, 768) both checked.
- [x] Seller income report: stat cards + processed/incoming counts + empty state — verified incoming/processed
  counts flip correctly after processing an order (1/0 → 0/1), total income matches `seller_income_amount`.

**Bugs found and fixed during this pass** (owner flagged the first one directly while watching the session;
the second was found immediately after by measuring real pixel positions instead of trusting screenshots):

1. **Report breakdown table clipped its own Total column on mobile.** The 3-column `<Table>` (Status /
   Jumlah Pesanan / Total) needed 332px but only had 278px on a 360px screen; the shadcn `Table` wrapper's
   `overflow-auto` turned that into a horizontal scroll *inside the card*, so the amount — the one number the
   page exists to show — was off-screen by default with only a thin scrollbar as a hint. Replaced the table
   with a flex row list (status badge + count + amount, divided by `Separator`s) on both report pages, the
   same pattern already used for the §5.2 summary ledgers. Confirmed fixed: amount now sits a normal 24px
   from the card's right edge with no internal scroll.
2. **Order detail (buyer + seller) silently clipped the entire payment summary on mobile — more severe,
   pre-existing since Sprint 3, surfaced by the same investigation.** The `grid lg:grid-cols-3` → `flex
   flex-col lg:col-span-2` → `Card` chain around the 4-column items table had no `min-w-0` anywhere; the
   table's intrinsic content width (~432px) propagated up through the unconstrained flex/grid items, and
   because `AppContent` has `overflow-x-hidden` (the Sprint 3 padding fix), the excess was clipped invisibly
   instead of producing a visible scrollbar — at 360px this hid the grand total *and* the "Proses Pesanan"
   button entirely, with no indication anything was missing. Root-caused by measuring actual
   `getBoundingClientRect()` values rather than trusting `document.documentElement.scrollWidth` (which
   doesn't catch overflow an ancestor clips) or eyeballing screenshots. Fixed by adding `min-w-0` down the
   grid → flex → card chain in both `buyer/orders/Show.vue` and `seller/orders/Show.vue`, so the items table
   now scrolls within its own card (same contained-scroll pattern as the pre-existing wallet transaction
   table) instead of blowing out the whole layout. Also converted `seller/orders/Index.vue`'s incoming-orders
   table (same 5-column overflow at 360px) to the card-list pattern `buyer/orders/Index.vue` already uses.
   Commit `fix(ui): stop tables silently clipping content at mobile widths`.

**Note for the owner, not auto-fixed:** `buyer/wallet/Show.vue`'s transaction history table has the same
root overflow at 360px (confirmed: 506px content in a 311px container) but is wrapped in its own
`overflow-x-auto` + visible border — a deliberate, pre-existing scrollable-table affordance from Sprint 1,
not a layout-breaking clip like the two bugs above. Left as-is since it's out of Sprint 4's scope; flag if
it should be redesigned too.

## Post-review findings (Opus, after T1–T6 + visual QA shipped)

5-layer `code-review` pass over the whole sprint diff (`553e595..HEAD`). Security sweep clean (no `v-html`
on user content, no raw SQL / `DB::raw`, no `$guarded = []`); §5.3 combination + §6 voucher lock verified
correct (the `used_count` increment rolls back with the rest of the transaction even when a later promo
error throws, since the whole commit is one `DB::transaction`); lock order is globally consistent
(products → voucher → buyer wallet → seller wallet) with products sorted by `product_id`, so no deadlock;
no N+1 in the reports or order-detail read paths. Three findings:

1. **[LOW–MEDIUM — FIXED] Checkout summary discount lines didn't reconcile with the deduction.** When
   promo + voucher exceeded the subtotal, each line was capped individually and the *sum* was then min'd
   against the subtotal — so the two shown lines could add up to more than `discount_total` actually
   deducted (proved via tinker: subtotal 10 000, two fixed-8 000 codes → lines showed −8 000/−8 000 but only
   10 000 was taken). Display-only on the live checkout preview (the persisted order stores one combined
   `discount_total`, always consistent) and the demo path never triggers it (PROMO20K+HEMAT10 over a
   ≥100 000 subtotal never hits the cap), but it's the same "numbers don't add up" class as the Sprint 3
   preview/commit fix. `DiscountService::combine()` now applies the promo first and lets the voucher absorb
   the cap, so the lines always sum to exactly `discount_total`. Regression test
   `test_capped_discount_splits_so_the_displayed_lines_sum_to_the_total`. Commit `fix(discount)`.
2. **[MINOR — FIXED] `promos.value` / `vouchers.value` were `unsignedInteger`, not BIGINT.** For a `fixed`
   discount `value` is a money amount, so per §7 / golden rule 5 ("money is BIGINT UNSIGNED") it should match
   `min_spend` / `max_discount` (already BIGINT). Widened both to `unsignedBigInteger`; `migrate:fresh --seed`
   re-run, schema confirmed `bigint`. Commit `fix(db)`.
3. **[NOTE — not a bug, carry-over awareness] `ReportService` aggregates in PHP, not SQL.** Both
   `buyerSpending`/`sellerIncome` load all of the user's orders (two columns only) into a Collection and
   group in memory rather than a SQL `GROUP BY`. Fine at this scale and matches the brief's "reports needn't
   be overly complex", but if order volume grows it should move to a grouped query. Left as-is.

## Notes / deviations recorded

- The two layout bugs above are a reminder that `document.documentElement.scrollWidth >
  document.documentElement.clientWidth` is NOT a reliable "no horizontal overflow" check on this app, because
  `AppContent` has `overflow-x-hidden` — an ancestor can clip a descendant's overflow without the document
  ever reporting it. Future visual QA passes should measure `getBoundingClientRect()` on the actual cards
  (or just look closely at the rendered screenshot) rather than relying on that single document-level check.
