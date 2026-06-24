# Sprint 5 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

### Level 5 — Driver delivery

- [x] T1 · `deliveries` resource + auto-create on seller process — `feat(delivery): create delivery job when a seller processes an order`
- [x] T2 · DeliveryService take + complete (locked, escrow payout) + driver controllers/API — `feat(delivery): add driver take and complete with escrow payout`
- [x] T3 · Driver UI (jobs, detail, dashboard) + buyer/seller tracking — `feat(delivery): add driver job UI, dashboard, and order tracking`

### Level 6 — Time-sim + Overdue + Admin

- [x] T4 · Time simulation: clock advance + artisan command + admin endpoint — `feat(admin): add simulated-clock advance via endpoint and artisan command`
- [x] T5 · OverdueService::sweep() idempotent refund + report netting — `feat(overdue): add idempotent SLA overdue auto-refund sweep`
- [x] T6 · Admin monitoring dashboard — `feat(admin): add monitoring dashboard with resource counts`
- [x] T7 · Admin Voucher/Promo management UI — `feat(admin): add voucher and promo management UI`
- [x] T8 · Seed driver/overdue demo + README — `feat(db): seed driver and overdue demo data and document levels 5-6`

## Tests

- [x] Seller process creates one `available` delivery; unique per order; non-processed order has none (T1)
- [x] Two concurrent takes on one job → one succeeds, one 409, `driver_id` set once (T2, CRITICAL) — `DeliveryConcurrencyTest`
- [x] Driver with an active job refused a second take → 422 (one-active-job) (T2)
- [x] On complete: driver earning intdiv(fee*80,100) AND seller income credited, each once; checkout does NOT move seller balance (escrow) (T2)
- [x] Cross-driver complete → 403; double-complete pays out only once (T2)
- [x] Driver jobs endpoint lists only available jobs; dashboard returns own active+history+earnings (T3)
- [x] `advance()` moves `simulated_now` by one day; artisan advances + sweeps; non-admin POST → 403 (T4)
- [x] Overdue paid order refunded once; buyer wallet + stock reconcile; seller balance untouched; sweep twice = no double effects (T5, CRITICAL) — `OverdueSweepTest`
- [x] Not-yet-overdue + Pesanan Selesai untouched; voucher used_count unchanged; reports exclude refunded (T5)
- [x] Admin dashboard counts correct against seeded data; non-admin blocked (T6)
- [x] Admin web store persists + redirects; toggleActive flips is_active; non-admin → 403 (T7)

Full Pest suite: 162 tests / 636 assertions passing. `pint`, `npm run lint`, `npm run format`,
`vue-tsc --noEmit`, and `npm run build` all clean as of the final commit.

## Visual QA (Playwright MCP) — PARTIAL, owner-curtailed (deviation from CLAUDE.md)

**Deviation note:** CLAUDE.md's execution model mandates a Playwright pass per UI slice at
360/768/1280/1920, both locales, light+dark. The owner explicitly said to stop using Playwright
mid-pass ("gausah playwright mulai sekarang dan next sprint") — this applies to the rest of Sprint 5
*and Sprint 6*. The checklist below reflects what was actually verified (1280px, dark mode, ID
locale only, via live click-throughs) before the instruction landed; the unchecked items were
**not** verified visually and are an accepted gap, not an oversight.

- [x] Driver jobs list + detail: earning preview, take/complete actions both performed live (job
  moved available → taken → completed; earning preview Rp 4.000 matched the realized payout) — 1280px, ID, dark only
- [ ] "Sudah diambil" (409) conflict state — not screenshotted (covered by `DriverJobApiTest`/`DriverTakeCompleteTest` instead)
- [x] Driver dashboard: active/completed/earnings cards + history verified live (count 0→1, Rp 0→Rp 4.000) — 1280px, ID, dark only
- [ ] Driver dashboard empty state — not screenshotted this pass (was screenshotted, matches the Sprint 3 EmptyState pattern)
- [x] Buyer/seller order tracking: assigned driver name ("Driver One") + delivery status badge
  ("Sedang Diantar") confirmed on both buyer's and seller's order detail pages — 1280px, ID, dark only
- [x] Admin dashboard: count cards + simulated_now + advance-day control + confirm dialog + sweep
  effect (Pesanan Overdue 1→0, Dikembalikan 0→1) + idempotency (clicked twice, no double-refund) — 1280px, ID, dark only
- [x] Admin promo/voucher management: list with badges, create dialog (server-side inline
  validation error shown for value>100 on a percentage promo), toggle-active flipping the badge —
  all performed live — 1280px, ID, dark only
- [x] Interactive click-throughs all performed for real (not just screenshotted): took the seeded
  job as `driver1` → confirmed `buyer1`'s and `seller1`'s order pages showed Sedang Dikirim live;
  completed it → driver dashboard earnings rose Rp 0→Rp 4.000 and seller's income report rose
  Rp 0→Rp 18.000 (escrow release, confirmed via live report reload); advanced the simulated day as
  `admin` → the seeded overdue order became Dikembalikan with the warning banner disappearing.
- [ ] 360/768/1920 breakpoints — not checked (only 1280 was QA'd before Playwright was stopped)
- [ ] Light mode — not checked (only dark mode was QA'd)
- [ ] EN locale — not checked (only ID locale was QA'd)
- **Bug found and fixed during this partial pass:** seller's income-report "Rincian per Status"
  card rendered as a bare empty card (no rows, no message) for any seller with orders but zero
  `Pesanan Selesai` orders — a direct consequence of T5's escrow-aware report change. Fixed by
  adding a `report.sellerBreakdownEmpty` explanatory message (`fix(report): explain why seller
  breakdown is empty under escrow`).

## Design rework (post-review, 2026-06-25)

Owner flagged that Sprint 5 UI shipped as generic shadcn defaults — the `ui-ux-pro-max` /
`frontend-design` skills were never invoked during the Sonnet build because they weren't wired
into CLAUDE.md or the project skills (the verbal request was lost across compaction/handoff). Fixed:

- **Rule wired permanently** so it can't be dropped again: CLAUDE.md golden rule 11a + execution
  model + DoD, the `vertical-feature` skill (step 10 + checklist), the `sprint-planner` skill
  ("Design direction" section), and a memory entry (`docs(ui): require ui-ux-pro-max/frontend-design...`).
- **Pages reworked through the design skills** (anchored on the existing teal "sea" brand tokens —
  no new palette): admin ops dashboard (signature "time-machine" command bar + KPI band +
  proportion bars), promo/voucher management lists (status-accent rows, voucher usage meter),
  driver dashboard + jobs board + job detail (dispatch rows, prominent active-job slot, 80/20
  payout split made legible). New primitives: `StatTile`, `StatBar` + status fill/accent tokens.
- Verified: `vue-tsc`, ESLint, Prettier, `npm run build` clean; 42 dashboard/driver/admin Pest
  tests pass. Visual QA still skipped per the no-Playwright instruction (1280px reasoning only).

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
