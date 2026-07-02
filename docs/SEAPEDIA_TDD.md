# SEAPEDIA — Technical Design Document (TDD)

> **COMPFEST 18 — Software Engineering Academy Technical Challenge**
> Author: Farros Rifantiarno Ramadhani (El)
> Stack: Laravel 13 (what `laravel new` installs; 12 also fine) · Inertia.js · Vue 3 · Tailwind CSS · MySQL 8 · Docker · iPaymu
> Timeline: **6 days, solo** · Target: **Levels 1–5 guaranteed (80 pts) + Level 6–7 stretch + UI/Deploy bonus**
> Purpose: This document is the single source of truth for the build. It is written to be consumed by **Claude Code**. Every business-rule decision the challenge left open has been **locked here** so the agent never improvises.

---

## 0. How to read this document

This TDD is paired with two operational files:

- **`CLAUDE.md`** (Section 14) — placed at repo root. Project rules Claude Code loads automatically every session.
- **`.claude/skills/*/SKILL.md`** (Section 15) — eight reusable procedure modules: `sprint-planner`, `vertical-feature`, `money-and-checkout`, `order-lifecycle`, `ipaymu-topup`, `security-pass`, `commit-message`, `code-review`. Folder per skill; model-invoked by `description`.
- **`planning/sprint{N}/`** — per-sprint `plan.md` + `progress.md`, written and approved before each sprint's implementation (see §13 and the `sprint-planner` skill).

**Reading order for the agent:** TDD → CLAUDE.md → relevant SKILL.md for the current task → **write the sprint plan to `planning/sprint{N}/plan.md` and pause for approval (§13)** → implement → commit.

The **Locked Decisions** (Section 5) and **Database Schema** (Section 7) are authoritative. If anything elsewhere conflicts with them, they win.

---

## 1. Strategic overview & scope policy

SEAPEDIA is a multi-role marketplace (Admin, Seller, Buyer, Driver) with public catalog, wallet-based checkout, discounts, delivery workflow, admin monitoring, overdue auto-refund, and security hardening, delivered as **one integrated Laravel + Inertia + Vue application** (monolith, API-capable).

### 1.1 Scope tiers (non-negotiable discipline)

| Tier | Levels | Points | Rule |
|---|---|---|---|
| **FLOOR (must ship)** | L1–L5 | 80 | Complete, demoable, deployed. Do NOT start L6 until L5 demo passes end-to-end. |
| **CEILING (stretch)** | L6–L7 | 20 | Only after FLOOR is green and committed. |
| **BONUS** | UI + Deploy | 25 | Deploy is built from Day 1; UI polish is continuous, not a final-day task. |

**The grading reality:** a clean L1–L5 + deployment + good README beats a buggy L1–L7. Architect so the app is internally consistent and demoable at the end of *every* day.

### 1.2 Backend-API requirement

Even though Inertia couples frontend and backend, the challenge requires the backend to be **API-based**. We satisfy this by:
- Building all business logic in **Service classes**, callable independently of Inertia.
- Exposing a parallel **`/api/v1/*` JSON API** (Sanctum-token guarded) that mirrors the core flows, documented via Swagger/OpenAPI. Inertia pages call controllers that call the same services. No logic lives in controllers.

This kills two birds: satisfies "API-based backend" + "clean separation of concerns" grading.

---

## 2. Tech stack & rationale

| Layer | Choice | Why |
|---|---|---|
| Backend | **Laravel 13 (PHP 8.3)** | Eloquent gives SQLi safety for free (Level 7), Policies for RBAC, queue/scheduler for overdue. **Version note:** `laravel new` installs the latest stable, which is Laravel 13 — that's what this build uses. Laravel 12 is equally fine; the major version does **not** affect this architecture at all, so use whatever the installer gives rather than fighting the tooling to pin a version. Don't "upgrade/downgrade" for its own sake. One caveat: if `l5-swagger` doesn't yet support 13, use a Postman collection for API docs instead (the challenge accepts either). |
| Frontend | **Vue 3 (Composition API) + Inertia 2 + TypeScript** | Single deploy unit, no separate API plumbing for the UI; Vue auto-escapes → XSS safety for free. TypeScript is kept **loose** (type props/interfaces; `any` is allowed where it speeds things up — do not fight the compiler under deadline). |
| UI components | **shadcn-vue** (ships with the official Vue starter kit) | Accessible, polished components out of the box → directly serves the 10-pt UI bonus and responsive-layout grading. Add components with `npx shadcn-vue@latest add <name>`. |
| Styling | **Tailwind CSS (v4, via the starter kit)** | Fast, responsive utility-first; shadcn-vue is built on it. |
| DB | **MySQL 8** | Required. Transactions + row locks for concurrency-critical flows. |
| Auth (web) | **Laravel session + Inertia** | Web dashboards. |
| Auth (API) | **Laravel Sanctum (token)** | The documented `/api/v1` surface + Swagger demo. |
| Payments | **iPaymu (v2 redirect)** | Real top-up channel for the Buyer wallet, behind an interface with a dummy fallback. |
| Container | **Docker + docker-compose** | "Works on any machine" + reproducible deploy. |
| API docs | **Swagger/OpenAPI** via `darkaonline/l5-swagger` (or a committed Postman collection) | Required deliverable. |
| Testing | **Pest** (feature tests on critical flows) | Cheap insurance on the race-condition/idempotency logic. |

**Key libraries:** `spatie/laravel-permission` is intentionally **NOT** used — the multi-role + active-role-per-session model is custom and simpler to keep in our own pivot than to bend a package around it. Roles are a small fixed set; a hand-rolled solution is clearer and less risky under deadline.

---

## 2.5 Project structure & code conventions (graded: "clean code + maintainable structure")

### Backend folder layout (create these directories; keep each layer in its place)
```
app/
├── Enums/                 # OrderStatus, DeliveryMethod, WalletTxnType, RoleName — PHP 8 enums, not magic strings
├── Models/                # Eloquent models, singular (User, Store, Product, Order, ...)
├── Policies/              # ProductPolicy, OrderPolicy, DeliveryPolicy — ownership checks
├── Services/              # ALL business logic: CheckoutService, DiscountService, WalletService,
│                          #   OrderService, DeliveryService, OverdueService, ReportService, ClockService
├── Support/               # PaymentGateway interface + IpaymuGateway + FakeGateway; small helpers
├── Http/
│   ├── Controllers/
│   │   ├── Web/           # Inertia controllers (return Inertia::render)
│   │   └── Api/           # JSON controllers for /api/v1 (return resources)
│   ├── Requests/          # FormRequests: StoreProductRequest, UpdateProductRequest, CheckoutRequest, ...
│   ├── Resources/         # API JSON resources (ProductResource, OrderResource, ...)
│   └── Middleware/        # EnsureActiveRole, EnsureAdmin
└── Console/Commands/      # AdvanceSimulatedDay (seapedia:advance-day)

database/{migrations,factories,seeders}
routes/{web.php, api.php, console.php}
tests/Feature/            # Pest: checkout, take-job, overdue, role-access
```

### Frontend layout (Vue + TS, from the starter kit — extend, don't restructure)
```
resources/js/
├── components/ui/         # shadcn-vue (added via CLI — do not edit by hand unless customizing)
├── components/            # project components: Navbar, StatusTimeline, StatCard, EmptyState, ...
├── composables/           # useCart, useActiveRole, ...
├── layouts/               # GuestLayout, DashboardLayout, AuthLayout
├── pages/
│   ├── auth/  buyer/  seller/  driver/  admin/  public/
├── stores/                # Pinia: authStore, cartStore
├── lib/                   # utils, axios/inertia helpers, formatRupiah()
└── types/                 # Product, Order, User, ... shared TS interfaces
```

### Naming conventions
- Models singular (`Product`); tables plural snake (`products`); pivots alphabetical singular (`role_user`).
- Controllers: `ProductController` (resourceful methods index/store/update/destroy).
- Services: `XxxService`, one public method per use case (`CheckoutService::place()`).
- FormRequests: `StoreXxxRequest` / `UpdateXxxRequest`.
- Enums for every fixed set (status, delivery method, txn type, role) — never raw strings in logic.
- Routes kebab-case; route names dotted (`seller.products.store`). Vue components/pages PascalCase.

### Formatting & linting (automated — run before every commit)
- **Backend:** Laravel **Pint** (ships with Laravel; PSR-12) → `./vendor/bin/sail pint`.
- **Frontend:** **ESLint + Prettier** (the Vue starter kit includes them) → `./vendor/bin/sail npm run lint` and `npm run format` (check the kit's `package.json` for exact script names).
- Clean code is enforced by tooling + the `code-review` skill, not by vibes. A slice isn't done until Pint + lint pass.

---

## 3. High-level architecture

```
┌──────────────────────────────────────────────────────────────┐
│                         Browser (Vue 3)                        │
│   Inertia pages · reusable UI kit · Pinia (active-role/cart)   │
└───────────────▲───────────────────────────────┬──────────────┘
                │ Inertia (XHR)                  │ window.location → iPaymu page
                │                                ▼
┌───────────────┴──────────────────────────────────────────────┐
│                       Laravel 13 (monolith)                    │
│                                                                │
│  Routes ──▶ Middleware (auth, EnsureActiveRole) ──▶ Controllers│
│                 │ thin, no logic                               │
│                 ▼                                              │
│  FormRequests (validation)                                     │
│                 ▼                                              │
│  ┌──────────── Services (ALL business logic) ──────────────┐   │
│  │ Auth/RoleService · StoreService · ProductService        │   │
│  │ WalletService · CartService · CheckoutService           │   │
│  │ DiscountService · OrderService · DeliveryService        │   │
│  │ OverdueService · ReportService · ClockService           │   │
│  └────────────────────┬────────────────────────────────────┘   │
│       Policies (RBAC) │ Eloquent models (DB::transaction)       │
│                       ▼                                         │
│  PaymentGateway (interface) ──┬── IpaymuGateway                 │
│                               └── FakeGateway (dummy top-up)    │
│                                                                │
│  Console: SimulateNextDay command · scheduler · webhook ctrl   │
└───────────────┬───────────────────────────────┬───────────────┘
                ▼                                ▼
            MySQL 8                       iPaymu API (v2)
                                          (sandbox / prod)
```

### 3.1 Layering rules
- **Controllers are thin.** They validate (via FormRequest), call one Service method, return Inertia/JSON. No `if`-business-logic in controllers.
- **Services own transactions.** Any operation touching money or stock runs inside `DB::transaction()` with appropriate locks.
- **Policies own authorization** for resource ownership (Seller→product, Buyer→order, Driver→job).
- **Middleware owns role-gating** at route level (`EnsureActiveRole:seller`).
- **`ClockService`** is the *only* source of "now". It returns either real `now()` or the simulated time. Every overdue/expiry check uses it. This makes time-simulation trivial and centralized.

---

## 4. Active-role model (the Level 1 correctness trap)

This is where most submissions get subtly wrong. Read carefully.

### 4.1 Model
- A `User` owns 0..n non-admin roles via `role_user` pivot. Admin is a separate boolean/role and handled apart.
- **Active role is session/token state, NOT a DB column on user.** Storing "current role" on the user record is wrong because the same user can be a Buyer in one tab and a Driver in another session conceptually; active role is *per session*.
  - **Web:** store `active_role` in the session.
  - **API:** the Sanctum token is minted *for a chosen role*; the token's ability/claim carries the active role. A token issued for `buyer` cannot perform `seller` actions even if the user owns the seller role.

### 4.2 Login flow
1. Authenticate credentials.
2. Load owned roles.
3. If user is **Admin** → go to admin dashboard (admin handled separately).
4. If user owns **exactly one** non-admin role → set it active, proceed.
5. If user owns **multiple** non-admin roles → **do NOT redirect to any dashboard.** Return to a **role-selection screen/modal**. Private dashboards stay locked until a role is chosen.

### 4.3 Enforcement
- `EnsureActiveRole` middleware reads active role from session/token and rejects (`403`) if it doesn't match the route's required role.
- **Authorization is based on active role, not the list of owned roles.** A user who owns Seller+Buyer but is active as Buyer cannot hit Seller endpoints.
- Active role is shown clearly in the UI (top bar badge) and switchable via a "Switch role" action that re-runs role selection.
- **Never trust role from the request body / frontend.** The backend resolves active role server-side every request.

---

## 5. Locked business-rule decisions

The challenge leaves these open but demands consistency + documentation. **These are final. Document each in the README.**

### 5.0 Authentication mechanism (LOCKED — answers Level 1 "token, JWT, or session-based")
The spec offers three options ("token, JWT, or session-based") — **one is sufficient**. We choose **Sanctum**, which satisfies the requirement via two layers simultaneously:

| Surface | Mechanism | Why |
|---|---|---|
| **Web / Inertia dashboards** | Laravel **session** (cookie-based, stateful) | Native, zero config, CSRF-protected, pairs with Inertia automatically. |
| **`/api/v1` JSON endpoints** | Sanctum **opaque token** (DB-stored, scoped to active role) | One-line issuance, instant revocation on logout (row deleted from DB — trivially proves Level 7 "logout invalidates token"). |

**JWT is deliberately NOT used.** Reasons: (a) requires `tymon/jwt-auth` package (extra dependency under a 6-day deadline), (b) stateless → logout cannot truly revoke an in-flight token without a blacklist — harder to satisfy Level 7's "logout invalidates token correctly", (c) JWT claim encoding for active-role adds complexity where Sanctum token abilities do the same thing natively.

**Active-role scoping on tokens:** when a user picks an active role and calls `POST /role/select` on the API, we issue a Sanctum token with an ability matching the role (`createToken('session', ['role:buyer'])`). The `EnsureActiveRole` middleware verifies `$token->can('role:buyer')` server-side. The frontend never dictates the role — the token's ability does. Document in README: "Sanctum opaque tokens, one per active-role session, revoked on logout."

### 5.1 Money representation
- All monetary values stored as **integers in rupiah** (no decimals; IDR has no sub-unit in practice here). Avoids float rounding bugs. Column type: `BIGINT UNSIGNED`.

### 5.1b Unified wallet model (LOCKED — answers Level 1 "balance across roles")
- **One wallet per user**, shared across all roles that user owns. Stored balance lives in `wallets.balance` and is shown on the web dashboards.
- All money movement is a row in `wallet_transactions` with a `type`:
  - Buyer: `topup` (credit), `payment` (debit at checkout), `refund` (credit on overdue).
  - Seller: `income` (credit when an order is completed/paid), `reversal` (debit on overdue refund).
  - Driver: `earning` (credit on job completion = 80% of delivery fee).
- A user who owns multiple roles sees **one balance** + a **per-role financial summary** derived by filtering transactions by type. This is exactly what Level 1 asks for ("balance or financial summaries across roles owned by the same username").
- Seller income and driver earnings are **spendable** (marketplace-style instant settlement). This is a deliberate, documented simplification — state it in the README.
- Every wallet mutation goes through `WalletService` inside a transaction with a row lock; `balance_after` is recorded on each transaction for an auditable ledger.

### 5.2 Checkout calculation order (PPN 12%)
```
subtotal        = Σ(item.price_snapshot × item.quantity)
discount_total  = promo_discount + voucher_discount   (capped: ≤ subtotal)
taxable_base    = subtotal − discount_total
tax_amount      = round(taxable_base × 0.12)
delivery_fee    = base_fee(delivery_method) + distance_fee + weight_fee   (see 5.4a)
grand_total     = taxable_base + tax_amount + delivery_fee
```
- **PPN base = discounted subtotal** (discount applied *before* tax). Delivery fee is **not** taxed. This is consistent and must be stated in README.
- Rounding: `round()` half-up at the final tax step only.

### 5.3 Discount rules (Voucher vs Promo)
- **Promo**: campaign-style, `expiry_date`, no per-use limit. `type ∈ {percentage, fixed}`, `value`, optional `max_discount`, optional `min_spend`.
- **Voucher**: `expiry_date` + `usage_limit` + `used_count` (remaining usage = limit − used). Same `type/value/max_discount/min_spend` fields.
- **Combination rule (LOCKED): one Promo AND one Voucher may be combined.** Both compute against `subtotal`. `discount_total = min(promo + voucher, subtotal)`. Promo computed first, then voucher, both off the original subtotal (not stacked sequentially) for predictability.
- Expired Promo/Voucher → rejected. Voucher with `remaining_usage = 0` → rejected. `min_spend` not met → rejected, with a clear message.
- Voucher `used_count` is incremented **inside the checkout transaction**, with a row lock, only on successful order creation. On overdue refund, usage is **not** restored (documented choice — keeps idempotency simple).
- Validation result clearly distinguishes which is a Promo vs Voucher in the checkout summary.

### 5.4 Delivery methods & fees (LOCKED)
| Method | Fee (IDR) | SLA (in day-ticks) |
|---|---|---|
| Instant | 20,000 | 1 tick |
| Next Day | 10,000 | 2 ticks |
| Regular | 5,000 | 4 ticks |

"Day-tick" = one advance of the simulated clock (see 5.7). SLA is measured from order creation; if the order is not `Pesanan Selesai` by `created_sim_day + SLA`, it is overdue.

The `Fee (IDR)` column above is the **base fee per method** (spec line 278 only requires the fee to *differ* per method, not these exact values) and the **per-km rate** below differs per method too. The final `delivery_fee` adds a distance and a weight component (§5.4a).

### 5.4a Distance + weight delivery fee (Sprint 7 decision — spec-legal)
`delivery_fee = base_fee(method) + billable_km × rate_per_km(method) + weight_fee`, computed by `DeliveryFeeService` and applied identically in `preview()` and `commit()` (same address + weight) so the quote equals the charge.

- **Distance** is the Haversine great-circle km between the store's **origin lat/lng** and the buyer's **address lat/lng** (both picked on a Leaflet/OpenStreetMap map — no runtime geocoding at checkout). Billed as `min(ceil(km), 80)` — an 80 km cap keeps long-haul fees bounded while base + rate keep every method distinct even at the cap (spec line 278).
- **Rate per km:** Instant 2,500 / Next Day 1,500 / Regular 1,000.
- **Weight** = Σ(unit weight × qty), where a line uses the variant weight if set, else the product weight. `weight_fee = max(0, ceil((grams − 1000) / 1000)) × 2,000` — the first 1 kg is free, each started kg beyond it costs 2,000.
- **Fallback:** a missing origin or address coordinate bills 0 km; a null weight bills 0 — never surprise-charge on incomplete data. Stores/products without geo/weight fall back to the base fee.
- The whole delivery fee (base + distance + weight) is **not taxed** (§5.2); PPN base is unchanged.
- **Driver distance is NOT part of the fee:** at checkout no driver is assigned yet (§5.6 lifecycle), so the fee can't depend on one, or it couldn't be quoted. Driver proximity only sorts jobs (nearest-first). Driver earning is still 80% of the *total* `delivery_fee` (§5.5).
- The seller's origin used for the fee is fixed on the store profile **before** checkout. A pickup location the seller might set later is a driver label only; it must never change an already-charged fee (preview == charge).

### 5.5 Driver earning rule (LOCKED)
- Driver earns **80% of the order's `delivery_fee`** on `Pesanan Selesai`. Platform keeps 20%. Stored on the delivery row as `earning_amount` at completion. Documented in README.

### 5.6 Order lifecycle (status machine — LOCKED transitions)
User-facing statuses (must never disappear):
```
Sedang Dikemas → Menunggu Pengirim → Sedang Dikirim → Pesanan Selesai
                                                     ↘ (overdue) Dikembalikan
```
Valid transitions ONLY:
| From | To | Trigger | Guard |
|---|---|---|---|
| (create) | Sedang Dikemas | checkout success | wallet paid, stock reduced |
| Sedang Dikemas | Menunggu Pengirim | Seller processes | seller owns order |
| Menunggu Pengirim | Sedang Dikirim | Driver takes job | job unclaimed (locked) |
| Sedang Dikirim | Pesanan Selesai | Driver confirms | driver owns job |
| Sedang Dikemas / Menunggu Pengirim / Sedang Dikirim | Dikembalikan | overdue handler | order paid, not already refunded |

- **Every transition writes an `order_status_histories` row** `{status, note, changed_by, created_at}` using the simulated clock where relevant.
- An invalid transition throws a domain exception (returns 422). No "silent" status changes.

### 5.7 Time simulation (LOCKED mechanism)
- A single `settings` row holds `simulated_now` (datetime). `ClockService::now()` returns it (or real now if unset).
- **Admin trigger** `POST /admin/clock/advance` advances by 1 tick (configurable as "1 day") and immediately runs `OverdueService::sweep()`.
- Also exposed as artisan: `php artisan seapedia:advance-day` (so it works via cron/worker/command — covers the spec's "scheduler, cron, worker, command, or manual Admin trigger").
- Overdue sweep is **idempotent**: only processes orders whose `(SLA deadline < simulated_now)` AND status ∈ {not-final} AND `refunded_at IS NULL`.

### 5.8 Cart rule (LOCKED)
- One active cart per Buyer. Cart has nullable `store_id`. First item sets the store. Adding an item from a different store → **422 with a clear message** ("Cart contains items from {store}. Clear cart to add from another store?"). Frontend offers a "Clear & add" action.

### 5.9 Overdue refund effects (LOCKED, idempotent)
On a single overdue order transition to `Dikembalikan`, inside ONE transaction:
1. Lock the order row. If `refunded_at IS NOT NULL` → abort (no double refund).
2. Credit Buyer wallet by `grand_total`; write `wallet_transactions{type: refund, ...}`.
3. Reverse Seller income: write a `wallet_transactions` reversal on the Seller wallet (or a flagged adjustment in the income report) for the order's seller-credited amount.
4. Restore stock for each `order_item` (`product.stock += quantity`).
5. Set `order.refunded_at = ClockService::now()`, status → `Dikembalikan`, write status history.
- Guards prevent double refund / double income reversal / double stock restore via the `refunded_at` sentinel + row lock.

---

## 6. Concurrency & integrity (where correctness points live)

| Risk | Mitigation |
|---|---|
| Two checkouts oversell stock | `DB::transaction` + `Product::lockForUpdate()` per item; re-check `stock ≥ qty` after lock; reject if not (no negative stock). |
| Two Drivers take same job | `DB::transaction` + `lockForUpdate` on delivery row; only assign if `driver_id IS NULL`; else 409. |
| Double refund on overdue | `refunded_at` sentinel + row lock (5.9). |
| Voucher used past limit under race | `lockForUpdate` on voucher row inside checkout txn; re-check remaining. |
| Wallet balance race on top-up + spend | All wallet mutations go through `WalletService` with row lock; never read-modify-write outside a transaction. |
| Webhook replay (iPaymu notifies twice) | Top-up records keyed by gateway reference; mark `processed_at`; ignore if already processed. |

**Rule for the agent:** any method that changes `balance`, `stock`, `used_count`, or an order `status` must be wrapped in a transaction with the relevant `lockForUpdate()`.

---

## 7. Database schema

Conventions: all tables have `id` (bigint PK), `created_at`, `updated_at`. Money = `BIGINT UNSIGNED` (IDR). FKs indexed.

### users / roles
```
users
  id, name, username (UNIQUE), email (UNIQUE), phone, password,
  is_admin (bool, default false)

roles
  id, name (UNIQUE)   -- seed: buyer, seller, driver  (admin via users.is_admin)

role_user  (pivot)
  user_id (FK), role_id (FK), UNIQUE(user_id, role_id)
```

### stores / products
```
stores
  id, user_id (FK, UNIQUE), name (UNIQUE), slug (UNIQUE), description, is_active

products
  id, store_id (FK), name, slug, description, price (money),
  stock (int unsigned), image_path (nullable), is_active (bool)
  INDEX(store_id), INDEX(is_active)
```

### wallet / addresses
```
wallets
  id, user_id (FK, UNIQUE), balance (money, default 0)

wallet_transactions
  id, wallet_id (FK), type ENUM(topup, payment, refund, income, earning, reversal, adjustment),
  amount (money, signed via direction flag or separate debit/credit — store amount + direction),
  direction ENUM(credit, debit),
  balance_after (money),
  reference_type (nullable, e.g. order/topup), reference_id (nullable),
  description, created_at
  INDEX(wallet_id, created_at)

addresses
  id, user_id (FK), recipient_name, phone, full_address, is_default (bool)
  INDEX(user_id)
```

### cart
```
carts
  id, user_id (FK, UNIQUE), store_id (FK, nullable)

cart_items
  id, cart_id (FK), product_id (FK), quantity (int), price_snapshot (money)
  UNIQUE(cart_id, product_id)
```

### discounts
```
promos
  id, code (UNIQUE), type ENUM(percentage, fixed), value (int),
  max_discount (money, nullable), min_spend (money, nullable),
  expiry_date (datetime), is_active (bool)

vouchers
  id, code (UNIQUE), type ENUM(percentage, fixed), value (int),
  max_discount (money, nullable), min_spend (money, nullable),
  expiry_date (datetime), usage_limit (int), used_count (int default 0), is_active (bool)
```

### orders
```
orders
  id, code (UNIQUE),  buyer_id (FK), store_id (FK),
  -- address snapshot:
  ship_recipient, ship_phone, ship_address,
  delivery_method ENUM(instant, next_day, regular),
  subtotal (money), discount_total (money),
  promo_id (FK nullable), voucher_id (FK nullable),
  delivery_fee (money), tax_amount (money), grand_total (money),
  status ENUM(sedang_dikemas, menunggu_pengirim, sedang_dikirim, pesanan_selesai, dikembalikan),
  created_sim_at (datetime),    -- simulated time at creation, for SLA
  sla_due_at (datetime),        -- precomputed deadline (created_sim_at + SLA)
  paid_at (datetime nullable),
  refunded_at (datetime nullable),   -- idempotency sentinel
  seller_income_amount (money)       -- amount credited to seller (for reversal)
  INDEX(buyer_id), INDEX(store_id), INDEX(status), INDEX(sla_due_at)

order_items
  id, order_id (FK), product_id (FK),
  product_name_snapshot, price_snapshot (money), quantity (int), line_subtotal (money)

order_status_histories
  id, order_id (FK), status (string), note (nullable),
  changed_by (FK users, nullable), created_at
  INDEX(order_id, created_at)
```

### delivery
```
deliveries
  id, order_id (FK, UNIQUE), driver_id (FK nullable),
  status ENUM(available, taken, completed),
  taken_at (datetime nullable), completed_at (datetime nullable),
  earning_amount (money, default 0)
  INDEX(status), INDEX(driver_id)
```

### topups / payments (iPaymu)
```
topups
  id, wallet_id (FK), amount (money),
  status ENUM(pending, paid, failed, expired),
  gateway ENUM(ipaymu, fake),
  gateway_session_id (nullable), gateway_reference (nullable, UNIQUE),
  raw_response (json nullable), processed_at (datetime nullable)
  INDEX(status)
```

### app reviews / settings
```
app_reviews
  id, user_id (FK nullable),  reviewer_name, rating (tinyint 1..5), comment (text), created_at
  INDEX(created_at)

settings
  id, key (UNIQUE), value (text)   -- holds simulated_now, etc.
```

**ER summary:** `users 1—n role_user n—1 roles`; `users 1—1 stores 1—n products`; `users 1—1 wallets 1—n wallet_transactions`; `users 1—1 carts 1—n cart_items n—1 products`; `users(buyer) 1—n orders 1—n order_items`; `orders 1—n order_status_histories`; `orders 1—1 deliveries n—1 users(driver)`; `wallets 1—n topups`.

---

## 8. API surface (representative — full set in Swagger)

Two parallel surfaces share the same Services. Web routes use Inertia; `/api/v1` uses Sanctum + JSON (documented).

### Public
```
GET    /                         landing (reviews, featured)
GET    /products                 catalog (paginated, search)
GET    /products/{slug}          product detail (+ store block)
GET    /stores/{slug}            store detail
GET    /reviews                  list app reviews
POST   /reviews                  submit app review (guest allowed)
```

### Auth
```
POST   /register
POST   /login                    → returns owned roles
POST   /role/select              set active role (web session) / mint role-scoped token (api)
POST   /logout                   invalidate session/token
GET    /me                       current user + owned roles + active role + balances summary
```

### Seller (active role: seller)
```
POST/PUT  /seller/store
GET       /seller/products
POST      /seller/products
PUT       /seller/products/{id}
DELETE    /seller/products/{id}
GET       /seller/orders                incoming orders
POST      /seller/orders/{id}/process   Sedang Dikemas → Menunggu Pengirim
GET       /seller/reports               income summary
```

### Buyer (active role: buyer)
```
GET    /buyer/wallet
POST   /buyer/wallet/topup              create top-up (iPaymu or fake)
GET    /buyer/addresses  POST  /buyer/addresses  ...
GET    /buyer/cart
POST   /buyer/cart/items                add (single-store guard)
PUT    /buyer/cart/items/{id}           qty
DELETE /buyer/cart/items/{id}
POST   /buyer/cart/clear
POST   /buyer/checkout/preview          returns full summary (subtotal/discount/tax/fee/total)
POST   /buyer/checkout                  create order (transactional)
GET    /buyer/orders   GET /buyer/orders/{id}
GET    /buyer/reports                   spending summary
```

### Driver (active role: driver)
```
GET    /driver/jobs                     available (status menunggu_pengirim)
GET    /driver/jobs/{id}
POST   /driver/jobs/{id}/take           → Sedang Dikirim (locked)
POST   /driver/jobs/{id}/complete       → Pesanan Selesai (+ earning)
GET    /driver/dashboard                active + history + earnings
```

### Admin (is_admin)
```
GET    /admin/dashboard                 monitoring: users/stores/products/orders/discounts/deliveries/overdue
GET/POST /admin/vouchers   GET /admin/vouchers/{id}
GET/POST /admin/promos     GET /admin/promos/{id}
POST   /admin/clock/advance             advance simulated day + sweep overdue
```

### Webhook
```
POST   /webhook/ipaymu                  unotify handler (no auth, signature/reference validated, idempotent)
```

---

## 9. iPaymu integration design

**Verified facts (iPaymu API v2, redirect):**
- Endpoints: sandbox `https://sandbox.ipaymu.com/api/v2/payment`, prod `https://my.ipaymu.com/api/v2/payment`.
- Headers: `Content-Type: application/json`, `va: <VA>`, `signature: <sig>`, `timestamp: YmdHis`.
- Signature: `stringToSign = "POST:" + VA + ":" + lowercase(sha256(jsonBody)) + ":" + apiKey`; `signature = hmac_sha256(stringToSign, apiKey)`.
- Body (redirect): `product[]`, `qty[]`, `price[]`, `returnUrl`, `cancelUrl`, `notifyUrl`, `buyerName`, `buyerEmail`, `buyerPhone`, `referenceId`.
- Success response: `Status: 200`, `Data.SessionID`, `Data.Url` (redirect Buyer here).
- Notification: iPaymu POSTs to `notifyUrl` (the webhook) on payment result.

### 9.1 Where iPaymu fits
iPaymu is **only** the Buyer wallet top-up channel. It does **not** touch checkout (checkout always pays from wallet). This isolation means a broken gateway never blocks the core 20-pt checkout flow.

### 9.2 Interface + fallback (mandatory)
```php
interface PaymentGateway {
    public function createTopup(Topup $topup): array; // returns ['redirect_url' => ...] or marks paid
}
```
- `IpaymuGateway` — real v2 redirect call (signature above). Returns `Data.Url`; Buyer is redirected.
- `FakeGateway` — instantly marks the top-up `paid` and credits the wallet (used in local/dev/demo when `PAYMENT_GATEWAY=fake`).
- Selected by env: `PAYMENT_GATEWAY=ipaymu|fake`. **Seed/demo accounts use `fake` so the demo never depends on iPaymu uptime.** iPaymu is demonstrated as one scenario, not the critical path.

### 9.3 Top-up flow
1. Buyer requests top-up amount → create `topups{status: pending}` with a unique `gateway_reference` (= our reference id).
2. If `ipaymu`: call API, store `gateway_session_id`, redirect to `Data.Url`. If `fake`: credit immediately, done.
3. iPaymu redirects Buyer back to `returnUrl` (a "processing" page that polls top-up status).
4. iPaymu POSTs `notifyUrl` → **webhook controller**:
   - Validate the notification maps to a known pending top-up by reference.
   - **Idempotency:** if `processed_at` set → 200 and return (replay-safe).
   - On success: inside a transaction, credit wallet via `WalletService`, write `wallet_transactions{type: topup}`, set `topups.status = paid`, `processed_at = now()`.
5. The webhook URL **requires the deployed public HTTPS URL** → another reason deployment is Day-1 work.

### 9.4 Config (`.env`)
```
PAYMENT_GATEWAY=fake          # fake for demo; ipaymu to exercise real flow
IPAYMU_VA=
IPAYMU_API_KEY=
IPAYMU_MODE=sandbox           # sandbox | production
IPAYMU_RETURN_URL=${APP_URL}/buyer/wallet/topup/return
IPAYMU_CANCEL_URL=${APP_URL}/buyer/wallet/topup/cancel
IPAYMU_NOTIFY_URL=${APP_URL}/webhook/ipaymu
```

---

## 10. Security checklist (Level 7 — mostly verification, not new work)

| Item | How it's covered | Verify |
|---|---|---|
| SQL Injection | Eloquent / query builder only; **no raw concatenated SQL** anywhere. | grep for `DB::raw`/`whereRaw` with interpolation → must be none, or parameter-bound. |
| XSS | Vue auto-escapes `{{ }}`; **never use `v-html`** on user content (reviews especially). | grep `v-html` → must not wrap user input. |
| Input validation | Every write goes through a **FormRequest** validating email, phone, rating(1–5), quantity≥1, price≥0, stock≥0, discount values. | each controller write has a FormRequest. |
| Clear error rejection | FormRequest messages + domain exceptions → 422 with messages. | demo invalid inputs. |
| Mass assignment | `$fillable` whitelists on all models; no `$guarded = []`. | model review. |
| AuthZ (ownership) | Policies: Seller→own product/store, Buyer→own order/cart/wallet, Driver→own job. | demo cross-user access → 403. |
| Active-role server-side | `EnsureActiveRole` middleware; API token scoped to role. | hit seller route as buyer → 403. |
| Route protection | No private route reachable by manual URL without passing middleware. | manual URL test. |
| Admin isolation | `is_admin` gate on all `/admin/*`. | non-admin → 403. |
| Session/token expiry | Sanctum token expiration set + documented; logout invalidates. | logout → token rejected. |
| Webhook safety | Reference validation + idempotency; no trust of unauthenticated body to mutate beyond mapped top-up. | replay test. |
| Password storage | bcrypt (Laravel default `Hash::make`). | — |
| CSRF | Laravel web middleware (Inertia handles token). | — |

**Security test cases to demo (from spec):**
1. `<script>alert(1)</script>` in a review comment → rendered as inert text.
2. `' OR 1=1 --` into login/search/review/checkout → no effect (Eloquent binds params).

---

## 11. Reusable UI foundation (Level 1, 4 pts + UI bonus)

**Use shadcn-vue primitives; don't hand-roll what it already provides.** The starter kit ships a base set; add more with `npx shadcn-vue@latest add button input textarea select card badge dialog table` etc.

> **Install before use (important):** shadcn-vue is not a node_modules library — `add` copies real component files into `resources/js/components/ui/`. A component does not exist until you've added it. Never import one you haven't installed first. List the sprint's needed components in `plan.md`, run `add` as the first UI step, verify the files exist, then import. (CLAUDE.md rule 12.)

- **From shadcn-vue (add as needed):** Button, Input, Textarea, Select, Card, Badge, Dialog (modal), Table, Pagination, Toast/Sonner (for cart single-store conflict + errors), Tabs, Avatar.
- **Project wrappers to build on top (thin):** `Navbar` (guest vs authed variants), `Footer`, `BottomNav` (mobile), `StatusTimeline` (order lifecycle), `EmptyState`, `StatCard`. These compose shadcn primitives — keep them small.
- **Layouts:** `GuestLayout`, `DashboardLayout` (role-aware sidebar — the starter kit's app layout is a good base), `AuthLayout` (from the kit).
- **Active-role badge** in the top bar + role switcher.
- **Responsive:** desktop sidebar / mobile bottom-nav. Test at 360px, 768px, 1280px.
- **State (Pinia):** `authStore` (user, roles, activeRole), `cartStore`. Keep it minimal — Inertia props are the source of truth; Pinia only for cross-page UI state. (Add Pinia: `npm i pinia` if the kit doesn't include it.)
- **TypeScript:** type Inertia page props with interfaces (e.g. `defineProps<{ products: Product[] }>()`); define shared types in `resources/js/types/`. Loose is fine — `any` where it unblocks you.

Design direction: clean marketplace feel (Tokopedia/Shopee-inspired but original). One accent color, generous spacing, real empty/error/loading states — these are what the UI bonus rewards. shadcn-vue gives you the polish cheaply; spend your time on layout and states, not reinventing buttons. (Consult the `frontend-design` skill before customizing the look.)

---

## 12. Seed & demo data (Level 7 deliverable + your own sanity)

`DatabaseSeeder` must produce a fully demoable world:
- **Admin:** `admin / password` (`is_admin = true`).
- **Seller:** `seller1` (owns "Toko Berkah", 6 products with varied stock). A **multi-role** user `multi1` owning buyer+seller+driver to demo role selection.
- **Buyer:** `buyer1` with wallet pre-funded (via fake top-up) + a default address.
- **Driver:** `driver1`.
- **Discounts:** 1 active voucher (`HEMAT10`, 10%, limit 5), 1 expired voucher, 1 active promo (`PROMO20K`, fixed 20,000), 1 expired promo.
- **Orders:** one order seeded in each status, including one already past its SLA so the overdue sweep has something to act on in the demo.
- README documents every credential and the exact demo path.

---

## 12.5 Project setup (Day 1, first hour — do this exactly)

Goal: a running Laravel 13 (or 12) + Inertia + Vue + Tailwind app inside Docker, with auth scaffolding already done, so Day 1 spends time on multi-role logic, not boilerplate.

### A. Scaffold + auth (official Vue starter kit — TS + shadcn-vue)

**Dev environment: Sail (Docker).** Chosen deliberately for dev=prod parity — you develop in Docker and deploy to Docker, so "works on my machine" surprises are eliminated. Prerequisite: Docker Desktop running. You also need PHP 8.3 + Composer locally just to run the installer once (or use the Docker scaffold path noted below).

The official Vue starter kit ships Inertia + Vue 3 (Composition API) + **TypeScript** + Tailwind 4 + **shadcn-vue** + built-in auth (login, register, password reset, email verification). This is the chosen base.

```bash
# Scaffold via the Laravel installer (needs local PHP 8.3 + Composer)
# NOTE: `laravel new` installs the LATEST stable — Laravel 13 right now. That's fine; 12 and 13 are
# identical for this build. Don't fight the installer to pin a version.
composer global require laravel/installer
laravel new seapedia
#   → Starter kit:     Vue
#   → Authentication:  Laravel's built-in   (NOT WorkOS — avoid the external dependency)
#   → Teams:           no
#   → Testing:         Pest
#   → Laravel Boost:   yes   (gives Claude Code version-accurate Laravel docs)
cd seapedia

# Add Sail with a containerized MySQL, then bring the stack up
php artisan sail:install --with=mysql
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate          # built-in auth tables (verify .env DB_CONNECTION=mysql, DB_HOST=mysql)

# API token layer for the documented /api/v1 surface + Swagger
./vendor/bin/sail composer require laravel/sanctum
./vendor/bin/sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# API docs + Pinia for cross-page UI state
# If l5-swagger refuses to install on Laravel 13, skip it and use a Postman collection instead.
./vendor/bin/sail composer require darkaonline/l5-swagger
./vendor/bin/sail npm install
./vendor/bin/sail npm install pinia

# Front-end dev server (run in its own terminal)
./vendor/bin/sail npm run dev
```

> **No local PHP?** Scaffold plain Laravel via Docker — `curl -s "https://laravel.build/seapedia?with=mysql" | bash` — then `cd seapedia && ./vendor/bin/sail up -d`, and add the Vue starter kit manually per the Laravel starter-kit docs. The installer path above is simpler if you have PHP 8.3 locally.

Add shadcn-vue components on demand, e.g.:
```bash
./vendor/bin/sail npx shadcn-vue@latest add button input card dialog table badge sonner
```

Out of the box you get working login/register/logout + a TS Vue/Inertia/Tailwind app shell with shadcn-vue — that covers Level 1's basic-auth requirement with zero hand-written code. Build the multi-role / active-role layer (TDD §4) on top of it.

**TypeScript note for the agent:** keep TS loose. Type Inertia page props and shared models (`resources/js/types/`); use `any` freely where it unblocks progress. Do not introduce strict generics or fight the compiler — shipping features beats type purity under a 6-day deadline.

### B. Production image for deployment (separate from Sail)
Sail is for dev; the GCP box needs a lean production setup. Create at repo root:
- `Dockerfile` — multi-stage: stage 1 `node:20` builds Vite assets (`npm ci && npm run build`); stage 2 `php:8.3-fpm` (MUST match the dev PHP version — pin extensions: pdo_mysql, mbstring, bcmath, intl) with composer install `--no-dev --optimize-autoloader`, copies built assets.
- `docker-compose.prod.yml` — services `app` (php-fpm), `nginx` (serves `public/` + proxies php-fpm), `mysql`. Optional `caddy` as the HTTPS edge.
- `.dockerignore`, `nginx.conf`.

**Decision:** commit both `docker-compose.yml` (Sail, dev) and `docker-compose.prod.yml` (deploy). README documents both. "Works on any machine" = `sail up` for devs; the prod compose for the evaluator-facing deploy.

### C. First commit + first deploy (still Day 1)
```bash
git init && git add -A
git commit -m "chore: scaffold laravel 12 + inertia vue + tailwind + sail"
# push to GitHub (public repo), then on the GCP e2-micro box:
#   git clone ... && docker compose -f docker-compose.prod.yml up -d --build
#   migrate --force --seed
```
The deployed URL must serve the app **before you write feature #2**. (iPaymu webhook needs it later anyway.)

---

## 13. Six-day milestone plan (sprints)

> Principle: **vertical slices**, deploy from Day 1, commit per slice, demo passes at end of each day. iPaymu real-integration is slotted late and optional.
>
> **Planning gate (every sprint):** before writing any feature code for sprint N, Claude Code first writes `planning/sprint{N}/plan.md` (per the `sprint-planner` skill) and **pauses for approval**. Implementation begins only after the plan is approved. `planning/sprint{N}/progress.md` is updated as tasks are committed. This makes the build reviewable and keeps each day scoped. (Sprint N ↔ Day N below; Day 5 carries two levels, so it gets one plan covering both.)

### Day 1 — Infra + Foundation + Level 1 (auth, multi-role, reviews, UI kit)
- [ ] **Write `planning/sprint1/plan.md` first; pause for approval** (sprint-planner skill). Then implement.
- [ ] Scaffold per §12.5: `laravel new seapedia` (Vue starter kit, built-in auth, Pest) → Sail with MySQL → Sanctum + l5-swagger + Pinia. App boots; login/register work out of the box.
- [ ] **Deploy skeleton live** to GCP e2-micro (production Docker compose + nginx + HTTPS). "Hello SEAPEDIA" reachable on a public URL. *This is the single highest-leverage thing you can do today.*
- [ ] Migrations: users, roles, role_user, app_reviews, settings, wallets (entry point).
- [ ] Auth: extend built-in auth (register, login, logout already there); add Sanctum token issuance for `/api/v1`.
- [ ] Multi-role: owned roles, role-selection modal, `EnsureActiveRole`, `/me`, active-role badge, balances **placeholder**.
- [ ] Reviews: public form + list (guest allowed), rendered as text.
- [ ] UI kit + layouts + responsive nav + role-aware dashboard shells (Admin/Seller/Buyer/Driver placeholders).
- [ ] Landing, catalog (dummy data ok), product detail, login/register pages.
- **End-of-day demo:** guest browses, submits review; user registers, picks role, sees correct dashboard; deployed URL works.
- **Commits:** per feature (`feat(auth): register`, `feat(role): active-role middleware`, ...).

### Day 2 — Level 2 (Seller store + products + real catalog)
- [ ] **Write `planning/sprint2/plan.md` first; pause for approval**, then implement.
- [ ] Migrations: stores, products.
- [ ] Store create/update with **unique name** validation (DB unique + FormRequest).
- [ ] Product CRUD (Seller), `ProductPolicy` (own-only).
- [ ] Seller dashboard product list.
- [ ] Public catalog + detail now read from DB; store info block; store detail page.
- **Demo:** seller creates store + products → appear in public catalog; cross-seller edit → 403.

### Day 3 — Level 3 (Wallet + Cart + Checkout) — HEAVIEST DAY (20 pts)
- [ ] **Write `planning/sprint3/plan.md` first; pause for approval**, then implement.
- [ ] Migrations: wallet_transactions, addresses, carts, cart_items, orders, order_items, order_status_histories, topups.
- [ ] `WalletService` (locked mutations) + **fake top-up** + transaction history + addresses.
- [ ] `CartService` with **single-store guard** (422 + clear message) + cart UI.
- [ ] `CheckoutService`: preview (subtotal/discount-placeholder/fee/PPN12%/total) + commit inside `DB::transaction` with `lockForUpdate` on products; reduce stock (no negative); charge wallet; create order `Sedang Dikemas`; status history; Seller incoming-order list.
- **Demo:** top-up → add to cart (single-store enforced) → checkout → wallet charged, stock reduced, order visible to buyer & seller, summary shows PPN 12%.
- **This is where race-condition correctness is won. Write a Pest test for oversell + insufficient-balance.**

### Day 4 — Level 4 (Discounts + Seller processing + Reports)
- [ ] **Write `planning/sprint4/plan.md` first; pause for approval**, then implement.
- [ ] Migrations: promos, vouchers. Admin endpoints to generate (UI deferred to L6).
- [ ] `DiscountService`: validate promo/voucher (expiry, remaining usage, min_spend), apply per locked rule (5.3), distinguish in summary; integrate into checkout preview + commit (lock voucher row, increment used_count).
- [ ] Seller `process` action: `Sedang Dikemas → Menunggu Pengirim` + history + timeline UI on buyer & seller.
- [ ] Buyer spending report + Seller income report (discount/fee/PPN/total visible).
- **Demo:** checkout with HEMAT10 + PROMO20K stacked; expired code rejected; seller processes order; reports reconcile.

### Day 5 — Level 5 (Driver) + Level 6 (Admin + Overdue) — the heavy stretch
- [ ] **Write `planning/sprint5/plan.md` first; pause for approval**, then implement.
- [ ] Migrations: deliveries. Auto-create delivery row when seller processes order (status `available`).
- [ ] Driver find/take(locked, → Sedang Dikirim)/complete(→ Pesanan Selesai, earning 80% fee) + driver dashboard + buyer/seller tracking.
- [ ] `ClockService` + `settings.simulated_now` + `seapedia:advance-day` + admin `/clock/advance`.
- [ ] `OverdueService::sweep()` — idempotent refund/return per 5.9 (wallet credit, seller reversal, stock restore, status `Dikembalikan`, history, `refunded_at` guard).
- [ ] Admin monitoring dashboard (users/stores/products/orders/discounts/deliveries/overdue) + Voucher/Promo management UI.
- **Demo:** full chain buyer→seller→driver→completed; then advance day on an overdue order → auto-refund verifiable in wallet history + status.
- **Write a Pest test for double-take prevention + double-refund prevention.**

### Day 6 — Level 7 (Security) + Docs + Deploy polish + Demo recording + BUFFER
- [ ] **Write `planning/sprint6/plan.md` first; pause for approval**, then implement.
- [ ] Security audit pass against Section 10 (grep `v-html`, `whereRaw`, `$guarded=[]`; confirm policies + middleware on every private route; token expiry).
- [ ] XSS + SQLi test cases demonstrated.
- [ ] Swagger/OpenAPI (or Postman collection committed).
- [ ] README: setup, env vars, admin setup, single-store rule, discount+PPN rule, driver earning, overdue SLA + time-sim, security notes, deploy URL, demo testing guide.
- [ ] Final seed verify; **(optional) wire real iPaymu sandbox** end-to-end against the live URL — only if buffer remains; otherwise ship with `fake` + documented iPaymu design.
- [ ] Final deploy + record end-to-end demo video.
- **Buffer is sacred. If a day slipped, this is where you absorb it — by cutting CEILING, never the FLOOR.**

### Cut order if you fall behind (decide fast, don't agonize)
1. Drop **real iPaymu** integration → keep `fake` + documented design. (Lose ~0 core pts.)
2. Drop **Level 7 hardening extras** beyond what the stack gives free. (Lose up to 10.)
3. Drop **Level 6 overdue/admin**. (Lose up to 10.)
4. Never drop below a clean, deployed **Level 5**.

---

## 14. `CLAUDE.md` (place at repo root)

````markdown
# SEAPEDIA — Claude Code Operating Rules

You are building SEAPEDIA, a multi-role marketplace, per `SEAPEDIA_TDD.md`.
Read the TDD's Locked Decisions (§5) and Schema (§7) before any task. They are authoritative.

## Stack
Laravel 13 (PHP 8.3) · Inertia · Vue 3 + TypeScript · shadcn-vue · Tailwind 4 · MySQL 8 · Docker · Sanctum · Pest · iPaymu (v2). TypeScript is kept loose (`any` allowed where it unblocks).

## Plan before you build (MANDATORY)
0. **Do not write feature code for a sprint until you have written its plan and I have approved it.** At the start of each sprint:
   - Read the relevant TDD sections + the `sprint-planner` skill.
   - Write the plan to **`planning/sprint{N}/plan.md`** (structure defined in the `sprint-planner` skill).
   - **STOP and wait for my approval.** Do not begin implementation in the same turn.
   - During the sprint, keep `planning/sprint{N}/progress.md` updated (check off tasks as committed).
   - The `planning/` folder is committed (`docs(planning): add sprint{N} plan`).

## Golden rules
1. **Controllers are thin.** Validate via FormRequest → call ONE Service method → return Inertia/JSON. No business logic in controllers.
2. **All business logic lives in Services** (`app/Services`). Services are framework-agnostic and reused by both web and `/api/v1`.
3. **Any mutation of balance / stock / used_count / order status MUST run in `DB::transaction()` with `lockForUpdate()`** on the affected rows.
4. **Authorization via Policies** for ownership; **`EnsureActiveRole` middleware** for role-gating. Never trust role from request body. Active role is resolved server-side.
5. **Money is integer IDR** (`BIGINT UNSIGNED`). No floats.
6. **Time comes ONLY from `ClockService::now()`** — never `now()`/`Carbon::now()` directly in business logic.
7. **Order status changes only via `OrderService` using the locked transition table (§5.6).** No status string set directly on the model elsewhere. Every change writes an `order_status_histories` row.
8. **Eloquent only.** No `DB::raw`/`whereRaw` with interpolated input. **Never `v-html` on user-generated content.**
9. **Every write endpoint has a FormRequest** validating per §10.
10. **iPaymu sits behind the `PaymentGateway` interface** with a `FakeGateway` fallback. Checkout never calls a gateway — it pays from the wallet only.
11. **Use shadcn-vue components**; don't hand-roll buttons/inputs/dialogs/tables it provides. Type Inertia props with interfaces; loose TS is fine.
12. **shadcn-vue: install before use.** A shadcn-vue component only exists after `npx shadcn-vue@latest add <name>` copies its files into `resources/js/components/ui/`. NEVER import a shadcn-vue component you have not added first. At the start of a sprint: (a) list the components the sprint needs in `plan.md`, (b) run the `add` command(s) as the first UI step, (c) confirm the files exist (`resources/js/components/ui/<name>/`), (d) only then import and use them. If a needed component isn't installed, run `add` — do not write a substitute or assume it's there.
13. **Follow the project structure (§2.5).** Each layer in its folder: logic in `app/Services`, ownership in `app/Policies`, validation in `app/Http/Requests`, fixed sets as `app/Enums` (no magic strings), web vs API controllers separated. Models singular, FormRequests `Store/Update...Request`, services `XxxService`.
14. **Format before every commit.** Run `./vendor/bin/sail pint` (PHP) and the kit's ESLint/Prettier (`npm run lint`/`format`) before committing. A slice is not done until both pass. Functions stay focused (~≤30 lines); no business logic in controllers or Vue components.

## Definition of Done (per vertical slice)
- Migration + model (`$fillable`) + factory + seeder entry. Fixed sets use Enums (§2.5).
- FormRequest + Policy (if ownership) + Service method (transactional where needed). Files placed per §2.5 structure.
- Controller (web) + route behind correct middleware. Add `/api/v1` + Swagger annotation for core flows.
- Inertia page + reuses the UI kit; responsive; has empty/error/loading states.
- A Pest feature test for any concurrency/idempotency-critical path.
- **`pint` + ESLint/Prettier pass.**
- One focused commit. Conventional message: `feat(scope): ...`, `fix(scope): ...`.

## Commit discipline (GRADED — the evaluator reads the history)
Commit per vertical slice, never squash. Use Conventional Commits: `type(scope): subject`.
- **Subject:** imperative present tense ("add", not "added"), ≤ 50 chars, no trailing period.
- **Types:** `feat` (user-facing feature), `fix` (bug a user could hit), `refactor` (no behavior change), `test`, `docs`, `chore` (tooling/config/deps), `style`, `perf`.
- **Scopes for THIS project:** `auth`, `role`, `store`, `product`, `catalog`, `wallet`, `cart`, `checkout`, `discount`, `order`, `delivery`, `admin`, `overdue`, `report`, `ui`, `db`, `api`, `docker`, `deploy`, `security`, `docs`.
- **Body (optional):** explain the WHY/impact, not the WHAT (the diff shows what).
- **Granularity:** one logical change per commit. A migration+model+service for one feature can be one commit; mixing two unrelated features in one commit is wrong.
- Commit in the development order of TDD §13 so the history reads like the build progression.
- Examples:
  - `feat(auth): add register, login, logout with hashing`
  - `feat(role): enforce active-role via EnsureActiveRole middleware`
  - `feat(checkout): charge wallet and reduce stock in a locked transaction`
  - `fix(overdue): guard double refund with refunded_at sentinel`
  - `test(delivery): cover concurrent take-job rejection`
  - `chore(docker): add production compose with nginx + caddy`
- The full convention also lives in the `commit-message` skill (§15.6).

## Commands (local dev uses Laravel Sail)
- Up: `./vendor/bin/sail up -d`  ·  Shell: `./vendor/bin/sail shell`
- Migrate+seed: `./vendor/bin/sail artisan migrate:fresh --seed`
- Dev assets: `./vendor/bin/sail npm run dev`  ·  Build: `./vendor/bin/sail npm run build`
- Tests: `./vendor/bin/sail artisan test`
- Format (run before commit): `./vendor/bin/sail pint` · `./vendor/bin/sail npm run lint`
- Advance simulated day: `./vendor/bin/sail artisan seapedia:advance-day`
- Production deploy uses `docker-compose.prod.yml` (see §12.5 / §16), not Sail.

## Do NOT
- Do not gold-plate or invent features beyond the claimed level.
- Do not add libraries without a concrete need stated in the TDD.
- Do not skip seeders or the README.
- Do not leave deployment or docs to the end — they ship incrementally.

## When unsure
Re-read TDD §5 (Locked Decisions). If a decision is genuinely missing, pick the simplest option consistent with §5, implement it, and note it in the README — do not block.
````

---

## 15. `SKILL.md` modules (place each under `.claude/skills/<name>/SKILL.md`)

**How Claude Code skills actually work (verified against official docs — ignore "glob auto-inject" guides):**
- Each skill is a **folder** containing a `SKILL.md` (`.claude/skills/<name>/SKILL.md`). The folder name becomes the `/slash-command`.
- Skills are **model-invoked by `description`**, not by file globs. At startup Claude loads only each skill's `name` + `description`; it loads the full body **when the description matches your request** (or when you type `/name`). There is **no** open-a-`.ts`-file glob trigger — that is a Cursor concept, not Claude Code.
- Therefore the **`description` is the trigger, not documentation.** Write it in the third person, lead with what it does, and include the words you'll actually say ("Use when implementing checkout, discount, tax...").
- Only `name` + `description` are functional core frontmatter. Optional: `allowed-tools` (restrict tools — great for read-only audit skills) and `disable-model-invocation: true` (for side-effecting commands you want to fire only via `/name`, e.g. deploy/commit). **`version` / `author` / `license` / `metadata.tags` are NOT used by Claude Code** — omit them; they only add noise.
- Keep each body tight (well under 500 lines; aim for a screen). The body loads into context whenever the skill is active, so every line is a recurring token cost.
- **CLAUDE.md vs skills:** always-true rules (stack, locked decisions, golden rules) live in CLAUDE.md (always loaded). Repeatable procedures live in skills (loaded on demand). Don't duplicate.

These encode the repetitive build patterns so Claude Code stays consistent across 6 days.

### 15.1 `vertical-feature` — the core pattern (use for almost every feature)
````markdown
---
name: vertical-feature
description: Use whenever building or modifying any SEAPEDIA feature that touches the database (store, product, wallet, cart, checkout, order, delivery, discount, review, admin). Defines the exact file-by-file order to scaffold a full slice consistently.
---
# Building a vertical feature

Follow this order. Do not skip steps.

1. **Migration** — per TDD §7 schema. Index FKs. Money = BIGINT UNSIGNED.
2. **Model** — set `$fillable`, casts, relationships. Never `$guarded = []`.
3. **Factory + Seeder** — realistic demo data; wire into `DatabaseSeeder` (§12).
4. **FormRequest** — validate per §10 (types, ranges, required).
5. **Policy** (if the resource has an owner) — own-only access; register in `AuthServiceProvider`.
6. **Service method** — all logic here. If it mutates balance/stock/used_count/status → `DB::transaction` + `lockForUpdate`. Use `ClockService::now()` for time.
7. **Controller (web)** — thin; validate → service → Inertia render/redirect.
8. **Route** — behind `auth` + `EnsureActiveRole:<role>` (or `is_admin`) as required.
9. **API mirror** — for core flows, add `/api/v1` controller + Sanctum + Swagger annotation.
10. **Inertia page + Vue** — reuse UI kit; responsive; empty/error/loading states; never `v-html` user content.
11. **Pest test** — required for concurrency/idempotency-critical paths.
12. **Commit** — one focused conventional commit (see commit-message skill).

## Verification checklist
- [ ] Migration matches §7; FKs indexed; money is BIGINT UNSIGNED.
- [ ] Model has `$fillable`; no `$guarded = []`.
- [ ] Write path has a FormRequest; owned resource has a Policy.
- [ ] Money/stock/status mutation is inside a locked transaction.
- [ ] Time comes from ClockService, not now().
- [ ] Route is behind the correct role middleware.
- [ ] One conventional commit; files created/changed listed.
````

### 15.2 `money-and-checkout` — calculation correctness
````markdown
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
  rate_per_km = {instant:2500, next_day:1500, regular:1000}
  delivery_fee = base_fee + min(ceil(km),80)*rate_per_km + weight_fee   # DeliveryFeeService, §5.4a
  grand_total = taxable_base + tax_amount + delivery_fee
- Discount applied BEFORE tax. Delivery fee (base + distance + weight) NOT taxed.
- km = Haversine(store.origin lat/lng, buyer.address lat/lng); 0 when either coord missing.
  weight_fee = max(0, ceil((grams-1000)/1000))*2000; 0 when weight null.
  Compute it the SAME way in preview() and commit() — pass the same address + weight — so quote == charge.
  Driver distance is NOT in the fee (no driver at checkout); it only sorts jobs.
- Checkout commit (single DB::transaction):
  1. lockForUpdate each product; assert stock ≥ qty (else 422, no negative stock).
  2. lockForUpdate voucher (if any); assert remaining usage; increment used_count.
  3. lockForUpdate buyer wallet; assert balance ≥ grand_total (else 422); debit; write wallet_transaction(payment).
  4. decrement stock.
  5. create order (Sedang Dikemas) + order_items + status history. Record seller_income_amount.
- Always expose subtotal, discount, delivery_fee, tax_amount, grand_total in the preview response and order detail.
````

### 15.3 `order-lifecycle` — status machine + history
````markdown
---
name: order-lifecycle
description: Use when changing any order status (seller process, driver take/complete, overdue refund). Enforces the locked transition table and history logging.
---
# Order lifecycle (LOCKED — TDD §5.6, §5.9)

- Statuses (user-facing, Indonesian, never remove):
  sedang_dikemas → menunggu_pengirim → sedang_dikirim → pesanan_selesai
                                                       ↘ dikembalikan (overdue)
- Change status ONLY through OrderService::transition($order, $to, $by), which:
  - asserts the (from → to) pair is in the valid table; else throws (422).
  - writes order_status_histories {status, note, changed_by, created_at via ClockService}.
- Driver take: DB::transaction + lockForUpdate delivery; assign only if driver_id null (else 409).
- Overdue refund: idempotent via order.refunded_at sentinel + row lock. In one transaction:
  credit buyer wallet (grand_total), reverse seller income, restore stock, set refunded_at, status→dikembalikan, history.
- Never set $order->status = ... directly outside OrderService.
````

### 15.4 `ipaymu-topup` — gateway integration
````markdown
---
name: ipaymu-topup
description: Use when implementing wallet top-up, the PaymentGateway interface, IpaymuGateway, FakeGateway, or the iPaymu webhook. Encodes the verified v2 signature + idempotency rules.
---
# iPaymu top-up (TDD §9)

- iPaymu funds the WALLET only. Checkout never calls a gateway.
- Behind `PaymentGateway` interface. `PAYMENT_GATEWAY=fake` for demo/seed; `ipaymu` to exercise real flow.
- Signature (v2): stringToSign = "POST:"+VA+":"+lowercase(sha256(jsonBody))+":"+apiKey ;
  signature = hash_hmac('sha256', stringToSign, apiKey).
  Headers: va, signature, timestamp (YmdHis), Content-Type application/json.
  Endpoint: sandbox https://sandbox.ipaymu.com/api/v2/payment | prod https://my.ipaymu.com/api/v2/payment.
  Redirect Buyer to response Data.Url. Store Data.SessionID.
- Top-up record (topups) keyed by unique gateway_reference; status pending→paid.
- Webhook (notifyUrl, no auth): map by reference → if processed_at set, return 200 (idempotent) → else in a transaction credit wallet via WalletService, write wallet_transaction(topup), mark paid + processed_at.
- notifyUrl must be the deployed public HTTPS URL.
````

### 15.5 `security-pass` — Level 7 audit
````markdown
---
name: security-pass
description: Use on Day 6, or whenever finalizing a feature, to verify SQL injection, XSS, input validation, session, and authorization hardening before shipping. Read-only audit.
allowed-tools: Read, Grep, Glob
---
# Security pass (TDD §10)

Verify and fix:
- grep for `whereRaw`/`DB::raw` with interpolated input → none, or fully parameter-bound.
- grep for `v-html` → must never wrap user-generated content (reviews etc.).
- every write route has a FormRequest validating email/phone/rating(1-5)/qty/price/stock/discount.
- every private route passes auth + EnsureActiveRole (or is_admin); every owned resource has a Policy.
- Sanctum token expiry configured + documented; logout invalidates.
- webhook idempotent + reference-validated.
- demo: <script> in review renders inert; `' OR 1=1 --` in forms has no effect.

## Verification checklist
- [ ] No raw SQL with interpolated input.
- [ ] No `v-html` on user content.
- [ ] Every write endpoint validated by a FormRequest.
- [ ] Every private route + owned resource gated (middleware + Policy).
- [ ] Token expiry set; logout invalidates session/token.
- [ ] XSS + SQLi test cases demonstrated safe.
````

### 15.6 `commit-message` — graded commit history (adapted from the community guide, fixed for this project)
````markdown
---
name: commit-message
description: Use when committing changes or writing a git commit message for SEAPEDIA. Enforces Conventional Commits (type(scope): subject) with this project's scopes, imperative present tense, and per-slice granularity.
disable-model-invocation: false
---
# Commit message convention (history is GRADED)

Format: `type(scope): subject`  — subject ≤ 50 chars, imperative present tense ("add", not "added"), no trailing period.
Optional body explains WHY/impact, not WHAT.

## Types
| type | when |
|------|------|
| feat | user-facing feature |
| fix | bug a user could hit |
| refactor | no behavior change |
| test | add/modify tests |
| docs | documentation only |
| chore | tooling, config, deps |
| style | formatting/whitespace |
| perf | performance |

## Scopes (this project)
auth · role · store · product · catalog · wallet · cart · checkout · discount · order · delivery · admin · overdue · report · ui · db · api · docker · deploy · security · docs

## Rules
1. One logical change per commit. Do not mix two unrelated features.
2. Commit per vertical slice, in TDD §13 order, so history reads like the build.
3. NEVER squash the whole project into one commit.
4. A feature's migration+model+service may share one commit; UI for the same feature can be its own commit.

## Examples
- feat(auth): add register, login, logout with hashing
- feat(role): enforce active-role via middleware
- feat(checkout): charge wallet and reduce stock atomically
- fix(overdue): prevent double refund with refunded_at guard
- test(delivery): cover concurrent take-job rejection
- chore(deploy): add production compose with nginx and caddy

## Pitfalls
- Don't use `feat` for invisible internal changes → use `chore`/`refactor`.
- Don't use past tense ("added", "fixed").
- Don't bundle unrelated work to "save commits" — granularity is the point being graded.

## Verification checklist
- [ ] `type(scope): subject`, ≤ 50 chars, imperative.
- [ ] Scope is from the list above.
- [ ] One logical change.
- [ ] Body (if any) explains WHY.
````

### 15.7 `code-review` — self-review before each commit (read-only)
````markdown
---
name: code-review
description: Use before committing a SEAPEDIA slice, or when reviewing changes, to self-check logic, security, performance, readability, and consistency against this project's rules.
allowed-tools: Read, Grep, Glob
---
# Self code-review (5 layers)

## 1. Logic
- Edge cases handled (empty cart, zero stock, insufficient balance, expired/used voucher)?
- Money/stock/status mutations inside a locked DB::transaction?
- Idempotency guards present where required (refund, webhook, take-job)?

## 2. Security
- Eloquent only; no raw SQL with interpolation. No `v-html` on user input.
- FormRequest validates the input. Route gated by correct role / Policy.
- No secrets hardcoded; uses env.

## 3. Performance
- No N+1 (eager load relations used in views/reports).
- Queries hit indexes (status, sla_due_at, FKs).

## 4. Readability
- Logic in Services, not controllers. Functions focused (~≤30 lines).
- Clear names; comments explain WHY, not WHAT.

## 5. Consistency
- Follows TDD §5 locked decisions and the unified wallet model.
- Matches existing naming/structure; no new dependency without a TDD-stated need.

## Verification checklist
- [ ] 5 layers checked.
- [ ] Locked decisions (§5) respected.
- [ ] No N+1; indexes used.
- [ ] No raw SQL / no v-html on user content.
- [ ] Files placed per project structure (§2.5); enums used for fixed sets.
- [ ] `pint` + ESLint/Prettier pass.
````

### 15.8 `sprint-planner` — plan a sprint before building it
````markdown
---
name: sprint-planner
description: Use at the START of any sprint, before writing feature code, to produce the written plan at planning/sprint{N}/plan.md. Always run this and pause for approval before implementing a sprint.
---
# Sprint planning (write the plan, then STOP)

When asked to start sprint N (or "plan sprint N"):
1. Read the matching Day N section in TDD §13, plus the SEAPEDIA challenge criteria for the level(s) that sprint covers, and §5 locked decisions.
2. Create `planning/sprint{N}/plan.md` with the structure below.
3. Create `planning/sprint{N}/progress.md` as an empty checklist mirroring the task list.
4. Commit: `docs(planning): add sprint{N} plan`.
5. **STOP. Do not write any feature code.** Print a short summary and ask for approval.
6. Only after approval, implement task-by-task using the `vertical-feature` skill, updating progress.md and committing per slice.

## `plan.md` required structure
```
# Sprint {N} Plan — {sprint title}

## Goal
One paragraph: what works end-to-end when this sprint is done.

## Scope (challenge criteria covered)
- Level X.Y — <criterion> (pts)
- ... (list each gradable item this sprint satisfies)

## Locked decisions referenced
- Link the §5 rules this sprint must honor (e.g. §5.2 PPN order, §5.8 cart rule).

## shadcn-vue components needed
- List every shadcn-vue component this sprint will use. These get `npx shadcn-vue@latest add ...`'d as the first UI step, before any import.

## Task breakdown (ordered vertical slices)
For each task:
- T{n}: <name>
  - Files: migrations/models/services/requests/policies/controllers/routes/pages to create or change
  - Business rules: which §5 / §4 rules apply
  - Acceptance: what must be demoable for this task to be "done"
  - Tests: Pest cases (esp. concurrency/idempotency) — or "none"
  - Commit: the conventional message

## Demo checklist (end of sprint)
- Bullet the end-to-end flow to verify (mirror the challenge's Final Demo Checklist items for this level).

## Risks / open questions
- Anything ambiguous, plus the simplest §5-consistent assumption you'll make if unanswered.

## Out of scope (deferred)
- What belongs to later sprints, so this sprint stays bounded.
```

## Rules
- Plans describe WHAT and in what order; they do not contain full implementation code.
- Keep tasks small enough to be one commit each.
- If the sprint covers two levels (Day 5), list both levels' criteria in Scope and group tasks per level.
- Never skip the approval pause.
````

---

## 16. Deployment plan

**Target:** GCP `e2-micro` (free tier) — you already know this box from the Menfess project; reuse that knowledge.

- **Compose services:** `app` (php-fpm + the built app), `nginx` (reverse proxy + serves built assets), `mysql`. Build Vite assets in a node stage, copy into the image.
- **HTTPS:** Caddy as the edge (automatic Let's Encrypt) in front of nginx, or nginx + certbot. HTTPS is mandatory for the iPaymu webhook.
- **Domain:** a free subdomain (e.g. nip.io / sslip.io against the static IP) is acceptable if you don't have a domain; document it.
- **Env:** production `.env` with real DB creds, `APP_ENV=production`, `APP_DEBUG=false`, `PAYMENT_GATEWAY=fake` (flip to `ipaymu` only for the real-flow demo), iPaymu sandbox creds.
- **Deploy loop:** push to GitHub → pull on the box → `docker compose up -d --build` → `php artisan migrate --force` (and `--seed` once). Keep it a one-line script.
- **Day-1 proof:** the live URL must serve the app before feature #2. Re-deploy at least at the end of each day so "deployed" is never a last-minute scramble.

---

## 17. README outline (Level 7 deliverable — draft as you go)

1. Project overview + live URL.
2. Stack + architecture diagram.
3. Setup: Docker run, env vars table, `migrate:fresh --seed`.
4. **Demo accounts** (admin/seller/buyer/driver/multi-role) + passwords.
5. **Admin setup** instructions.
6. **Locked business rules:** auth mechanism (Sanctum session for web, opaque token for API — no JWT; token revoked on logout), single-store checkout, discount combination + PPN 12% base, driver earning (80% of fee), overdue SLA per method + how to simulate time (`seapedia:advance-day` / admin trigger).
7. **Security notes:** SQLi, XSS, input validation, session/token behavior, RBAC.
8. iPaymu config + how to switch fake/real.
9. API docs link (Swagger) / Postman collection.
10. **End-to-end testing guide** (the exact demo path).
11. Deployment notes.

---

## 18. Definition of "done" for the whole project

- [ ] Deployed, public, reachable by the evaluator.
- [ ] `migrate:fresh --seed` produces a fully demoable world on a clean machine.
- [ ] Every claimed level's flows pass end-to-end (use the Final Demo Checklist in the challenge PDF as the acceptance test).
- [ ] README + API docs complete.
- [ ] Git history shows incremental, conventional commits (no squash).
- [ ] Security test cases demonstrable.
- [ ] A recorded end-to-end demo exists as backup in case live demo has issues.

---

*End of TDD. The Locked Decisions (§5) and Schema (§7) override anything that conflicts. Build vertically, deploy early, commit per slice, protect the FLOOR.*
