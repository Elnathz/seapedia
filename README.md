# SEAPEDIA

A multi-role campus marketplace — buyers, sellers, and drivers share one
platform and one wallet, with roles switchable per session. Built with
Laravel 11, Inertia + Vue 3 (TypeScript), shadcn-vue, Tailwind 4, and MySQL,
running on Docker via Laravel Sail.

Full product/technical decisions live in `docs/SEAPEDIA_TDD.md`. Plans and
progress are tracked under `docs/planning/`.

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

3. **Start the Docker Containers**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Initialize the Application**
   Run the following commands to set up the application key, database, and storage:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate:fresh --seed
   ./vendor/bin/sail artisan storage:link
   ```
   *(Note: `storage:link` is crucial for seeded and uploaded product images to load correctly).*

5. **Install Frontend Dependencies & Build**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

6. **Access the App**
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
- **Delivery Fee (base + region surcharge)**: Each method has a base fee (Instan Rp20.000 / Besok Rp10.000 / Reguler Rp5.000) plus a region-tier surcharge based on how far the buyer's address is from the store's origin — same kelurahan Rp0, kecamatan Rp2.000, kota Rp5.000, provinsi Rp10.000, antar-provinsi Rp20.000 (the spec only requires the fee to differ per method; the surcharge is our documented, spec-legal extension). The surcharge is never taxed and the preview always equals the amount charged. Stores without a recorded origin fall back to base fee only.
- **Unified Wallet System**: Buyers, sellers, and drivers share a unified wallet system. All transactions are securely recorded as immutable ledger entries within a database transaction. A single top-up is bounded to **Rp5.000–Rp100.000.000** (the spec leaves top-up amounts open; these are our documented limits).
- **Voucher & Promo Constraints**: Vouchers and Promos can be applied simultaneously. They feature minimum spend limits, maximum discount caps, and usage limits. Expired or exhausted codes are instantly rejected.
- **Overdue Refund / Time Machine**: Admins can advance the system time to test SLA due dates. Overdue orders that have not been delivered are automatically refunded to the buyer's wallet without duplicating refunds or reversing seller income (as seller income is held in escrow until delivery is complete).

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
