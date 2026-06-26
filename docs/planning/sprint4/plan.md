# Sprint 4 Plan — Level 4 (Discounts + Seller Processing + Reports) — 15 pts

> Maps to TDD §13 "Day 4" and brief Level 4. Schema §7 (`promos`, `vouchers`, plus
> `orders.promo_id`/`orders.voucher_id` deferred from Sprint 3). Sources of authority, in order:
> `docs/KetentuanPanitia.pdf` (Level 4) → this `plan.md` → `docs/SEAPEDIA_TDD.md` (§ cited per task)
> → `CLAUDE.md`. **If the TDD/plan conflict with the brief, the brief wins**; flag it in `progress.md`.

## Goal

A buyer can enter a Promo code and/or a Voucher code at checkout; the system validates each
(expiry, remaining usage, min-spend), applies the §5.3 combination rule (`discount_total =
min(promo + voucher, subtotal)`, both computed off the original subtotal), and shows the discount
distinctly in the summary — with PPN 12% charged on the **discounted** subtotal (§5.2). On commit,
the voucher's `used_count` is incremented inside the same locked transaction that already charges
the wallet and reduces stock, and the order records which promo/voucher it used. Separately, a
seller can **process** an incoming order (`Sedang Dikemas → Menunggu Pengirim`) via the locked
transition table, writing a history row that both buyer and seller see on the order timeline.
Finally, a buyer sees a **spending report** and a seller sees an **income report**, each
reconciling with the underlying order data. All of this is mirrored under `/api/v1` with Swagger.

## Scope (challenge criteria covered)

**Level 4 — Discounts and Seller Order Processing (15 pts), brief pp. Level 4:**

- **Implement Voucher and Promo Discounts (6 pts)** — Voucher resource + Promo resource; admin
  endpoints to generate; endpoints to list + view; Voucher has expiry + remaining usage; Promo has
  expiry; checkout receives a discount code; validate during checkout; show the discount effect;
  keep subtotal/discount/delivery/PPN 12%/total visible; distinguish promo vs voucher; combination
  rule clear + consistent; discount-vs-PPN position documented.
- **Allow Sellers to Process Orders (4 pts)** — seller action to process an incoming order;
  `Sedang Dikemas → Menunggu Pengirim`; store the change in status history with timestamp; show the
  timeline on buyer + seller pages; only the owning seller may process; an order can't reach drivers
  before it's processed; main statuses stay visible.
- **Add Buyer and Seller Reports (5 pts)** — buyer spending/expense summary; seller income/revenue
  summary; buyer order history + detail + status history (exists from Sprint 3); seller incoming +
  processed orders + income summary; discount/fee/PPN/total visible in transaction details.

## Locked decisions referenced

- **§5.3 Discount rules (LOCKED)** — Promo: `type ∈ {percentage, fixed}`, `value`, optional
  `max_discount`, optional `min_spend`, `expiry_date`, no per-use limit. Voucher: same fields +
  `usage_limit` + `used_count`. **Combination: one Promo AND one Voucher may combine**;
  `discount_total = min(promo + voucher, subtotal)`; promo first then voucher, **both off the
  original subtotal** (not stacked sequentially). Expired → rejected; voucher `remaining = 0` →
  rejected; `min_spend` not met → rejected (clear message). Voucher `used_count` incremented
  **inside the checkout transaction with a row lock**, only on success; **not restored on overdue
  refund** (Sprint 5 honours this). Promo vs Voucher must be distinguishable in the summary.
- **§5.2 Checkout order (LOCKED)** — `taxable_base = subtotal − discount_total`; `tax =
  round(taxable_base × 0.12)`; delivery fee not taxed; `grand_total = taxable_base + tax + fee`.
  The checkout pipeline already computes exactly this with `discount_total = 0`; Sprint 4 only makes
  `discount_total` real — **no structural change to the money math** (as promised in Sprint 3).
- **§5.6 Order lifecycle (LOCKED)** — `Sedang Dikemas → Menunggu Pengirim` is "Seller processes,
  seller owns order". Set **only** via `OrderService::transition()` (already built in Sprint 3 with
  the full locked transition table); every change writes an `order_status_histories` row. An invalid
  transition (e.g. processing an already-processed order) throws → 422.
- **§6 Concurrency** — voucher `used_count` increment uses `lockForUpdate` on the voucher row inside
  the checkout transaction, re-checking remaining after the lock (mirrors the Sprint 3 oversell
  guard). Also folds Sprint 3 review finding #3 (lock products in a deterministic `product_id` order
  to remove the multi-product deadlock window) since T2 re-touches `CheckoutService::commit`.
- **Golden rules** — 1–2 (thin controllers → one Service call), 3 (locked txn on used_count/status),
  4 (Policies for ownership; `is_admin` for admin endpoints), 5 (integer IDR), 7 (status only via
  OrderService + history), 8 (Eloquent only), 9 (FormRequest per write), 13 (§2.5 structure;
  Enums), 16 (eager-load; no N+1 in reports).

## Decisions BEYOND / DIFFERENT FROM the TDD (must stay visible)

1. **➕ Admin discount endpoints are API-only (JSON + Sanctum + `is_admin` + Swagger), plus seed
   data** — the brief explicitly allows codes "prepared through seed data, API documentation, or a
   minimal Admin-only setup flow" and says the **full Admin management UI is not required until
   Level 6**. So Sprint 4 ships `/api/v1/admin/{promos,vouchers}` generate/list/view + demo seed
   codes; the Inertia admin management UI lands in Sprint 5 (Level 6). This is the first admin-gated
   surface, so it introduces an **`EnsureAdmin` middleware** (alias `is_admin`) — none exists yet.
2. **➕ Two explicit checkout fields: `promo_code` + `voucher_code`** (both nullable), not one
   classified input. The §5.3 combination is "one promo AND one voucher", so two labelled fields
   make the promo-vs-voucher distinction unambiguous in both the request and the summary — exactly
   what the brief asks ("clearly distinguished in the validation result or checkout summary").
3. **➕ Reports = summary cards + a breakdown table, no charting library.** The brief says reports
   "do not need to be overly complex"; adding a chart dependency would violate the "no new libs
   without a TDD-stated need" rule. Money stays tabular; figures reconcile with the order rows.
4. **➕ Spending excludes nothing yet (no refunds until Sprint 5).** Buyer spending = Σ
   `grand_total` of the buyer's orders; seller income = Σ `seller_income_amount` credited. When
   overdue refunds arrive in Sprint 5, `Dikembalikan` orders get netted out — noted as a carry-over.
5. **ℹ️ Visual identity stays "Ocean" + Instrument Sans; process unchanged** — implementation on
   **Claude Sonnet** after approval; every UI slice visually verified via **Playwright MCP** at
   360 / 768 / 1280 / **1920**, both locales (ID/EN), light + dark; **`npm run build` before every
   Playwright pass** (localhost serves the compiled build, not Vite HMR); commit locally only, no AI
   attribution.

## Instructions for the Sonnet implementer (READ FIRST)

You are implementing Sprint 4. Opus wrote and the owner approved this plan.

- **Authority order:** `docs/KetentuanPanitia.pdf` (Level 4) → this `plan.md` →
  `docs/SEAPEDIA_TDD.md` (§ cited) → `CLAUDE.md`. If the TDD/plan conflict with the brief, the brief
  wins; flag in `progress.md`.
- **One slice = one commit.** Implement T1→T6 in order; do not batch. Follow the `vertical-feature`
  skill order for every slice. Commit messages per the `commit-message` skill.
- **Use the implementation skills for their slices:** `money-and-checkout` (T2 — the §5.3 discount
  math + the locked `used_count` increment, where the race-correctness points live),
  `order-lifecycle` (T4 — `Sedang Dikemas → Menunggu Pengirim` via `OrderService::transition`, which
  already exists; this slice mostly wires the controller action + UI).
- **Honor the golden rules**, especially: rule 3 — voucher `used_count` and order status mutate only
  inside `DB::transaction` + `lockForUpdate`; rule 7 — status only via `OrderService::transition` +
  a history row; rule 4 — admin endpoints behind the new `is_admin` gate, seller process behind a
  Policy (owning seller only); rule 9 — every write has a FormRequest.
- **Fold Sprint 3's review finding #3 in T2:** when you re-touch `CheckoutService::commit` to add
  the voucher lock, also sort the per-product `lockForUpdate` calls by `product_id` so two concurrent
  multi-product checkouts can't deadlock by locking in opposite orders.
- **Carry-overs from Sprint 2/3** (`planning/sprint3/plan.md` → "Perlu dikerjakan next sprint"):
  when you touch `StoreService::publicShow` or any public payload, apply the column-scoping fix
  (don't leak `user_id`/timestamps); prefer API Resources if the API envelope starts repeating;
  breadcrumb i18n. Not blocking T1–T6 — fold in where you pass through that code.
- **Before every commit:** `./vendor/bin/sail pint` + `npm run lint`; run the `code-review` skill as
  a self-check; run the slice's Pest tests. A slice is not done until format + lint + tests pass.
- **Owner-run commands (ask, don't run yourself):** `migrate`/`seed`, `storage:link`, `npm run dev`,
  any `npx shadcn-vue@latest add ...` (**none needed this sprint — see below**), and any `.env`
  change. Everything else (test/pint/lint/format/build/vue-tsc/wayfinder `--with-form`) you run.
- **Visual QA via Playwright MCP** for every UI slice: **run `npm run build` first**, then
  360/768/1280/1920, both locales, light+dark, empty/loading/error states. Translate all new
  chrome/pages (ID + EN dictionaries) — new strings use `t(...)` from the start.
- **Any new TDD deviation you introduce MUST be added to the deviations list above**, with the reason.

## shadcn-vue components needed

**None new.** Level 4 reuses components already installed in `resources/js/components/ui/`:
`input` + `button` (discount code entry), `card` + `table` + `tabs` + `badge` (reports + summary
distinction), `dialog` (process-order confirm), `select`/`separator`/`alert`/`skeleton` (states).
Confirm each exists in `resources/js/components/ui/<name>/` before importing — if any is somehow
missing, run `npx shadcn-vue@latest add <name>` (owner-run) first; do **not** hand-roll a substitute.

## UI/UX direction (from `ui-ux-pro-max` + `frontend-design`)

- **Discount on checkout:** two labelled fields ("Kode Promo" / "Kode Voucher") with an Apply
  action; on apply, re-fetch the preview and show the **promo line** and **voucher line** as
  separate rows in the existing summary ledger (the §5.2 signature block), each with the code as a
  removable chip/badge; an invalid code shows an inline error below its field; PPN visibly recomputes
  on the discounted base. Subtotal / discount / delivery / PPN 12% / total all stay visible.
- **Seller process:** the seller order list/detail gets a primary "Proses Pesanan" button on
  `Sedang Dikemas` orders (confirm dialog → status advances → button disappears, timeline updates).
  The `StatusTimeline` component already renders history; the seller now gets an order-detail page
  reusing it. Order-status labels stay Indonesian in both locales (§15.3, the existing
  `orderStatus.ts` helper).
- **Reports:** a quiet summary view — a couple of focal **stat cards** (total spent / total income,
  order counts) over a tabular breakdown (per-status or per-order). Tabular numerals; success-green
  for income/credits, muted for spend; no chart library. Empty state when there are no orders yet.
- **Quality floor:** responsive 360/768/1280/1920, dark-mode parity, visible focus, reduced-motion,
  contrast ≥ 4.5:1; every list/detail has empty/loading/error states.

## Task breakdown (ordered vertical slices — one commit each)

- **T1 · Promo + Voucher resources + admin API + `orders` discount FKs**
  - Files: `promos` + `vouchers` migrations (§7) + `add_discount_fks_to_orders` migration
    (`promo_id`/`voucher_id` nullable FK, deferred from Sprint 3 T5); `DiscountType` enum
    (percentage, fixed); `Promo` + `Voucher` models (`$fillable`, casts `expiry_date` →
    immutable_datetime, `is_active` bool); `PromoFactory` + `VoucherFactory` (+ `expired()` /
    `usedUp()` states for tests/demo); `Order` model gains `promo()`/`voucher()` relations +
    fillable; **`EnsureAdmin` middleware** (alias `is_admin`) — first admin gate in the app;
    `StorePromoRequest` + `StoreVoucherRequest` (§10: code unique, type enum, value ≥ 0,
    expiry_date required, voucher usage_limit ≥ 1); `Api/Admin/PromoController` +
    `Api/Admin/VoucherController` (generate/index/show) behind `auth:sanctum` + `is_admin`; routes
    `/api/v1/admin/{promos,vouchers}`; OpenAPI annotations.
  - Business rules: §5.3 fields, golden rules 4 (admin isolation), 9, 13.
  - Acceptance: an admin token can POST a promo and a voucher and GET their list + detail; a
    non-admin token → 403; invalid type/value/expiry rejected (422).
  - Tests (Pest): admin creates a voucher → persisted with `used_count = 0`; non-admin → 403;
    `StoreVoucherRequest` rejects `usage_limit = 0` and a past-only required field as configured.
  - Commit: `feat(discount): add promo and voucher resources with admin endpoints`

- **T2 · DiscountService + checkout preview/commit integration (locked) — the core**
  - Files: `DiscountService` (`validatePromo(code, subtotal)`, `validateVoucher(code, subtotal)`,
    `resolve(promoCode, voucherCode, subtotal)` returning the §5.3 combined `discount_total` +
    which promo/voucher applied + per-code reject reasons); extend `CheckoutService::preview` to
    accept `?promoCode`/`?voucherCode` (compute discount, return distinct promo/voucher lines) and
    `CheckoutService::commit` to: lock the voucher row, re-check `remaining > 0`, increment
    `used_count`, set `order.promo_id`/`order.voucher_id`, and pass the real `discount_total` into
    the already-correct §5.2 math; `Preview/StoreCheckoutRequest` gain nullable `promo_code` +
    `voucher_code`; **fold review finding #3** (sort the product `lockForUpdate` loop by
    `product_id`).
  - Business rules: §5.2, §5.3, §6, golden rules 3, 5, 7, 10. Use the `money-and-checkout` skill.
  - Acceptance: a valid promo + voucher reduce `grand_total` per §5.3 with PPN on the discounted
    base; expired / used-up / min-spend-unmet codes rejected with a clear per-code message;
    `discount_total` never exceeds subtotal; on a successful commit the voucher's `used_count` goes
    up by exactly one and the order rows reference the codes used.
  - Tests (Pest, CRITICAL): expired promo/voucher rejected; voucher `remaining = 0` rejected;
    `min_spend` not met rejected; promo+voucher combine = `min(promo+voucher, subtotal)` off the
    original subtotal; `discount_total` capped at subtotal; **two concurrent checkouts on a
    1-remaining voucher → exactly one succeeds, the other rejected, `used_count` never exceeds
    `usage_limit`** (dual-connection lock proof, like the Sprint 3 oversell test).
  - Commit: `feat(discount): apply promo and voucher in a locked checkout`

- **T3 · Checkout UI: discount code entry + summary distinction**
  - Files: `buyer/checkout/Show.vue` — two code fields ("Kode Promo" / "Kode Voucher") + Apply that
    re-fetches the preview with the codes, inline per-field error, applied-code chips, and distinct
    promo/voucher rows in the summary ledger; `Api/CheckoutController` preview/store accept the codes
    (web `CheckoutController` already previews all delivery methods — extend it to thread the codes
    through); ID + EN i18n strings for the new chrome.
  - Business rules: brief "show discount effect" + "keep subtotal/discount/fee/PPN/total visible" +
    "distinguish promo vs voucher"; golden rule 17 (presentation only — all math server-side).
  - Acceptance: entering `HEMAT10` + `PROMO20K` shows both as separate summary lines with a reduced
    total before paying; an invalid/expired code shows an inline error and leaves the total
    unchanged; the PPN line visibly reflects the discounted base.
  - Tests (Pest): the checkout-preview endpoint returns distinct `promo`/`voucher` discount data for
    valid codes and a structured error for an invalid one (service-level race covered by T2).
  - Commit: `feat(checkout): add discount code entry to the checkout UI`

- **T4 · Seller process order (`Sedang Dikemas → Menunggu Pengirim`)**
  - Files: `Web/SellerOrderController` gains `show` (order detail) + `process`; `OrderPolicy` gains
    `process` (the seller owns `order.store.user_id`); routes `seller/orders/{order}` +
    `seller/orders/{order}/process`; `seller/orders/Show.vue` (reuses `StatusTimeline`) with a
    "Proses Pesanan" button (confirm dialog) on `Sedang Dikemas` orders; `Api/Seller/OrderController`
    `process`; the buyer order timeline already reflects new history rows; i18n.
  - Business rules: §5.6 (transition only via `OrderService::transition`, already built), only the
    owning seller, golden rules 7, 4.
  - Acceptance: the owning seller processes a `Sedang Dikemas` order → status becomes
    `Menunggu Pengirim`, a history row with a timestamp is written, and the timeline updates on both
    the seller and buyer order pages; a non-owning seller → 403; processing an order not in
    `Sedang Dikemas` → 422 (invalid transition).
  - Tests (Pest): owning seller processes ok + history written + status advanced; cross-seller
    process → 403; double-process (already `Menunggu Pengirim`) → 422.
  - Commit: `feat(order): add seller process action to advance order status`

- **T5 · Buyer spending report + Seller income report**
  - Files: `ReportService` (`buyerSpending(User)`: total spent, order count, breakdown by status;
    `sellerIncome(Store)`: total income, processed vs incoming counts, order count — all eager-loaded,
    no N+1); `Web/BuyerReportController` + `Web/SellerReportController`; `Api/BuyerReportController` +
    `Api/SellerReportController`; routes; `buyer/reports/Index.vue` + `seller/reports/Index.vue`
    (stat cards + breakdown table); sidebar nav links; i18n.
  - Business rules: brief reports; golden rule 16 (no N+1), scoped to own data.
  - Acceptance: the buyer sees total spent + order count + a per-status breakdown; the seller sees
    total income + processed/incoming counts; the figures reconcile with the seeded/created orders;
    a buyer can't see another buyer's report and a seller can't see another store's.
  - Tests (Pest): buyer report scoped to own orders (totals match); seller report scoped to own
    store; cross-user access blocked.
  - Commit: `feat(report): add buyer spending and seller income reports`

- **T6 · Seed demo discount codes + extend demo + README**
  - Files: `DiscountSeeder` — `HEMAT10` (voucher, e.g. 10% with a cap + usage limit), `PROMO20K`
    (promo, fixed 20 000 with a `min_spend`), and one **expired** code so the demo can show
    rejection; wire into `DatabaseSeeder`; optionally have `BuyerDemoSeeder`'s sample order apply a
    code so reports show a discounted order; README gains the **discount combination rule**, the
    **discount-before-PPN position** (§5.2), the **seller processing flow**, the **reports**, and a
    demo-codes table, plus a "Demo path (Sprint 4)" section.
  - Business rules: §12 seed; brief "codes may be prepared through seed data".
  - Acceptance: `migrate:fresh --seed` yields working `HEMAT10` + `PROMO20K` + an expired code, and
    the README documents the rules a grader checks.
  - Tests: none (covered by T1–T5 feature tests).
  - Commit: `feat(db): seed demo discount codes and document level 4 rules`

## Demo checklist (end of sprint)

1. Checkout with `HEMAT10` + `PROMO20K` stacked → summary shows the promo line and voucher line
   distinctly, a reduced total, and PPN 12% on the **discounted** subtotal → pay from wallet.
2. An **expired** code → rejected with a clear message; an exhausted voucher (`remaining = 0`) →
   rejected; a code under its `min_spend` → rejected.
3. Seller logs in → "Pesanan Masuk" → **processes** a `Sedang Dikemas` order → it moves to
   `Menunggu Pengirim`; the timeline updates on both the seller and the buyer order detail.
4. Buyer **spending report** and seller **income report** reconcile with the orders (discount, fee,
   PPN, total visible in the detail).
5. `/api/v1/admin/{promos,vouchers}` generate/list/view (admin token); `/api/v1/buyer/checkout`
   accepts the codes; reports mirrored — all in Swagger.
6. Responsive + dark-mode + both-locale pass at 360/768/1280/1920 via Playwright (build first), with
   order-status labels staying Indonesian in EN.

## Risks / open questions

- **Two codes vs one field:** going with explicit `promo_code` + `voucher_code` fields (Decision 2)
  so the promo/voucher distinction is unambiguous; the §5.3 combination supports exactly one of each.
- **Reports period/refund scope:** Sprint 4 has no refunds yet, so spending = Σ `grand_total` and
  income = Σ `seller_income_amount`; once overdue refunds exist (Sprint 5), `Dikembalikan` orders are
  netted out. Assumption, carried forward.
- **Admin gate:** no `is_admin` middleware exists yet — T1 introduces `EnsureAdmin` (alias
  `is_admin`); the full admin management UI is Sprint 5 per the brief. Assumption.
- **`discount_total` vs PPN position:** discount applied **before** PPN (§5.2) — already how the
  Sprint 3 pipeline computes it; documented in the README (T6).
- **Voucher refund semantics:** `used_count` is **not** restored on a later overdue refund (§5.3
  documented choice) — relevant to Sprint 5's `OverdueService`, flagged in the carry-over below.

## Perlu dikerjakan next sprint (carry-over)

To be picked up by Sprint 5+; Sprint 5's Sonnet instructions must reference this section.

- **Overdue refund must NOT restore voucher `used_count`** (§5.3) and must net `Dikembalikan` orders
  out of the buyer/seller reports — wire both into `OverdueService::sweep()` (Sprint 5).
- **Full Admin voucher/promo management UI** (Level 6) — Sprint 4 ships API + seed only; the Inertia
  admin CRUD screens land in Sprint 5.
- **Real iPaymu** — `IpaymuGateway`'s real v2 call + webhook → Sprint 6.
- **Carried from Sprint 2** (still open): public store-page payload column-scoping; API Resource
  shaping if the envelope repeats; breadcrumb i18n.
- **Carried from Sprint 3 review:** finding #3 (deterministic product-lock ordering) is folded into
  T2; if T2 doesn't end up re-touching the product-lock loop, do it as a small `fix(checkout)`.

## Out of scope (deferred to later sprints)

- Driver/delivery jobs, `seapedia:advance-day` + clock-advance UI, `OverdueService::sweep()`, the
  admin monitoring dashboard, and the **full voucher/promo management UI** → **Sprint 5**.
- Real iPaymu integration, security pass, Swagger polish, README finalize, deployment → **Sprint 6**.
- **Marketplace homepage + real product photos + product re-theme** (assets in `public/images/`) →
  **final sprint** (owner's decision; see Sprint 3 plan's "Visual polish bundle").
- Moving `planning/` into `docs/` → **final sprint** (owner's instruction).
