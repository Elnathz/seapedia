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

Seeded by `migrate:fresh --seed` (`DemoUserSeeder` + `StoreProductSeeder`).
Every account's password is `password`.

| Username  | Role(s)                | Notes                                   |
| --------- | ----------------------- | ---------------------------------------- |
| `admin`   | Admin (`is_admin`)      | Lands on the admin dashboard shell       |
| `seller1` | Seller                  | Single role — skips the role-select step. Owns store "Toko Berkah" (3 products, seeded images) |
| `buyer1`  | Buyer                   | Wallet pre-funded with a placeholder balance (Rp 500.000) |
| `driver1` | Driver                  | Single role — skips the role-select step |
| `multi1`  | Buyer, Seller, Driver   | Multi-role — shows the role-select modal on login. Owns store "Warung Mama Lia" (3 products, seeded images) |

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

## Current status

Sprint 2 (Level 2 — seller store + product CRUD with image upload, real
DB-backed public catalog + store pages, `/api/v1/catalog` + Swagger, hybrid
ID/EN i18n) is complete. See `planning/sprint2/progress.md` for the
task-by-task log and documented deviations from the TDD. Sprint 1 (Level 1)
log is at `planning/sprint1/progress.md`.

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
