# Sprint 8 Plan — Post-Level Polish Batch & Documentation Finale

> Levels 1–6 are DONE. This sprint bundles nine requested improvements that
> raise UX quality and sharpen the workspace views for every role, then closes
> with a documentation pass. It changes no graded money/lifecycle rule — the
> feature work is sorting, grouping, display, and a handful of legal, documented
> extensions; the last item aligns the README and repo docs with the shipped
> state.

## Goal

When this sprint is done: the catalog search surfaces relevant **stores**, not
just products; quantity inputs clamp to available stock everywhere they appear;
the account page is redesigned with a secured avatar upload and self-service
role management (add a role, remove a role, delete the account); drivers earn
more concurrent capacity as they complete deliveries on time; the admin, seller,
and driver workspaces group their lists by category or date and float
near-cancel orders to the top; a first-time role switch is gently pointed out;
and the README plus supporting docs describe the system as it actually ships.

## Scope

Nine items, each its own small vertical change on top of already-graded
criteria:

1. **Catalog** — search results also surface matching stores ("Toko terkait"),
   ranked name-match first.
2. **Cart / product** — quantity inputs clamp to stock on product detail and in
   the cart/checkout.
3. **Account** — redesigned profile; secured avatar upload; add-role,
   remove-role, and account deletion (soft-delete + anonymize).
4. **Delivery** — driver concurrent-job cap grows with on-time completions.
5. **Admin** — fix the overdue page's double sidebar; per-category product view;
   feature-completeness audit against the Level 6 monitoring checklist.
6. **Seller / driver views** — seller orders sorted near-cancel first and
   grouped by date; driver dashboard shows urgency and date-grouped history;
   seller products grouped per category.
7. **Docs** — README aligned to the brief and the as-built system; architecture
   guide; repo tidy.
8. **Onboarding** — a first-visit pointer to the role switcher.

## Locked decisions

| # | Decision | Choice | Basis |
|---|----------|--------|-------|
| Delivery SLA display | How to show "near cancel" | Server-side urgency enum (`SlaUrgency`: Overdue ≤ 0 / Critical ≤ 1 day-tick / Normal); UI only formats it | Brief requires defining SLA rules and showing overdue data; classification is a read-only display of that rule |
| Driver concurrency | How many jobs a driver holds at once | Progressive cap: 1 by default, 2 after 15 on-time completions, 3 after 30 | Brief is silent on per-driver concurrency, so this is a documented, spec-legal extension — reusing existing job history, no rating subsystem |
| Account deletion | What "delete account" does | Soft-delete + anonymize PII, guarded by no-active-work and zero-balance checks | Preserves historical orders/deliveries; frees unique fields for re-registration |
| Role removal | Removing a role with dependents | Hard-block (422) a seller with a live store or a driver with active deliveries; block removing the last non-admin role | Never orphan dependent records or leave a user role-less |
| Add role | Self-service role acquisition | Add buyer/driver immediately; adding seller requires creating a store; `admin` can never be self-added | Brief seeds/documents admin separately |
| "Works on any machine" | Setup story | Docker (Sail) as the primary path, native documented, plus a live deploy link | Runs identically on Windows (WSL2), macOS, and Linux |

## Authority model (3-tier)

Recorded here because this sprint's docs pass makes it explicit across the repo:

1. **`docs/SEAPEDIA_SPEC.md`** — the committee brief. *What must be true.*
   External, graded, always wins; code that violates it is a bug.
2. **Code + `README.md`** — *what is true now.* The concrete choices the brief
   left open; the README's "Design Decisions" is the living record.
3. **`docs/SEAPEDIA_TDD.md`** — *why (history).* Non-authoritative; carries a
   banner saying so. Where it disagrees with the brief or the code, they win.

Where a request goes beyond the brief, we build it only if it does not conflict
("build if legal") and document it as an explicit extension.

## Documentation deliverables (item 7)

- **README** — corrected stack (Laravel 13, PHP 8.3), a Docker-first setup that
  works from a fresh clone, demo credentials, and a "Design Decisions" section
  covering money math, the delivery-fee model, escrowed seller income, driver
  earning (80% of the delivery fee), the SLA table, idempotent overdue refunds,
  the driver reliability tiers, multi-role self-service, and the secured avatar
  upload. Authority points at `docs/SEAPEDIA_SPEC.md`.
- **`docs/ARCHITECTURE.md`** — the engineering guide: stack, layering,
  invariants, definition of done, commit conventions, and commands.
- **`docs/SEAPEDIA_TDD.md`** — a banner marking it non-authoritative under the
  3-tier model above.

## Ground truth (verified against the code)

- **Stack:** Laravel 13, PHP 8.3, Vue 3.5, Inertia, Tailwind 4, MySQL 8.
- **Driver earning:** 80% of `delivery_fee` (`intdiv(delivery_fee * 80, 100)`).
- **SLA durations** (1 tick = 1 simulated day): Instant 1, Next Day 2, Regular 4.
- **Delivery fee:** `base(method) + billable_km × rate(method) + weight_fee`;
  base 20k/10k/5k, per-km 2 500/1 500/1 000, Rp2 000/kg over 1 free kg,
  Haversine distance, 80 km cap; never taxed; quote equals charge.
- **Discount:** promo then voucher, both combine off the original subtotal;
  PPN 12% applied to the discounted subtotal; delivery fee exempt.
- **Overdue:** auto-refund to the buyer wallet, idempotent via a `refunded_at`
  sentinel; seller income escrowed until delivery; stock restored.
- **Time simulation:** `php artisan seapedia:advance-day` plus the admin Time
  Machine buttons on the overdue page.
- **Admin:** seeded `admin@seapedia.test` (`is_admin`).

## Outcome

All nine items shipped as focused commits, each with tests where a concurrency
or idempotency path was involved (e.g. the driver cap check runs under a row
lock; the SLA urgency classification has boundary tests). The full Pest suite is
green, and Pint / ESLint pass.
