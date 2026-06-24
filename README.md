# SEAPEDIA

A multi-role campus marketplace — buyers, sellers, and drivers share one
platform and one wallet, with roles switchable per session. Built with
Laravel 13, Inertia + Vue 3 (TypeScript), shadcn-vue, Tailwind 4, and MySQL,
running on Docker via Laravel Sail.

Full product/technical decisions live in `docs/SEAPEDIA_TDD.md`. Sprint plans and
progress are tracked under `planning/sprint{N}/`.

## Setup

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan storage:link
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

The app runs at `http://localhost`. `storage:link` is required for seeded
and seller-uploaded product images to load — without it, product images
404 even though the database row is correct.

## Demo credentials

Seeded by `migrate:fresh --seed` (`DemoUserSeeder` + `StoreProductSeeder` +
`BuyerDemoSeeder`). Every account's password is `password`.

| Username  | Role(s)                | Notes                                   |
| --------- | ----------------------- | ---------------------------------------- |
| `admin`   | Admin (`is_admin`)      | Lands on the admin dashboard shell       |
| `seller1` | Seller                  | Single role — skips the role-select step. Owns store "Toko Berkah" (3 products, seeded images). Has one incoming order from `buyer1` |
| `buyer1`  | Buyer                   | Wallet topped up via `TopupService` (Rp 500.000, with a real ledger entry); one saved address; one placed order against "Toko Berkah" |
| `driver1` | Driver                  | Single role — skips the role-select step |
| `multi1`  | Buyer, Seller, Driver   | Multi-role — shows the role-select modal on login. Owns store "Warung Mama Lia" (3 products, seeded images). Wallet topped up (Rp 300.000) and one saved address as the buyer role |

## Demo path (Sprint 1)

1. Guest: landing (`/`) → catalog (`/catalog`) → product detail → submit a
   review (`/reviews`).
2. Log in as `multi1` → pick a role from the modal → land on that role's
   dashboard → switch role from the sidebar badge.
3. Log in as `seller1` or `driver1` → single role, no modal, straight to
   the dashboard shell.
4. Log in as `admin` → dashboard shows live platform counts (users per
   role); full admin tooling (user management, overdue sweep, reports)
   ships in Sprint 5.

## Demo path (Sprint 2)

1. Log in as `seller1` → sidebar "Toko Saya" shows the seeded store; "Produk"
   shows its 3 seeded products (table, image thumbnails, Edit/Delete).
2. Create a new product with an image upload → it appears in the seller
   list and in the public catalog (`/catalog`) immediately.
3. Guest: catalog → search by name → open a product → see the store info
   block → open the store's public page (`/stores/{slug}`) → see only that
   store's active products.
4. Log in as `multi1` (switch to seller) → try opening `seller1`'s product
   edit URL directly by id → **403** (cross-seller ownership enforced by
   `ProductPolicy`).
5. Toggle language **ID ⇄ EN** from the navbar (guest or logged in) or
   Settings → Appearance → the whole UI flips instantly, no reload; a
   logged-in user's choice persists after logout/login.
6. `GET /api/v1/catalog` mirrors the same active-only data; Swagger UI at
   `/api/documentation` lists the `Catalog` endpoints.

## Demo path (Sprint 3)

1. Log in as `buyer1` → "Dompet" shows the seeded balance and one `topup`
   ledger entry; top up again with a quick-pick amount → balance and ledger
   update immediately.
2. "Alamat" → add/edit an address, set a different one as default, delete
   one (the remaining one is promoted to default automatically).
3. Browse the catalog → add a product to the cart → "Keranjang" shows it
   with a qty stepper and running subtotal → try adding a product from a
   **different store** → blocked with a dialog → "Clear & add" replaces
   the cart with the new store's item.
4. "Lanjut ke Checkout" → pick the address + a delivery method (Instant /
   Besok Sampai / Reguler) → the summary ledger shows subtotal, discount
   (always 0 this sprint), delivery fee, and **PPN 12%** → confirm → wallet
   debited, stock reduced, order created **Sedang Dikemas**, cart cleared.
5. "Pesanan Saya" lists it with the money breakdown and status timeline.
   Switch to `seller1` → "Pesanan Masuk" shows the same order (read-only —
   the process action ships in Sprint 4).
6. Lower the wallet balance below the order total (e.g. via repeated
   top-ups elsewhere) and try checkout again → the "Bayar Sekarang" button
   is disabled with a clear reason and a "Top up dompet" recovery link.
7. `/api/v1/buyer/*` mirrors the whole flow (Sanctum, role-scoped token);
   Swagger UI lists the `Buyer Wallet`, `Buyer Addresses`, `Buyer Cart`,
   `Checkout`, and `Buyer Orders` tags.

### Locked rules this sprint depends on

- **Single-store cart (§5.8):** one cart per buyer, locked to whichever
  store the first item came from. Adding a product from another store is
  rejected (422) with a "Clear & add" recovery action — never silently
  mixed.
- **PPN 12% base (§5.2):** `taxable_base = subtotal − discount_total`;
  `tax_amount = round(taxable_base × 0.12)`. Delivery fee is **not** taxed.
  `grand_total = taxable_base + tax_amount + delivery_fee`. Discount is a
  zero placeholder this sprint — Sprint 4 fills in real vouchers/promos
  against the same formula, no checkout rework needed.
- **Unified wallet model (§5.1b):** every buyer/seller/driver has exactly
  one wallet; every movement is an immutable `wallet_transactions` row
  (`type` + `direction` + `balance_after`) written inside a locked
  transaction — never a raw balance write. Seller income settles
  **instantly** at checkout (the product revenue only, excluding tax and
  delivery fee) rather than waiting for delivery completion — a deliberate
  simplification consistent with §5.1b's "spendable... instant settlement"
  and what §5.9's overdue-refund reversal assumes already happened.
- **Fake top-up gateway (§9):** `PAYMENT_GATEWAY=fake` (the default) credits
  the wallet instantly via `FakeGateway`, so the demo never depends on
  iPaymu uptime. `IpaymuGateway` is scaffolded behind the same
  `PaymentGateway` interface; its real v2 call ships in Sprint 6. No
  minimum top-up amount is locked by the TDD — 10,000 IDR
  (`config('payment.topup.min_amount')`) was picked as the simplest
  reasonable floor.

## Current status

Sprint 3 (Level 3 — buyer wallet + fake top-up, delivery addresses, cart
with the single-store guard, checkout with PPN 12% in one locked
transaction, order history + seller incoming list, `/api/v1/buyer/*` +
Swagger) is complete. See `planning/sprint3/progress.md` for the
task-by-task log and documented deviations from the TDD. Sprint 2 (Level 2)
log is at `planning/sprint2/progress.md`; Sprint 1 (Level 1) log is at
`planning/sprint1/progress.md`.

## Tests

```bash
./vendor/bin/sail artisan test
```

## Formatting

```bash
./vendor/bin/sail pint
./vendor/bin/sail npm run lint
./vendor/bin/sail npm run format
```
