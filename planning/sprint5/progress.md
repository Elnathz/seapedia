# Sprint 5 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

### Level 5 — Driver delivery

- [ ] T1 · `deliveries` resource + auto-create on seller process — `feat(delivery): create delivery job when a seller processes an order`
- [ ] T2 · DeliveryService take + complete (locked) + driver controllers/API — `feat(delivery): add driver take and complete with locked assignment`
- [ ] T3 · Driver UI (jobs, detail, dashboard) + buyer/seller tracking — `feat(delivery): add driver job UI, dashboard, and order tracking`

### Level 6 — Time-sim + Overdue + Admin

- [ ] T4 · Time simulation: clock advance + artisan command + admin endpoint — `feat(admin): add simulated-clock advance via endpoint and artisan command`
- [ ] T5 · OverdueService::sweep() idempotent refund + report netting — `feat(overdue): add idempotent SLA overdue auto-refund sweep`
- [ ] T6 · Admin monitoring dashboard — `feat(admin): add monitoring dashboard with resource counts`
- [ ] T7 · Admin Voucher/Promo management UI — `feat(admin): add voucher and promo management UI`
- [ ] T8 · Seed driver/overdue demo + README — `feat(db): seed driver and overdue demo data and document levels 5-6`

## Tests

- [ ] Seller process creates one `available` delivery; unique per order; non-processed order has none (T1)
- [ ] Two concurrent takes on one job → one succeeds, one 409, `driver_id` set once (T2, CRITICAL)
- [ ] Driver with an active job refused a second take → 422 (one-active-job) (T2)
- [ ] On complete: driver earning intdiv(fee*80,100) AND seller income credited, each once; checkout does NOT move seller balance (escrow) (T2)
- [ ] Cross-driver complete → 403; double-complete pays out only once (T2)
- [ ] Driver jobs endpoint lists only available jobs; dashboard returns own active+history+earnings (T3)
- [ ] `advance()` moves `simulated_now` by one day; artisan advances + sweeps; non-admin POST → 403 (T4)
- [ ] Overdue paid order refunded once; buyer wallet + stock reconcile; seller balance untouched; sweep twice = no double effects (T5, CRITICAL)
- [ ] Not-yet-overdue + Pesanan Selesai untouched; voucher used_count unchanged; reports exclude refunded (T5)
- [ ] Admin dashboard counts correct against seeded data; non-admin blocked (T6)
- [ ] Admin web store persists + redirects; toggleActive flips is_active; non-admin → 403 (T7)

## Visual QA (Playwright MCP) — build first; 360/768/1280/1920, both locales, light + dark

**Direction (per Sprint 4 findings — see plan.md "Playwright visual-QA checks"):** measure
`getBoundingClientRect()` on cards/tables, NOT `document.scrollWidth` (AppContent's `overflow-x-hidden`
hides clips). Every new table uses the card-list pattern at ≤768px.

- [ ] Driver jobs list + detail: earning preview, take/complete actions, "Sudah diambil" (409) state — 360/768/1280/1920, ID+EN, light+dark
- [ ] Driver dashboard: active/completed/earnings cards + history + empty state
- [ ] Buyer/seller order tracking: assigned driver + delivery status on the timeline (labels stay Indonesian in EN)
- [ ] Admin dashboard: count cards + simulated_now + advance-day control + result toast
- [ ] Admin promo/voucher management: list with badges, create dialog with inline errors, toggle-active
- [ ] Interactive click-throughs: take→buyer sees Sedang Dikirim; complete→earnings rise; advance day→overdue order becomes Dikembalikan with wallet rows

## Notes / deviations recorded

- **ESCROW (owner decision, 2026-06-25) — deviates from §5.9 as written.** Seller income is credited
  on `Pesanan Selesai` (in `DeliveryService::complete`), NOT at checkout. Therefore the §5.9 "reverse
  seller income" step is dropped — an overdue order was never completed, so the seller was never paid.
  This resolves an internal TDD inconsistency (§5.2 already says income is credited "when
  completed/paid") and removes the negative-seller-balance edge case. Brief intent still met (buyer
  made whole, seller keeps nothing for an undelivered order). **TDD §5.2/§5.9 should be reconciled to
  the escrow model in a later docs pass** (not edited yet — flagged here per CLAUDE.md's
  plan-vs-TDD-conflict rule). Touches Sprint 3 `CheckoutService::commit` (remove checkout-time seller
  credit) — scoped into T2 with regression coverage.
- **One active delivery per driver (owner decision, 2026-06-25).** `DeliveryService::take` rejects
  (422) when the driver already holds a `taken` delivery; dashboard shows a single active slot.
- Refunds only apply to non-final orders — once `Pesanan Selesai` (final per §5.6) a buyer cannot be
  refunded (owner: "after completion, only review/rating"). Already enforced by the transition table.
