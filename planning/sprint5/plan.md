# Sprint 5 Plan — Level 5 (Driver delivery) + Level 6 (Admin + Overdue + Time-sim) — the heavy stretch

> Maps to TDD §13 "Day 5" (one plan covering two levels) and brief Levels 5 + 6. Schema §7
> (`deliveries`; `settings.simulated_now` already seeded). Sources of authority, in order:
> `docs/KetentuanPanitia.pdf` (Levels 5 + 6) → this `plan.md` → `docs/SEAPEDIA_TDD.md` (§ cited per
> task) → `CLAUDE.md`. **If the TDD/plan conflict with the brief, the brief wins**; flag it in
> `progress.md`.

## Goal

The order chain runs end-to-end across all four roles. After a seller **processes** an order
(Sprint 4: `Sedang Dikemas → Menunggu Pengirim`), a `deliveries` row is auto-created as `available`.
A **driver** sees available jobs, **takes** one (locked, `available → taken`, order
`Menunggu Pengirim → Sedang Dikirim`, `driver_id` claimed under a row lock so two drivers can't grab
the same job), and **completes** it (`taken → completed`, order `Sedang Dikirim → Pesanan Selesai`).
On completion, in one locked transaction, the **seller** is finally credited the order's
`seller_income_amount` (escrow release) **and** the **driver** is credited **80% of `delivery_fee`**
per §5.5 (stored as `earning_amount`). Until then the buyer's payment sits held by the platform — a
driver may hold only **one active job** at a time. Buyer and seller see the driver/tracking on the
order timeline. Separately, an **admin** can **advance the simulated day**
(`POST /admin/clock/advance`, mirrored by `php artisan seapedia:advance-day`), which immediately runs
`OverdueService::sweep()` — an **idempotent** pass that refunds any order past its `sla_due_at` and
not yet final (§5.9): buyer credited `grand_total`, stock restored, status → `Dikembalikan`,
`refunded_at` stamped so a re-run is a no-op. **No seller reversal is needed** — under escrow an
overdue order was never completed, so the seller was never paid (only completed orders pay out).
Finally, an admin
sees a **monitoring dashboard** (users/stores/products/orders/discounts/deliveries/overdue) and the
**voucher/promo management UI** (the Inertia CRUD deferred from Sprint 4). All new flows mirrored
under `/api/v1` with Swagger.

## Scope (challenge criteria covered)

**Level 5 — Driver Delivery Workflow:**

- **Delivery job lifecycle** — a processed order becomes an available delivery job; a driver lists
  available jobs, takes one (only if unclaimed), and completes it; order status advances along the
  locked machine (`Menunggu Pengirim → Sedang Dikirim → Pesanan Selesai`); every change writes a
  history row visible on the timeline.
- **Driver earning** — on completion the driver earns **80%** of the order's `delivery_fee`
  (platform keeps 20%), credited to the driver wallet and stored as `earning_amount`.
- **Concurrency** — two drivers taking the same job → exactly one wins; the other gets 409 (no double
  assignment).
- **Driver dashboard** — active job + history + total earnings; buyer/seller tracking of the assigned
  driver + delivery status on the order pages.

**Level 6 — Admin Monitoring + Overdue Auto-Refund:**

- **Time simulation** — `settings.simulated_now` + `ClockService` (exist) driven by an admin
  "advance day" trigger AND `php artisan seapedia:advance-day` (covers the brief's "scheduler, cron,
  worker, command, or manual Admin trigger").
- **Overdue auto-refund (idempotent)** — orders past `sla_due_at` and not final are auto-returned per
  §5.9 (escrow-adapted, Decision 3): buyer refund, stock restore, status `Dikembalikan`, history,
  with a `refunded_at` sentinel guarding against double effects; no seller reversal (the seller was
  never paid for an undelivered order). Verifiable in wallet history + status.
- **Admin monitoring dashboard** — counts/links across users, stores, products, orders, discounts,
  deliveries, and overdue orders.
- **Voucher/Promo management UI** — admin Inertia CRUD over the promo/voucher resources Sprint 4
  shipped as API + seed only.

## Locked decisions referenced

- **§5.4 Delivery methods & fees (LOCKED)** — Instant 20 000 / 1 tick, Next Day 10 000 / 2 ticks,
  Regular 5 000 / 4 ticks. Already encoded in `DeliveryMethod::fee()` + `slaTicks()`; `sla_due_at` is
  precomputed at checkout (`created_sim_at + slaTicks`). Sprint 5 only **reads** these.
- **§5.5 Driver earning (LOCKED)** — `earning_amount = round(delivery_fee × 0.80)`; platform keeps
  20%; credited only on `Pesanan Selesai`; stored on the delivery row. Documented in README.
- **§5.6 Order lifecycle (LOCKED)** — only valid moves: `Menunggu Pengirim → Sedang Dikirim` (driver
  takes, job unclaimed), `Sedang Dikirim → Pesanan Selesai` (driver confirms, driver owns job),
  `{Sedang Dikemas | Menunggu Pengirim | Sedang Dikirim} → Dikembalikan` (overdue handler, paid + not
  already refunded). Status mutates **only** via `OrderService::transition()` (built Sprint 3, full
  table present), which writes an `order_status_histories` row every time. Invalid move → 422.
- **§5.7 Time simulation (LOCKED)** — one `settings.simulated_now` row; `ClockService::now()` returns
  it or real now; admin `POST /admin/clock/advance` advances **1 tick (= 1 day)** and **immediately
  runs `OverdueService::sweep()`**; also `php artisan seapedia:advance-day`. Sweep is idempotent:
  processes only orders with `sla_due_at < simulated_now` AND status ∈ {not-final} AND
  `refunded_at IS NULL`.
- **§5.9 Overdue refund effects (LOCKED, idempotent) — adapted to escrow (Decision 3).** In ONE
  transaction: lock order; if `refunded_at IS NOT NULL` abort; credit buyer `grand_total`
  (`wallet_transactions{refund}`); restore stock per `order_item` (`product.stock += quantity`); set
  `refunded_at = ClockService::now()`, status → `Dikembalikan`, write history. **The §5.9 "reverse
  seller income" step is dropped** — under escrow (Decision 3) an overdue order was never completed,
  so the seller was never paid; there is nothing to reverse. Same end-state §5.9 intends (buyer made
  whole, seller keeps nothing for an undelivered order, stock back), reached without a seller debit
  that could strand a negative balance. The `refunded_at` sentinel + row lock guard against double
  refund / double stock restore.
- **§6 Concurrency** — driver take uses `DB::transaction` + `lockForUpdate` on the delivery row,
  assigns only if `driver_id IS NULL`, else 409. Overdue double-refund guarded by `refunded_at` +
  row lock. All wallet mutations go through `WalletService` (`credit`/`debit`, already row-locked).
- **Golden rules** — 1–2 (thin controllers → one Service call), 3 (locked txn on stock / status /
  balance / delivery assignment), 4 (Policies for ownership: `DeliveryPolicy`; `is_admin` gate for
  admin surfaces; never trust role from body), 5 (integer IDR), 6 (time ONLY from
  `ClockService::now()` — the sweep and earning timestamps must use it, never `now()`), 7 (status only
  via `OrderService::transition` + history), 8 (Eloquent only), 9 (FormRequest per write), 13 (§2.5
  structure; Enums — new `DeliveryStatus`), 15 (idempotency: take + complete + sweep all idempotent /
  guarded), 16 (eager-load; no N+1 in driver/admin lists), 17 (Vue presentation only).

## Decisions BEYOND / DIFFERENT FROM the TDD (must stay visible)

1. **➕ `earning_amount = intdiv(delivery_fee × 80, 100)` (floor), not `round`.** §5.5 says "80% of
   fee"; the fees (20 000 / 10 000 / 5 000) are all divisible by 5 so 80% is exact (16 000 / 8 000 /
   4 000) — floor vs round never differ here, but we lock **floor** (`intdiv`) as the rule so the
   platform never over-pays a fractional rupiah, consistent with golden rule 5 (integer IDR). Stated
   in README.
2. **➕ Delivery row auto-created at seller-process time (status `available`), not at checkout.** §13
   says "auto-create delivery row when seller processes order". So the Sprint 4 seller-process action
   (T-prev) is extended in T1 to also insert the `deliveries` row inside the **same transaction** as
   the status transition — a job only exists once an order is actually ready for a driver, which also
   satisfies the brief's "an order can't reach drivers before it's processed".
3. **➕➕ ESCROW: seller income is credited on `Pesanan Selesai`, NOT at checkout (owner decision).**
   This is the most significant deviation and it resolves an internal TDD inconsistency: §5.2 says
   seller income is credited "when completed/paid", but the Sprint 3 `CheckoutService::commit` credits
   the seller immediately at checkout — which is what forced §5.9's "reverse seller income" step and
   created a negative-seller-balance edge case on refund. **We switch to the escrow model:** the
   buyer's payment is debited at checkout and **held by the platform** (no seller credit yet); the
   seller wallet is credited `order.seller_income_amount` (type `income`) only inside
   `DeliveryService::complete`, in the **same locked transaction** as the driver's 80% earning. The
   platform's cut (tax + 20% of fee) is simply never credited to anyone — money conservation holds
   (buyer paid `grand_total`; on completion seller + driver are paid their cut, platform keeps the
   rest; on refund the buyer gets the full `grand_total` back). **Consequences:** (a) remove the
   seller `WalletService::credit` from `CheckoutService::commit` (a Sprint 3 code change — flagged in
   T2); (b) §5.9 sweep no longer reverses seller income (above); (c) the seller income **report** now
   counts only `Pesanan Selesai` orders (realized income), which is more honest than counting
   in-flight orders. No platform wallet entity is introduced (YAGNI). Documented in the README.
   Refunds therefore only ever apply to non-final orders — once `Pesanan Selesai` (final per §5.6) a
   buyer can no longer be refunded, matching the owner's "after completion, only review/rating".
4. **➕ Overdue nets `Dikembalikan` orders OUT of the buyer/seller reports** (Sprint 4 carry-over).
   `ReportService::buyerSpending`/`sellerIncome` exclude refunded orders so the figures keep
   reconciling with real money after a sweep. Voucher `used_count` is **NOT** restored on refund
   (§5.3 documented choice) — the sweep deliberately leaves vouchers alone.
5. **➕ Admin promo/voucher management UI reuses the Sprint 4 admin API contracts**, adding Inertia
   `Web/Admin/*` controllers that call the **same `DiscountService`/validation** the API uses — no
   second source of truth. Full CRUD = list + create + view + toggle-active (no destructive delete;
   discounts are soft-disabled via `is_active`, matching the existing column).
6. **➕ One active delivery per driver (owner decision).** A driver may hold at most one job in
   flight: `DeliveryService::take` rejects (422, clear message) if the driver already owns a delivery
   with status `taken`. The driver dashboard renders a single "active job" slot rather than a list.
   More realistic and keeps the take/complete UX unambiguous. The §6 double-take race guard is
   unaffected (still a row lock + `driver_id IS NULL` check).
7. **ℹ️ Visual identity stays "Ocean" + Instrument Sans; process unchanged** — implementation on
   **Claude Sonnet** after approval; every UI slice visually verified via **Playwright MCP** at
   360 / 768 / 1280 / **1920**, both locales (ID/EN), light + dark; **`npm run build` before every
   Playwright pass**; commit locally only, no AI attribution.

## Instructions for the Sonnet implementer (READ FIRST)

You are implementing Sprint 5. Opus wrote and the owner approved this plan.

- **Authority order:** `docs/KetentuanPanitia.pdf` (Levels 5 + 6) → this `plan.md` →
  `docs/SEAPEDIA_TDD.md` (§ cited) → `CLAUDE.md`. If the TDD/plan conflict with the brief, the brief
  wins; flag in `progress.md`.
- **One slice = one commit.** Implement T1→T8 in order; do not batch. Follow the `vertical-feature`
  skill order for every slice. Commit messages per the `commit-message` skill.
- **Use the implementation skills for their slices:** `order-lifecycle` (T2 driver take/complete and
  T5 overdue → `Dikembalikan` — every status move goes through `OrderService::transition`, never a
  direct `status` set; each writes a history row), `money-and-checkout` mindset for the T2 escrow
  payout (seller income + driver earning) and T5's buyer refund (all integer IDR, row-locked via
  `WalletService`). The `security-pass` skill is Sprint 6, not now.
- **Honor the golden rules**, especially: rule 6 — the sweep deadline check, the earning timestamp,
  and any new "now" read use **`ClockService::now()`**, never `now()`/`Carbon::now()`; rule 3 —
  delivery assignment, driver earning credit, and the overdue refund each mutate inside
  `DB::transaction` + `lockForUpdate`; rule 7 — status only via `OrderService::transition`; rule 15 —
  take, complete, and sweep must all be idempotent / re-run-safe (take rejects a claimed job with
  409; complete on an already-completed delivery is a no-op or 422; sweep keyed on `refunded_at`).
- **Reuse, don't duplicate:** `DeliveryMethod::fee()`/`slaTicks()` already exist — read them, don't
  re-encode the fee table. `ClockService` exists — extend it (or add a tiny `advance()` that bumps
  `settings.simulated_now` by one tick) rather than reading the clock anywhere else. The full
  transition table already lives in `OrderService`.
- **Carry-overs you MUST fold (from `planning/sprint4/plan.md` → "Perlu dikerjakan next sprint"):**
  (a) overdue refund must NOT restore voucher `used_count` and MUST net `Dikembalikan` orders out of
  both reports — wire both into `OverdueService::sweep()` / `ReportService` in T5; (b) the full
  admin voucher/promo management UI lands here (T7); (c) still-open from Sprint 2: public store-page
  payload column-scoping + API-Resource shaping if the envelope repeats — fold in only where T6/T7
  pass through that code; (d) Sprint 4 left **`buyer/wallet/Show.vue`'s transaction table** with a
  360px overflow it judged acceptable — Sprint 5 adds several new tables (driver jobs, admin lists),
  so apply the **card-list-at-mobile** pattern (below) to every new table and, while you're at it,
  decide on the wallet table per the owner's flag.
- **Before every commit:** `./vendor/bin/sail pint` + `npm run lint`; run the `code-review` skill as
  a self-check; run the slice's Pest tests. A slice is not done until format + lint + tests pass.
- **Owner-run commands (ask, don't run yourself):** `migrate`/`seed`, `storage:link`, `npm run dev`,
  any `npx shadcn-vue@latest add ...` (**none needed — see below**), and any `.env` change.
  Everything else (test/pint/lint/format/build/vue-tsc/wayfinder `--with-form`) you run.
- **Any new TDD deviation you introduce MUST be added to the deviations list above**, with the reason.

## shadcn-vue components needed

**None new.** Levels 5 + 6 reuse components already in `resources/js/components/ui/`: `card` +
`table` + `badge` + `tabs` (driver dashboard, admin dashboard, management lists), `dialog` (take-job
confirm, advance-day confirm, promo/voucher create), `button` + `input` + `select` + `textarea` +
`label` (forms), `alert` + `skeleton` + `separator` (states + the mobile ledger/list pattern),
`pagination` (long admin lists), `tooltip`, `sonner` (action toasts). **Confirm each exists in
`resources/js/components/ui/<name>/` before importing.** If any is somehow missing, run
`npx shadcn-vue@latest add <name>` (owner-run) first — do NOT hand-roll a substitute (golden rule 12).

## UI/UX direction (from `ui-ux-pro-max` + `frontend-design`)

- **Driver jobs list & detail:** a job card shows route (store → buyer address), delivery method +
  fee, the **80% earning preview** (so the driver knows the payout before taking), and a primary
  "Ambil Pesanan" button; the detail page reuses `StatusTimeline`. On take, the button becomes
  "Selesaikan Pengiriman" once `Sedang Dikirim`. A job already taken by someone else disappears /
  shows "Sudah diambil" (the 409 path) rather than erroring loudly.
- **Driver dashboard:** a single **active-job slot** (the one in-flight delivery, or an empty state
  prompting "find a job") plus stat cards (completed count, **total earnings**) over a history list;
  tabular numerals; success-green for earnings. Empty state when no jobs taken yet.
- **Buyer/seller tracking:** the existing order-detail timeline gains the assigned driver's name +
  delivery status (`available`/`taken`/`completed` mapped to friendly labels), so both sides can see
  who's delivering. Order-status labels stay Indonesian in both locales (the existing `orderStatus.ts`
  helper); add a small `deliveryStatus` label helper the same way.
- **Admin dashboard:** a quiet monitoring grid of focal **count cards** (users / stores / products /
  orders / active discounts / deliveries by status / **overdue-eligible orders**) each linking to its
  list; an "Advance simulated day" control (current `simulated_now` shown, confirm dialog, result
  toast summarizing how many orders were swept). No charting library (brief: reports needn't be
  complex).
- **Promo/Voucher management:** list with active/expired/used-up badges, a create dialog (reusing the
  Sprint 4 request rules), a detail view, and a toggle-active switch — no hard delete.
- **Quality floor:** responsive 360/768/1280/1920, dark-mode parity, visible focus, reduced-motion,
  contrast ≥ 4.5:1; every list/detail has empty/loading/error states. **Every new table uses the
  card-list pattern at ≤ 768px** (status/label + count + amount rows divided by `Separator`, the
  pattern Sprint 4 adopted) — do NOT ship a raw `<Table>` that horizontally clips at 360px.

## Task breakdown (ordered vertical slices — one commit each)

### Level 5 — Driver delivery

- **T1 · `deliveries` resource + auto-create on seller process**
  - Files: `create_deliveries_table` migration (§7: `order_id` UNIQUE FK, `driver_id` nullable FK,
    `status` enum, `taken_at`/`completed_at` nullable, `earning_amount` default 0, indexes on
    `status` + `driver_id`); `DeliveryStatus` enum (`available`, `taken`, `completed`); `Delivery`
    model (`$fillable`, casts, `order()`/`driver()` relations) + `DeliveryFactory` (+ `taken()` /
    `completed()` states) + seeder hook; `DeliveryPolicy` (driver may take any `available`; may
    complete only a delivery they own — `delivery.driver_id === user.id`); **extend the Sprint 4
    seller `process` action** (`OrderService`/`SellerOrderController` path) so the same transaction
    that moves `Sedang Dikemas → Menunggu Pengirim` also inserts the `deliveries` row as `available`
    (idempotent: don't duplicate if one already exists for the order); `Order` gains a `delivery()`
    relation.
  - Business rules: §5.6 (order can't reach drivers before processed), §5.4 (fee carried on order),
    golden rules 3, 13, 15.
  - Acceptance: processing an order creates exactly one `available` delivery; re-processing (already
    `Menunggu Pengirim`) does not create a second row and still 422s on the status move.
  - Tests (Pest): seller process creates one `available` delivery; the delivery is unique per order
    (DB constraint + service guard); a non-processed order has no delivery row.
  - Commit: `feat(delivery): create delivery job when a seller processes an order`

- **T2 · DeliveryService take + complete (locked, escrow payout) + driver controllers/API — the core**
  - **First, the escrow change to Sprint 3 code (Decision 3):** remove the seller
    `WalletService::credit(type: Income, ...)` from `CheckoutService::commit` (it currently credits
    the seller at checkout, lines ~188–193). The buyer debit + order creation stay; the seller is no
    longer paid at checkout. Keep `order.seller_income_amount` populated (it's the amount to release
    later). Re-run the Sprint 3/4 checkout tests after — they must still pass (a checkout no longer
    moves the seller balance; assert that instead of the old credit).
  - Files: `DeliveryService` (`availableJobs()`, `activeJobFor(User $driver)`, `take(Delivery, User
    $driver)`, `complete(Delivery, User $driver)`); `take` = `DB::transaction` + `lockForUpdate` on
    the delivery, **reject (422) if the driver already owns a `taken` delivery** (Decision 6,
    one-active-job — check inside the txn), then assign only if `driver_id IS NULL` else throw a 409
    conflict, set `status = taken`, `taken_at = ClockService::now()`, and
    `OrderService::transition(order, SedangDikirim, $driver->id)`; `complete` = lock delivery, require
    it belongs to the driver + is `taken`, set `status = completed`, `completed_at =
    ClockService::now()`, compute `earning_amount = intdiv(fee*80,100)`, **credit the driver wallet**
    via `WalletService::credit(type: earning, ref: delivery)` **AND credit the seller wallet**
    `order.seller_income_amount` via `WalletService::credit(type: income, ref: order)` — escrow
    release — then `OrderService::transition(order, PesananSelesai, $driver->id)`, all in ONE
    transaction; `Web/DriverJobController` (index/show/take/complete) + `Web/DriverDashboardController`;
    `Api/Driver/JobController` mirroring; `TakeJobRequest`/`CompleteJobRequest` (thin — ids from route,
    no body, but present per rule 9); routes behind `auth` + `EnsureActiveRole:driver`; OpenAPI.
  - Business rules: §5.5, §5.6, §6 (double-take), §5.2 (seller income on completion — escrow),
    golden rules 3, 6, 7, 15. Use `order-lifecycle` + the `money-and-checkout` mindset for the payout.
  - Acceptance: a driver takes an `available` job → order `Sedang Dikirim`, history written, job now
    `taken` with `driver_id`; the same driver completes it → order `Pesanan Selesai`, history written,
    **driver credited 80% of fee (`earning`) AND seller credited `seller_income_amount` (`income`)**,
    `earning_amount` stored; a driver who already has an active job → 422 on take; a second driver
    taking the same job → 409; completing a job you don't own → 403; completing an already-completed
    job → idempotent no-op / 422 (no double payout).
  - Tests (Pest, CRITICAL): **two concurrent takes on one job → exactly one succeeds, the other 409,
    `driver_id` set once** (dual-connection lock proof, like the Sprint 3 oversell / Sprint 4 voucher
    test); a driver with an active job is refused a second take (422); on complete, earning =
    `intdiv(fee*80,100)` AND seller income credited, each once; checkout does NOT move the seller
    balance (escrow); cross-driver complete → 403; double-complete pays out only once.
  - Commit: `feat(delivery): add driver take and complete with escrow payout`

- **T3 · Driver UI (jobs, detail, dashboard) + buyer/seller tracking**
  - Files: `driver/jobs/Index.vue` (available jobs, card-list at mobile, earning preview),
    `driver/jobs/Show.vue` (route + timeline + take/complete actions with confirm dialog),
    `driver/Dashboard.vue` (active + history + total earnings stat cards); add the driver entries to
    the role-aware nav (sidebar + mobile bottom-nav); extend `buyer/orders/Show.vue` +
    `seller/orders/Show.vue` to show assigned driver + delivery status; `deliveryStatus.ts` label
    helper; ID + EN i18n for all new chrome.
  - Business rules: brief driver workflow + tracking visibility; golden rule 17 (presentation only).
  - Acceptance: a driver logs in → sees available jobs → takes one (confirm) → it moves to the active
    slot and the order shows `Sedang Dikirim` on the buyer page → completes it → dashboard earnings go
    up and the buyer/seller see `Pesanan Selesai` + the driver's name.
  - Tests (Pest): the driver jobs endpoint lists only `available` jobs (status `Menunggu Pengirim`);
    the dashboard endpoint returns the driver's own active + history + summed earnings (race covered
    by T2).
  - Commit: `feat(delivery): add driver job UI, dashboard, and order tracking`

### Level 6 — Time-sim + Overdue + Admin

- **T4 · Time simulation: clock advance + artisan command + admin endpoint**
  - Files: extend `ClockService` with `advance(int $ticks = 1)` (bump `settings.simulated_now` by N
    days, persisting through the `Setting` model; initialize from real now if unset);
    `seapedia:advance-day` artisan command (calls `ClockService::advance(1)` then
    `OverdueService::sweep()` — wire the sweep in T5, leave a TODO-free seam here);
    `Web/Admin/ClockController@advance` + `Api/Admin/ClockController` behind `auth` + `is_admin`;
    route `POST /admin/clock/advance`; expose `simulated_now` to the admin dashboard payload; OpenAPI.
  - Business rules: §5.7 (1 tick = 1 day; advance then sweep), golden rule 6 (clock is the only time
    source), rule 4 (admin-gated), rule 15 (advance is safe to repeat — it just moves time forward).
  - Acceptance: an admin advances the day → `simulated_now` moves forward one day and the response
    reports the sweep result; `php artisan seapedia:advance-day` does the same headlessly; a non-admin
    → 403.
  - Tests (Pest): `advance()` moves `simulated_now` by exactly one day; the artisan command advances
    + triggers the sweep; non-admin POST → 403.
  - Commit: `feat(admin): add simulated-clock advance via endpoint and artisan command`

- **T5 · OverdueService::sweep() — idempotent refund (§5.9, escrow-adapted) + report netting**
  - Files: `OverdueService::sweep()` — select orders where `sla_due_at < ClockService::now()` AND
    status ∈ {`sedang_dikemas`,`menunggu_pengirim`,`sedang_dikirim`} AND `refunded_at IS NULL`; for
    each, in ONE `DB::transaction`: `lockForUpdate` the order, re-check `refunded_at IS NULL` (abort
    if set), credit buyer `grand_total` (`WalletService::credit` type `refund`), restore stock per
    `order_item` (`product.stock += quantity`, lock products in `product_id` order to match the
    checkout lock order), set `refunded_at = ClockService::now()`, and
    `OrderService::transition(order, Dikembalikan, null, 'Auto-refund: SLA overdue')`. **No seller
    reversal** — under escrow (Decision 3) the seller was never paid for a non-completed order, so
    there is nothing to claw back; this is why only non-final statuses are eligible and a
    `Pesanan Selesai` order is never swept. **Do NOT touch voucher `used_count`** (§5.3). Update
    `ReportService::buyerSpending`/`sellerIncome` to **exclude `Dikembalikan` orders** and base seller
    income on `Pesanan Selesai` orders (realized, per Decision 3). Returns a count summary for the
    advance-day response.
  - Business rules: §5.7, §5.9 (escrow-adapted), §6 (double-refund guard), golden rules 3, 5, 6, 7, 15.
  - Acceptance: advancing past an order's SLA auto-refunds it → buyer wallet shows a `refund` of
    `grand_total`, stock is restored, status is `Dikembalikan` with a history row, and the seller
    balance is **untouched** (was never credited); running the sweep again changes nothing
    (idempotent); a `Pesanan Selesai` order is never swept; the buyer/seller reports drop the refunded
    order from their totals.
  - Tests (Pest, CRITICAL): an overdue paid order is refunded once and the buyer wallet + stock
    reconcile while the seller balance stays unchanged; **running sweep twice does not double-refund /
    double-restore** (`refunded_at` sentinel); a not-yet-overdue order and a `Pesanan Selesai` order
    are untouched; voucher `used_count` unchanged after refund; reports exclude the refunded order.
  - Commit: `feat(overdue): add idempotent SLA overdue auto-refund sweep`

- **T6 · Admin monitoring dashboard**
  - Files: `AdminMonitorService` (eager-loaded counts: users by role, stores, products, orders by
    status, active vs expired discounts, deliveries by status, overdue-eligible count, current
    `simulated_now` — all via aggregate queries, **no N+1**); `Web/Admin/DashboardController` +
    `Api/Admin/DashboardController`; `admin/Dashboard.vue` (count cards linking to lists + the
    advance-day control from T4); admin nav entries; i18n; OpenAPI.
  - Business rules: brief admin monitoring; golden rules 4, 16.
  - Acceptance: an admin sees live counts across all monitored resources and the current simulated
    day, can advance the day from here, and each card links to the relevant list; a non-admin → 403.
  - Tests (Pest): the dashboard payload returns correct counts against a seeded dataset; non-admin
    access blocked.
  - Commit: `feat(admin): add monitoring dashboard with resource counts`

- **T7 · Admin Voucher/Promo management UI (Level 6 carry-over from Sprint 4)**
  - Files: `Web/Admin/PromoController` + `Web/Admin/VoucherController` (index/create/store/show +
    `toggleActive`) calling the **same** Sprint 4 `StorePromoRequest`/`StoreVoucherRequest` +
    `DiscountService`; `admin/promos/Index.vue` + `admin/promos/Create.vue` (or a create dialog) +
    `admin/vouchers/*`; active/expired/used-up badges; routes behind `is_admin`; i18n. (API already
    exists from Sprint 4 T1 — this is the Inertia surface only.)
  - Business rules: brief Level 6 admin management; golden rules 1–2, 4, 9, 12 (use shadcn dialog/form).
  - Acceptance: an admin lists promos + vouchers with status badges, creates one through the UI
    (validation errors shown inline), views detail, and toggles a code active/inactive; a non-admin →
    403.
  - Tests (Pest): admin store via the web controller persists + redirects with the code; toggle flips
    `is_active`; non-admin → 403 (validation race already covered by Sprint 4 T1).
  - Commit: `feat(admin): add voucher and promo management UI`

- **T8 · Seed driver/overdue demo + extend demo data + README**
  - Files: `DeliverySeeder` / extend the demo seeders so the demo dataset has: a driver user, an order
    sitting at `Menunggu Pengirim` with an `available` delivery (ready to take), and at least one
    **overdue-eligible** order (paid, `sla_due_at` already in the past relative to a fresh
    `simulated_now`) so advancing the day visibly refunds it; wire into `DatabaseSeeder`; README gains
    the **driver earning rule (80/20)**, the **escrow model** (buyer payment held by the platform;
    seller income released only on `Pesanan Selesai`; no platform wallet entity; money-conservation
    explanation), the **delivery SLA + time-simulation** mechanic (how to advance the day via UI +
    artisan), the **overdue auto-refund effects** (§5.9, escrow-adapted: buyer refund + stock restore,
    no seller reversal) and its idempotency, and the **admin dashboard / discount management** notes,
    plus a "Demo path (Sprint 5)" section.
  - Business rules: §12 seed; brief demo.
  - Acceptance: `migrate:fresh --seed` yields a takeable job + an overdue order; advancing the day
    once refunds the overdue order and the README documents every rule a grader checks.
  - Tests: none (covered by T1–T7 feature tests).
  - Commit: `feat(db): seed driver and overdue demo data and document levels 5-6`

## Demo checklist (end of sprint)

1. Seller processes an order → a delivery job appears for drivers; the order can't reach a driver
   before this.
2. Driver logs in → "Pesanan Tersedia" → **takes** a job (order `Sedang Dikirim`, timeline updates on
   buyer + seller) → **completes** it (order `Pesanan Selesai`); driver dashboard earnings rise by
   **80% of the delivery fee** AND the **seller wallet is credited its income** (escrow release —
   show the seller balance moving only now, not at checkout).
3. Two drivers race for the same job → exactly one wins; the other sees "Sudah diambil" (409). A
   driver who already has an active job is refused a second take (one-active-job).
4. Admin **advances the simulated day** (UI + `php artisan seapedia:advance-day`); an overdue order
   auto-refunds → buyer wallet `refund`, stock restored, status `Dikembalikan`, history row; the
   **seller balance is untouched** (never paid for an undelivered order); running it again is a no-op
   (idempotent). Reports drop the refunded order.
5. Admin **monitoring dashboard** shows live counts + current simulated day; admin **voucher/promo
   management UI** lists/creates/toggles codes.
6. `/api/v1` mirrors driver jobs, clock advance, and admin surfaces — all in Swagger.
7. Responsive + dark-mode + both-locale pass at 360/768/1280/1920 via Playwright (build first), with
   order/delivery-status labels staying Indonesian in EN.

## Playwright visual-QA checks (READ — directed per the Sprint 4 findings)

**These corrections are mandatory; Sprint 4's QA missed two clipping bugs by trusting the wrong
signal. Do not repeat them.**

- **`npm run build` FIRST, every pass.** Localhost serves the compiled build, not Vite HMR — an
  un-built change shows stale UI and you'll "verify" the wrong thing. (Project memory: build before
  Playwright.)
- **Do NOT use `document.documentElement.scrollWidth > clientWidth` as the overflow check.**
  `AppContent` has `overflow-x-hidden`, so an ancestor can clip a child's overflow without the
  document ever reporting it — that's exactly how Sprint 4's order-summary and report-table clips
  hid the grand total at 360px with no scrollbar. Instead, **measure `getBoundingClientRect()` on the
  actual card/table elements** (assert `rect.right <= viewport width` and the key number — total,
  earning, amount — is fully inside its card), AND look closely at the rendered screenshot. Every new
  table (driver jobs, admin lists, dashboard counts) gets this treatment at **360px** specifically.
- **Sizes:** screenshot every new/changed page at **360 / 768 / 1280 / 1920** (1920 is the
  competition demo display — project memory).
- **Locales:** both **ID and EN**; confirm order-status AND the new delivery-status labels stay
  **Indonesian** in EN while all other chrome translates.
- **Themes:** **light + dark** parity on every page.
- **States:** empty (no jobs / no orders / no discounts), loading (skeleton), and error (409
  taken-job, 422 invalid transition, refund already done) for each list/detail.
- **Interactive click-throughs to actually perform (not just screenshot):** take a job and watch the
  buyer page status flip; complete it and watch driver earnings rise; advance the day and watch an
  overdue order become `Dikembalikan` with the wallet rows appearing. Verify the timeline updates on
  both the actor's and the counterpart's pages.
- **If the Playwright MCP disconnects in WSL,** follow the project's "Playwright MCP WSL setup" memory
  fix-chain (nvm/symlinks/bundled Chromium/`--executable-path`/apt libs) before retrying — don't
  silently skip the visual QA.

## Risks / open questions

- **RESOLVED (owner) — escrow instead of reverse-on-refund.** The earlier open question (what to do
  when a seller's spendable balance is below the amount to reverse) is gone: with the escrow model
  (Decision 3) the seller is paid only on `Pesanan Selesai`, so an overdue order never paid them and
  there is nothing to reverse. No negative-balance edge case, money conservation holds, and §5.9's
  intent is still met. The cost is a Sprint 3 code change (remove the checkout-time seller credit) —
  scoped into T2 with regression coverage.
- **Platform "held funds" are not a wallet entity.** Between checkout and completion the buyer's
  payment is simply debited and not credited anywhere; the platform's retained cut (tax + 20% of fee)
  is never credited to a wallet. This is the documented simplification (no platform wallet) — money
  still balances because completion pays seller + driver and refund returns the full `grand_total`.
- **Earning rounding.** `intdiv(fee*80,100)` (floor); exact for all three locked fees, so moot in
  practice — locked anyway for integer-IDR consistency.
- **Sweep trigger surface.** Both the admin endpoint and the artisan command run the same
  `OverdueService::sweep()`; no queue/scheduler is added (the brief accepts "command or manual admin
  trigger"). A scheduled worker is out of scope.
- **"Active role" for driver actions.** Driver endpoints sit behind `EnsureActiveRole:driver`; a user
  who owns the driver role but is acting as a buyer must switch active role first (server-resolved,
  never from the body) — consistent with §4 / golden rule 4.

## Perlu dikerjakan next sprint (carry-over)

To be picked up by Sprint 6; Sprint 6's Sonnet instructions must reference this section.

- **Security pass (Level 7)** — the `security-pass` skill audit: grep `v-html`/`whereRaw`/`$guarded`,
  confirm Policy + middleware on every private route (the new driver/admin routes included), token
  expiry; demonstrate XSS + SQLi test cases.
- **Real iPaymu** — `IpaymuGateway`'s real v2 call + webhook (carried since Sprint 4) → Sprint 6.
- **Swagger/OpenAPI finalize + README finalize + final deploy + demo recording** → Sprint 6.
- **Still-open from Sprint 2:** public store-page payload column-scoping; API-Resource shaping if the
  envelope repeats; breadcrumb i18n — fold in wherever Sprint 6 passes through.
- **`buyer/wallet/Show.vue` mobile table** — if T3 didn't already redesign it to the card-list
  pattern, decide it in Sprint 6 per the owner's Sprint 4 flag.
- **Marketplace homepage + real product photos + product re-theme** (assets in `public/images/`) →
  **final sprint** (owner's decision; the banner/category/product images are already staged in git).
- **Moving `planning/` into `docs/`** → final sprint (owner's instruction).

## Out of scope (deferred)

- Real iPaymu integration, the Level 7 security hardening pass, Swagger/README finalize, deployment
  polish, and the demo recording → **Sprint 6**.
- Marketplace homepage + real product photos + product re-theme, and moving `planning/` into `docs/`
  → **final sprint** (owner's decision).
- Any scheduled/queued automatic overdue worker (cron daemon) — the admin trigger + artisan command
  satisfy the brief; a background scheduler is not built.
