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

## Live demo

A production instance runs at **https://seapedia.web.id**. It is seeded with the
same demo accounts as a local install, so you can try the whole marketplace
without installing anything. Log in with the fixed emails from
[Demo credentials](#demo-credentials) using the password `password`.

Notes for reviewers:

- Payments are simulated. The wallet is the only money in the system, and
  top-ups go through a fake gateway, so nothing ever touches a real card.
- The site serves the production build from `docker-compose.prod.yml` (nginx,
  PHP-FPM 8.3, and MySQL 8) over HTTPS on a small VPS.
- One shared database backs it, so demo data drifts as people click around. If
  an account looks off, run it locally for a clean seed.

## Setup (Docker)

### Prerequisites — do these first

> **Docker must be installed and running before any step below will work.**
> If you see `The command 'docker' could not be found`, start here.

| OS | What to install | Where to get it |
|---|---|---|
| **Windows** | Docker Desktop (includes Docker Engine + Compose) | https://docs.docker.com/desktop/install/windows-install/ |
| **macOS** | Docker Desktop | https://docs.docker.com/desktop/install/mac-install/ |
| **Linux** | Docker Engine + Compose plugin | https://docs.docker.com/engine/install/ |

**Windows extra step — WSL Integration:**

After installing Docker Desktop you must link it to your WSL2 distro, otherwise
the `docker` command will not be found inside WSL terminals.

1. Open **Docker Desktop → Settings → Resources → WSL Integration**.
2. Turn on **"Enable integration with my default WSL distro"**.
3. If your Ubuntu distro is listed separately, enable its toggle too.
4. Click **Apply & Restart** and wait for Docker Desktop to come back up.
5. Open a new WSL2 terminal and run `docker --version` to confirm it works.

After that, run every command below inside a WSL2 Ubuntu terminal. Sail is a
Linux tool and behaves correctly there. Git Bash mostly works too, but it
rewrites Docker volume-mount paths and will occasionally trip you up.

**macOS / Linux:** Open Docker Desktop (macOS) or start the Docker daemon
(`sudo systemctl start docker`) and verify with `docker --version`.

Everything after that is identical on all three operating systems.

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
   > **`The command 'docker' could not be found`?** Docker Desktop is either not
   > installed or not integrated with your WSL2 distro. Complete the
   > [Prerequisites](#prerequisites--do-these-first) section above and try
   > again.

4. **Start the containers**
   ```bash
   ./vendor/bin/sail up -d
   ```
   > No `--build` flag needed. Sail uses its own pre-built image
   > (`laravelsail/php85-composer`), not the project's `Dockerfile`. The
   > `Dockerfile` here is the **production** multi-stage build (used by
   > `docker-compose.prod.yml`), which runs `npm run build` automatically
   > inside the container — it is not involved in local dev at all.

5. **Initialize the application**
   Set the app key, build the database, and link storage:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate:fresh --seed
   ./vendor/bin/sail artisan storage:link
   ```
   `storage:link` matters here: seeded and uploaded product images will not
   load without it.

6. **Build or run the frontend**

   **Option A — just reviewing the app** (no live reload, simpler):
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   ```
   This compiles assets once into `public/build/`. The app at
   `http://localhost` will work immediately after, with no extra process
   running.

   **Option B — active development** (hot module replacement):
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```
   Starts the Vite dev server. Keep this terminal open while you work;
   edits to Vue/TS/CSS files reload the browser instantly.

7. **Open the app** at `http://localhost`.

### Prefer to run it without Docker?

You can. Install PHP 8.3, Node 20+, and MySQL 8 yourself, set `DB_HOST=127.0.0.1`
in `.env`, then run the same commands with the `./vendor/bin/sail` prefix
dropped (`php artisan migrate:fresh --seed`, `npm run dev`, and so on). Docker is
the path we test, so reach for it first if you just want the app up.

### Troubleshooting

#### `Access denied for user 'seapedia'@...` during migrate

This happens when a MySQL Docker volume from a **previous run** already exists.
Docker initialises the database user only on first boot; if the volume is
already there it skips that step, so a changed password in `.env` is never
applied.

It affects **anyone** who has run this project before and is re-installing,
not just the original developer. A fresh clone on a machine that has never run
the project will not hit it.

Fix — wipe the old volume and start fresh:

```bash
./vendor/bin/sail down -v   # stops containers AND deletes all volumes
./vendor/bin/sail up -d     # MySQL re-initialises from scratch
# wait ~15 s for MySQL to be ready, then:
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan storage:link
```

> `down -v` deletes all database data. This is intentional for a clean
> re-install; do not use it if you have data you want to keep.

#### `The [public/storage] link already exists`

This is a notice, not an error. The symlink was created by an earlier run.
The app works fine; you can safely ignore the message.


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

## Roles

A non-admin account can hold any mix of Buyer, Seller, and Driver, and it picks
one active role per session. Authorization always follows the active role, and
it is checked on the server, never on the role the UI happens to show.

- **Buyer.** Tops up a wallet, saves delivery addresses, fills a single-store
  cart, and checks out by paying from the wallet. Reads order history and tracks
  each delivery. A buyer cannot check out with an insufficient balance, and
  cannot mix two stores in one cart. A single top-up is capped between Rp5.000
  and Rp100.000.000.
- **Seller.** Owns one store with a unique name, manages products and stock, and
  processes incoming orders so they become delivery jobs. Sale income is held in
  escrow and released only when the driver completes the delivery, so an overdue
  refund never claws back money the seller has already been paid. The seller has
  an income report.
- **Driver.** Finds open delivery jobs, takes them, and confirms completion.
  Earns 80% of each order's delivery fee, credited on completion. Holds 1 job at
  a time by default, 2 after 15 on-time completions, and 3 after 30. One job
  belongs to exactly one driver.
- **Admin.** A separate, seeded role that nobody can self-assign. Monitors
  users, stores, products, orders, vouchers and promos, delivery jobs, and
  overdue orders; manages the vouchers and promos; and runs the overdue time
  machine.

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
- **Vouchers and promos.** A voucher and a promo can apply to the same order.
  The promo comes off the subtotal first (capped at the subtotal), then the
  voucher comes off what is left, so the two together never exceed the subtotal.
  PPN 12% is charged afterward, on `subtotal - total discount`. Both discount
  types honour minimum-spend thresholds, maximum-discount caps, and usage
  limits, and expired or exhausted codes are rejected on the spot.
- **Overdue refund / time machine.** Admins can advance the simulated clock to
  test SLA deadlines. To move time forward, run
  `./vendor/bin/sail artisan seapedia:advance-day` (one simulated day per run),
  or use the Time Machine buttons on the admin overdue page. An overdue order
  that was never delivered is refunded to the buyer's wallet automatically, the
  refund is written to the wallet history, and the order moves to `Dikembalikan`.
  Refunds never double up, and seller income is not reversed because it stays in
  escrow until delivery completes.
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
- **Driver earning.** A driver earns 80% of the order's delivery fee
  (`intdiv(delivery_fee * 80, 100)`), credited to the driver's wallet in the same
  locked transaction that marks the job complete and releases the seller's
  escrowed income.
- **Driver reliability tiers.** The spec never says how many jobs a driver may
  hold at once, so we cap it and let the cap grow with a proven track record (a
  documented, spec-legal extension). A driver holds 1 active job by default, 2
  after 15 on-time completions, and 3 after 30. "On time" means confirmed on or
  before the order's `sla_due_at`. The count runs inside a row-locked
  transaction, so two claims arriving at once can't both slip past the cap.

## How to review the app

The quickest way to see every rule fire is to walk one order from browsing all
the way to an overdue refund. Every demo account uses the password `password`,
and you switch roles from the account menu after logging in.

1. **Browse as a guest.** Open the catalog, search for a product, and read the
   public app reviews without logging in. Leave a review with a rating and a
   comment. Try putting a `<script>` tag in the comment: it renders as text and
   never executes, which is the XSS check from Level 7.
2. **Sign in as a seller** (`seller1@seapedia.test`). Use the seeded products,
   or add a new one with a price, stock, and weight.
3. **Sign in as a buyer** (`buyer1@seapedia.test`). Top up the wallet, add a
   delivery address, and add items from one store to the cart. Try adding an
   item from a second store: the app asks you to clear the cart first. Open
   checkout. The summary breaks down subtotal, discount, delivery fee, PPN 12%,
   and total. Apply a voucher and a promo together to watch the combined
   discount, pick a delivery method, then confirm. The wallet is charged and the
   order starts at `Sedang Dikemas`.
4. **Back as the seller,** process that order. It moves out of packing and shows
   up as an available delivery job.
5. **Sign in as a driver** (`driver1@seapedia.test`). Take the job, then confirm
   completion. The driver's wallet receives 80% of the delivery fee and the
   seller's escrowed income is released at the same moment.
6. **Sign in as admin** (`admin@seapedia.test`). Look through the monitoring
   pages, create or edit a voucher or promo, then open the overdue page.
7. **Test overdue handling.** Place another order, leave it undelivered, and
   advance the clock past its SLA with the Time Machine buttons (or
   `./vendor/bin/sail artisan seapedia:advance-day`; Instan is due in 1 day,
   Besok in 2, Reguler in 4). The order is refunded to the buyer's wallet once,
   the refund appears in the wallet history, and the order becomes
   `Dikembalikan`. Advancing time again does not refund it a second time.

The seeded data already includes orders in several states, so you can inspect
each stage without building it up from scratch first.

## API documentation

The API is documented with Swagger/OpenAPI. With the app running, open the
Swagger UI at `http://localhost/api/documentation`. It covers 38 endpoints
across 33 paths and 13 tags: Auth, Catalog, Buyer Wallet, Buyer Cart, Buyer
Addresses, Buyer Orders, Buyer Reports, Checkout, Seller Orders, Seller Reports,
Driver Jobs, Admin, and Admin Discounts.

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
  Web sessions last 120 minutes (`SESSION_LIFETIME`) and Sanctum tokens expire
  after 480 minutes (`SANCTUM_TOKEN_EXPIRATION`). Logging out clears the session
  and revokes the token.
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
