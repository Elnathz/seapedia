# SEAPEDIA

A multi-role campus marketplace. Buyers, sellers, and drivers share one
platform and one wallet, and switch roles per session. Built with Laravel 13
(PHP 8.3), Inertia + Vue 3 (TypeScript), shadcn-vue, Tailwind 4, and MySQL 8,
and it runs on Docker through Laravel Sail.

The graded requirements live in the committee brief, `docs/SEAPEDIA_SPEC.md`.
That file is the source of truth for what this project must do. Our extended
design notes (schema, locked values, historical rationale) live in
`docs/SEAPEDIA_TDD.md`, and the sprint plans under `docs/planning/`. Where any
of those disagree with the brief, the brief wins.

## Setup (Docker)

You need two things: Docker, and a shell to run the commands in. Getting them
differs a little per operating system.

- **Windows.** Install Docker Desktop with the WSL2 backend enabled. Run every
  command below inside a WSL2 Ubuntu terminal. Sail is a Linux tool, so WSL2 is
  where it behaves properly. Git Bash mostly works too, but it rewrites the
  paths in Docker volume mounts and will trip you up.
- **macOS.** Install Docker Desktop. Run the commands in Terminal (or iTerm).
- **Linux.** Install Docker Engine plus the Compose plugin. Run the commands in
  your normal shell.

Everything after that is identical on all three.

1. **Clone the repository**
   ```bash
   git clone https://github.com/Elnathz/seapedia.git
   cd seapedia
   ```

2. **Set up environment variables**
   ```bash
   cp .env.example .env
   ```
   The default `.env.example` has everything needed to run locally. It already
   points `APP_URL` at `http://localhost` and `DB_HOST` at the `mysql`
   container.

3. **Install PHP dependencies (first run only)**
   A fresh clone has no `vendor/` directory yet, so the `sail` script does not
   exist. Build one with a throwaway Composer container. No local PHP required:
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php84-composer:latest \
       composer install --ignore-platform-reqs
   ```

4. **Start the containers**
   ```bash
   ./vendor/bin/sail up -d
   ```

5. **Initialize the application**
   Set the app key, build the database, and link storage:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate:fresh --seed
   ./vendor/bin/sail artisan storage:link
   ```
   `storage:link` matters here: seeded and uploaded product images will not
   load without it.

6. **Install and run the frontend**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

7. **Open the app** at `http://localhost`.

### Prefer to run it without Docker?

You can. Install PHP 8.3, Node 20+, and MySQL 8 yourself, set `DB_HOST=127.0.0.1`
in `.env`, then run the same commands with the `./vendor/bin/sail` prefix
dropped (`php artisan migrate:fresh --seed`, `npm run dev`, and so on). Docker is
the path we test, so reach for it first if you just want the app up.

## Admin account

The seeder creates the `admin` user for you during `migrate:fresh --seed`. To
promote an existing user to admin by hand, use Tinker:

```bash
./vendor/bin/sail artisan tinker --execute="App\Models\User::where('username','admin')->update(['is_admin'=>true]);"
```

## Demo credentials

Every seeded account uses the password `password`. Usernames and display names
are random Faker data, so log in with the fixed emails below.

| Email                     | Role(s)                | Notes                                                                                                   |
| ------------------------- | ---------------------- | ------------------------------------------------------------------------------------------------------- |
| `admin@seapedia.test`     | Admin (`is_admin`)     | Admin monitoring dashboard, voucher/promo management, overdue time machine.                             |
| `seller1@seapedia.test`   | Seller                 | Store "Toko Berkah" (3 products). Other sellers: `seller2@seapedia.test`..`seller7@seapedia.test`.      |
| `buyer1@seapedia.test`    | Buyer                  | Wallet balance Rp 500.000, 1 address, active orders.                                                    |
| `driver1@seapedia.test`   | Driver                 | 1 active job, 2 completed jobs.                                                                         |
| `multi1@seapedia.test`    | Buyer, Seller, Driver  | Store "Warung Mama Lia" (3 products), Wallet balance Rp 300.000.                                        |

## Core business rules & features

- **Single-store checkout.** A cart holds items from one store at a time. Adding
  an item from a different store prompts the buyer to clear the current cart.
- **PPN 12%.** Tax applies to the subtotal *after* discounts. Delivery fees are
  not taxed.
- **Delivery fee (base + distance + weight).** `delivery_fee = base(method) + billable_km × rate(method) + weight_fee`.
  Each method has a base fee (Instan Rp20.000 / Besok Rp10.000 / Reguler
  Rp5.000) and a per-km rate (Rp2.500 / Rp1.500 / Rp1.000). Distance is the
  Haversine km between the store's origin and the buyer's address, both pinned
  on a Leaflet/OpenStreetMap map (no paid API key), billed up to an 80 km cap.
  Weight adds Rp2.000 per kilogram past the first free kilogram, and every
  product or variant carries a mandatory weight. The spec only requires the fee
  to differ per method (line 278); the distance and weight parts are our
  documented, spec-legal extension. The fee is never taxed, and it is computed
  the same way in the checkout preview and the final charge (the quote equals
  the charge). When a coordinate or a weight is missing it falls back to the
  base fee. A driver's own distance never changes the buyer's fee, since no
  driver is assigned at checkout; it only sorts the delivery jobs.
- **Unified wallet.** Buyers, sellers, and drivers share one wallet. Every
  transaction is written as an immutable ledger entry inside a database
  transaction. A single top-up is capped between Rp5.000 and Rp100.000.000. The
  spec leaves top-up amounts open, so those limits are ours and documented here.
- **Vouchers and promos.** A voucher and a promo can apply at the same time.
  Both honour minimum-spend thresholds, maximum-discount caps, and usage limits.
  Expired or exhausted codes are rejected on the spot.
- **Overdue refund / time machine.** Admins can advance the simulated clock to
  test SLA deadlines. An overdue order that was never delivered is refunded to
  the buyer's wallet automatically. Refunds never double up, and seller income
  is not reversed because it stays in escrow until delivery completes.
- **Delivery SLA and near-cancel urgency.** The spec requires SLA rules per
  method (line 454). Each order carries an `sla_due_at` deadline set by its
  delivery method: Instan 1 day, Besok 2 days, Reguler 4 days, measured in
  simulated day-ticks. The seller order lists and the driver dashboard sort each
  open order into *Overdue* (past due), *Critical* (one day-tick or less left),
  or *Normal*, push the at-risk ones to the top, and show the state as a
  read-only badge. The threshold lives in the backend; the UI only formats it.
- **Multi-role self-service.** One non-admin username can own Buyer, Seller, and
  Driver at once and pick an active role per session (spec lines 38 and 39).
  From the profile page, a user can add a role they lack or drop one they have.
  Dropping a role is guarded: a Seller with a live store or a Driver with active
  deliveries is blocked with a 422 rather than orphaning the dependent records.
- **Driver reliability tiers.** The spec never says how many jobs a driver may
  hold at once, so we cap it and let the cap grow with a proven track record (a
  documented, spec-legal extension). A driver holds 1 active job by default, 2
  after 15 on-time completions, and 3 after 30. "On time" means confirmed on or
  before the order's `sla_due_at`. The count runs inside a row-locked
  transaction, so two claims arriving at once can't both slip past the cap.

## API documentation

The API is documented with Swagger/OpenAPI. With the app running, open the
Swagger UI at `http://localhost/api/documentation`. It covers 33 endpoints
across 13 tags, including Catalog, Buyer Wallet, Checkout, Promo/Vouchers, and
the Admin functions.

## Security notes

Security is handled layer by layer:

- **SQL injection.** Only Eloquent and the query builder touch the database, and
  both bind parameters through PDO.
- **Cross-site scripting.** Inertia and Vue escape user input on render. Raw HTML
  is avoided, and sanitized where it is genuinely needed.
- **Input validation.** Every web and API write goes through a Laravel Form
  Request with typed, strict rules before it reaches a controller.
- **Sessions and CSRF.** Web routes use Laravel's CSRF protection
  (`VerifyCsrfToken`). API routes under `/api/v1/*` use Sanctum bearer tokens.
- **Role-based access control.**
  - Policies (`ProductPolicy`, `OrderPolicy`, `DeliveryPolicy`, and others)
    enforce ownership and role scope.
  - The `is_admin` and `active_role` middleware gate role-specific routes and
    endpoints.
  - Money-moving operations (checkout, wallet debits) run inside
    `DB::transaction()` with `lockForUpdate()`, which closes the door on races
    like double refunds and double job claims.
- **Account deletion and anonymization.** Deleting an account anonymizes the PII
  (name, email) and soft-deletes the row, so historical orders stay intact.
  Per-role removal is guarded the same way (a driver can't resign mid-delivery).
- **Secured avatar upload.** An avatar upload is limited by a Form Request
  (`jpeg/jpg/png/webp`, 2 MB max, 2000×2000 px max) and then re-encoded through
  GD into a fresh PNG before it is stored. Re-encoding throws away anything that
  slipped past the MIME check (EXIF metadata, polyglot files, embedded scripts),
  so only clean pixel data ever lands on disk.

## Formatting and testing

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail pint
./vendor/bin/sail npm run lint
./vendor/bin/sail npm run format
```

## Engineering decisions

### Region selector (address management)

Indonesia has roughly 80,000 provinces, regencies, districts, and villages.
Rather than seed all of them into our database, we pull them on demand from the
public EMSIFA API (https://www.emsifa.com/api-wilayah-indonesia/). This keeps
`migrate --seed` fast during judging, keeps the schema focused on the actual
e-commerce tables instead of a mountain of static reference data, and lets the
address form use cascading dropdowns that fetch each level as the user picks the
one above it.
