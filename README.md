# SEAPEDIA

A multi-role campus marketplace — buyers, sellers, and drivers share one
platform and one wallet, with roles switchable per session. Built with
Laravel 13 (PHP 8.3), Inertia + Vue 3 (TypeScript), shadcn-vue, Tailwind 4,
and MySQL 8, running on Docker via Laravel Sail.

The graded requirements are the committee brief in `docs/SEAPEDIA_SPEC.md` —
that document is the source of truth for what this project must do. Our
extended design notes (schema, locked values, historical rationale) live in
`docs/SEAPEDIA_TDD.md`, and sprint plans under `docs/planning/`; where those
ever disagree with the brief, the brief wins.

## Setup & Running Locally (Docker)

Ensure you have Docker and Docker Compose installed on your system.
This project uses Laravel Sail, which provides a light-weight Docker environment.

1. **Clone the repository**
   ```bash
   git clone https://github.com/Elnathz/seapedia.git
   cd seapedia
   ```

2. **Setup Environment Variables**
   ```bash
   cp .env.example .env
   ```
   The default `.env.example` contains all the necessary variables to run locally. Ensure `APP_URL` is set to `http://localhost`.

3. **Install PHP dependencies (first run only)**
   A fresh clone has no `vendor/` directory yet, so the `sail` script does not
   exist. Populate it with a one-off Composer container — no local PHP required:
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php84-composer:latest \
       composer install --ignore-platform-reqs
   ```
   *(On native Windows PowerShell, run this from WSL, or drop the `-u "$(id -u):$(id -g)"` line.)*

4. **Start the Docker Containers**
   ```bash
   ./vendor/bin/sail up -d
   ```

5. **Initialize the Application**
   Run the following commands to set up the application key, database, and storage:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate:fresh --seed
   ./vendor/bin/sail artisan storage:link
   ```
   *(Note: `storage:link` is crucial for seeded and uploaded product images to load correctly).*

6. **Install Frontend Dependencies & Build**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

7. **Access the App**
   Open `http://localhost` in your browser.

## Admin Account Setup

The `admin` seed user is created automatically by the database seeder (`migrate:fresh --seed`).
If you need to manually promote an existing user to an admin role, you can run the following via Tinker:

```bash
./vendor/bin/sail artisan tinker --execute="App\Models\User::where('username','admin')->update(['is_admin'=>true]);"
```

## Demo Credentials

All seeded accounts have the password: `password`.
*Note: Since Usernames and Display Names are randomly generated using Faker for realism, please login using their fixed **Email** addresses below.*

| Email                     | Role(s)                | Notes                                                                                                   |
| ------------------------- | ---------------------- | ------------------------------------------------------------------------------------------------------- |
| `admin@seapedia.test`     | Admin (`is_admin`)     | Admin monitoring dashboard, voucher/promo management, overdue time machine.                             |
| `seller1@seapedia.test`   | Seller                 | Store "Toko Berkah" (3 products). Other sellers: `seller2@seapedia.test`..`seller7@seapedia.test`.      |
| `buyer1@seapedia.test`    | Buyer                  | Wallet balance Rp 500.000, 1 address, active orders.                                                    |
| `driver1@seapedia.test`   | Driver                 | 1 active job, 2 completed jobs.                                                                         |
| `multi1@seapedia.test`    | Buyer, Seller, Driver  | Store "Warung Mama Lia" (3 products), Wallet balance Rp 300.000.                                        |

## Core Business Rules & Features

- **Single-Store Checkout**: Buyers can only check out items from a single store at a time per cart. Adding an item from a different store will prompt the buyer to clear their current cart.
- **PPN 12% Calculation**: Tax is applied dynamically based on the subtotal *after* discounts have been applied. Delivery fees are exempt from PPN.
- **Delivery Fee (base + distance + weight)**: `delivery_fee = base(method) + billable_km × rate(method) + weight_fee`. Each method has a base fee (Instan Rp20.000 / Besok Rp10.000 / Reguler Rp5.000) and a per-km rate (Rp2.500 / Rp1.500 / Rp1.000). Distance is the Haversine km between the store's origin and the buyer's address — both pinned on a Leaflet/OpenStreetMap map (no paid API key) — billed up to an 80 km cap. Weight adds Rp2.000 per kilogram over the first free kilogram (each product/variant has a mandatory weight). The spec only requires the fee to differ per method (line 278); distance and weight are our documented, spec-legal extension. The fee is never taxed, is computed identically in preview and commit (quote equals charge), and falls back to the base fee when a coordinate or weight is missing. The driver's own distance never affects the buyer's fee (no driver is assigned at checkout); it only sorts delivery jobs.
- **Unified Wallet System**: Buyers, sellers, and drivers share a unified wallet system. All transactions are securely recorded as immutable ledger entries within a database transaction. A single top-up is bounded to **Rp5.000–Rp100.000.000** (the spec leaves top-up amounts open; these are our documented limits).
- **Voucher & Promo Constraints**: Vouchers and Promos can be applied simultaneously. They feature minimum spend limits, maximum discount caps, and usage limits. Expired or exhausted codes are instantly rejected.
- **Overdue Refund / Time Machine**: Admins can advance the system time to test SLA due dates. Overdue orders that have not been delivered are automatically refunded to the buyer's wallet without duplicating refunds or reversing seller income (as seller income is held in escrow until delivery is complete).
- **Delivery SLA & Near-Cancel Urgency**: The spec requires defining SLA rules per method (line 454); each order carries an `sla_due_at` deadline derived from its delivery method (Instan 1 day, Besok 2 days, Reguler 4 days, in simulated day-ticks). Seller order lists and the driver dashboard classify each open order server-side into *Overdue* (past due), *Critical* (≤ 1 day-tick left), or *Normal*, float at-risk orders to the top, and surface the urgency as a read-only badge. The threshold lives entirely in the backend; the UI only formats it.
- **Multi-Role Self-Service**: The spec lets one non-admin username own Buyer, Seller, and Driver simultaneously and choose an active role per session (lines 38–39). From their profile, users can add a role they don't yet have or remove one they do. Removal is guarded — a Seller with a live store or a Driver with active deliveries is hard-blocked (422) rather than silently orphaning dependent records.
- **Driver Reliability Tiers**: The spec is silent on how many jobs a driver may hold at once, so we cap concurrency and let it grow with proven reliability (a documented, spec-legal extension). A driver holds **1** active job by default, **2** after **15** on-time completions, and **3** after **30**. "On time" means confirmed on or before the order's `sla_due_at`. The check runs inside a row-locked transaction so two concurrent claims can't both slip past the cap.

## API Documentation

API endpoints are fully documented using Swagger/OpenAPI.
Once the application is running, you can access the Swagger UI at:
**`http://localhost/api/documentation`**

It details 33 endpoints across 13 tags including Catalog, Buyer Wallet, Checkout, Promo/Vouchers, and Admin functionalities.

## Security Notes

SEAPEDIA implements several robust security measures to protect the platform and its users:

- **SQL Injection Prevention**: The application exclusively uses Laravel's Eloquent ORM and Query Builder, which utilize PDO parameter binding to prevent SQL injection attacks.
- **Cross-Site Scripting (XSS)**: Inertia.js and Vue 3 automatically escape all user input when rendering templates. Any raw HTML rendering is strictly avoided or explicitly sanitized.
- **Input Validation**: All incoming requests (both Web and API) are validated using Laravel Form Requests. Strong typing and strict rules are enforced before data reaches the controllers.
- **Session & CSRF Behavior**: Web routes are protected by Laravel's built-in CSRF protection (`VerifyCsrfToken`). API routes (`/api/v1/*`) are protected by Laravel Sanctum token-based authentication with Bearer tokens.
- **Role-Based Access Control (RBAC)**: 
  - Policies (e.g., `ProductPolicy`, `OrderPolicy`, `DeliveryPolicy`) strictly enforce ownership and role scopes.
  - Middlewares (`is_admin`, `active_role`) restrict access to role-specific dashboard routes and endpoints.
  - Sensitive operations (checkout, wallet debits) run entirely within `DB::transaction()` with `lockForUpdate()` to prevent race conditions (e.g., double refunds, double job claims).
- **Account Deletion & Anonymization**: When users delete their account, their PII (Personally Identifiable Information) such as Name and Email are anonymized to comply with data privacy standards, and the account is soft-deleted to maintain database integrity for historical transactions. Additionally, per-role removal is guarded (e.g. drivers cannot resign if they have active deliveries).
- **Secured Avatar Upload**: Avatar uploads are constrained by a Form Request (`jpeg/jpg/png/webp`, ≤ 2 MB, ≤ 2000×2000 px) and then **re-encoded through GD into a fresh PNG** before storage. Re-encoding discards any malicious payload (EXIF metadata, polyglot files, embedded scripts) that might survive a MIME check, so only clean pixel data is ever written to disk.

## Formatting and Testing

To run tests and code formatters:
```bash
./vendor/bin/sail artisan test
./vendor/bin/sail pint
./vendor/bin/sail npm run lint
./vendor/bin/sail npm run format
```

## Engineering Decisions

### Region Selector (Address Management)
We opted to use the public EMSIFA API (https://www.emsifa.com/api-wilayah-indonesia/) for retrieving Indonesia's Province, Regency, District, and Village data dynamically instead of seeding ~80,000+ regions into our local database. 
This decision was made to:
- Avoid excessively long database seeding times during judging/evaluation (using php artisan migrate --seed).
- Keep the database schema clean and lightweight, optimizing for core e-commerce transactions rather than static data storage.
- Enhance the user experience with cascading dropdowns that fetch region data asynchronously.
