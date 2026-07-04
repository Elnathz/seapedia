# SEAPEDIA — Architecture & Engineering Guide

This document explains how SEAPEDIA is built: the stack, the layering, the
invariants the code must uphold, and the conventions the team follows. It is a
companion to the [`README.md`](../README.md) (how to run it) and the committee
brief in [`docs/SEAPEDIA_SPEC.md`](./SEAPEDIA_SPEC.md) (what it must do — the
graded source of truth). Where anything here disagrees with the brief, the
brief wins.

## Stack

Laravel 13 (PHP 8.3) · Inertia · Vue 3 + TypeScript · shadcn-vue · Tailwind 4 ·
MySQL 8 · Docker (Laravel Sail for dev) · Sanctum · Pest · iPaymu (v2).
TypeScript is kept intentionally loose (`any` is allowed where it unblocks).

## Layering

SEAPEDIA is a single integrated Laravel + Inertia + Vue application (a
monolith that is also API-capable). Responsibilities are split by layer, and
each layer has one home:

| Concern | Location |
| --- | --- |
| Business logic | `app/Services` (framework-agnostic; reused by web **and** `/api/v1`) |
| Ownership / access | `app/Policies` |
| Request validation | `app/Http/Requests` (`Store…Request` / `Update…Request`) |
| Fixed value sets | `app/Enums` (no magic strings) |
| HTTP entry points | `app/Http/Controllers` — web and API controllers kept separate |
| Presentation | `resources/js/pages` + `resources/js/components` (Vue) |

Naming: models are singular; services are `XxxService`; form requests are
`Store…Request` / `Update…Request`.

## Architecture principles

These are the rules that keep the system correct and reviewable.

1. **Controllers are thin.** A controller validates via a FormRequest, calls a
   single Service method, and returns an Inertia response or JSON. No business
   logic in controllers.
2. **All business logic lives in Services.** Services are framework-agnostic so
   the web and `/api/v1` layers can share them.
3. **Money and state changes run in locked transactions.** Any mutation of a
   balance, stock count, `used_count`, or order status runs inside
   `DB::transaction()` with `lockForUpdate()` on the affected rows.
4. **Authorization follows the active role, resolved server-side.** Ownership is
   enforced with Policies; role-gating with the `EnsureActiveRole` middleware.
   The role is never trusted from the request body.
5. **Money is integer IDR** (`BIGINT UNSIGNED`). No floats, ever.
6. **Time comes only from `ClockService::now()`** — never `now()` /
   `Carbon::now()` directly in business logic — so the simulated-day "time
   machine" that drives overdue handling stays authoritative.
7. **Order status changes only through `OrderService`,** using the locked
   transition table. No status string is set on the model anywhere else, and
   every change writes an `order_status_histories` row.
8. **Eloquent only.** No `DB::raw` / `whereRaw` with interpolated input
   (parameter-bound `orderByRaw` for a constant SQL shape is fine). Never
   `v-html` on user-generated content.
9. **Every write endpoint has a FormRequest** enforcing types, ranges, and
   required fields before data reaches a controller.
10. **The payment gateway sits behind an interface.** iPaymu is used through a
    `PaymentGateway` interface with a `FakeGateway` fallback. Checkout never
    calls a gateway — it pays from the wallet only.
11. **UI is built deliberately on shadcn-vue, not shipped as raw defaults.**
    shadcn-vue is the component substrate; each page/component gets a
    considered palette, type scale, spacing, hierarchy, and a signature element
    so it doesn't read as a template. A grid of identical stat cards plus
    badge-and-count rows is the failure mode to avoid. A shadcn-vue component
    must be added with `npx shadcn-vue@latest add <name>` (which copies its
    files into `resources/js/components/ui/`) before it can be imported.
12. **Idempotency for anything retryable.** Payment webhooks, the overdue
    sweep, delivery completion, and seller order processing must be safe to run
    twice — repeated requests must not create duplicate effects (guarded with
    sentinels like `refunded_at`, uniqueness constraints, or status checks).
13. **No N+1.** Eager-load relationships explicitly with `with()` when
    rendering lists.
14. **Vue components are presentation only.** No business rules, money math,
    status transitions, or ownership checks inside components — that logic lives
    server-side and arrives as props.

## Definition of done (per vertical slice)

A feature is delivered as a full vertical slice:

- Migration + model (`$fillable`, casts, relationships — never `$guarded = []`)
  + factory + seeder entry. Fixed sets modelled as Enums.
- FormRequest + Policy (when the resource has an owner) + Service method
  (transactional where it touches money/stock/status), each in its layer.
- Web controller + route behind the correct middleware. Core flows also get an
  `/api/v1` controller with a Swagger/OpenAPI annotation.
- An Inertia page with a deliberate design and real empty / error / loading
  states; responsive; no `v-html` on user content.
- A Pest feature test for any concurrency- or idempotency-critical path.
- `pint` and ESLint/Prettier pass.
- One focused, conventionally-named commit.

## Commit conventions

The commit history is part of the deliverable, so commits are made per vertical
slice and never squashed. Messages follow Conventional Commits:
`type(scope): subject`.

- **Subject:** imperative present tense ("add", not "added"), ≤ 50 chars, no
  trailing period.
- **Types:** `feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `style`,
  `perf`.
- **Scopes:** `auth`, `role`, `store`, `product`, `catalog`, `wallet`, `cart`,
  `checkout`, `discount`, `order`, `delivery`, `admin`, `overdue`, `report`,
  `ui`, `db`, `api`, `docker`, `deploy`, `security`, `docs`.
- **Body (optional):** explain the *why* / impact, not the *what* — the diff
  already shows the what.
- **Granularity:** one logical change per commit. A migration + model + service
  for one feature can share a commit; two unrelated features cannot.

Examples:

- `feat(auth): add register, login, logout with hashing`
- `feat(checkout): charge wallet and reduce stock in a locked transaction`
- `fix(overdue): guard double refund with refunded_at sentinel`
- `test(delivery): cover concurrent take-job rejection`

## Commands (Laravel Sail)

```bash
# Bring the stack up / open a shell
./vendor/bin/sail up -d
./vendor/bin/sail shell

# Reset and seed the database
./vendor/bin/sail artisan migrate:fresh --seed

# Frontend dev / production build
./vendor/bin/sail npm run dev
./vendor/bin/sail npm run build

# Tests
./vendor/bin/sail artisan test

# Format & lint (run before every commit)
./vendor/bin/sail pint
./vendor/bin/sail npm run lint

# Advance the simulated day (drives SLA / overdue handling)
./vendor/bin/sail artisan seapedia:advance-day
```

Production deploys use `docker-compose.prod.yml` (nginx + Caddy), not Sail.

## Testing

Pest drives the test suite, with feature tests concentrated on the paths where
correctness is non-obvious: concurrent claims (checkout stock, driver job
take), idempotent retries (overdue refund, webhooks), money arithmetic
(subtotal → discount → PPN → total), and SLA/overdue transitions under the
simulated clock. Run the full suite in one process — two suites sharing the
MySQL testing database will clobber each other's schema.
