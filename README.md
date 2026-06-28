# SEAPEDIA

A multi-role campus marketplace — buyers, sellers, and drivers share one
platform and one wallet, with roles switchable per session. Built with
Laravel 13, Inertia + Vue 3 (TypeScript), shadcn-vue, Tailwind 4, and MySQL,
running on Docker via Laravel Sail.

Full product/technical decisions live in `docs/SEAPEDIA_TDD.md`. Plans and
progress are tracked under `docs/planning/`.

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

## Product categories

Products are organized into a 2-level hierarchy (parent → child). Admin manages categories; sellers pick from the list when creating products.

| Parent | Children |
| ------ | -------- |
| Makanan | Makanan Berat, Cemilan |
| Minuman | Kopi, Teh, Jus |
| Elektronik | Aksesori HP, Audio |
| Fashion | Pria, Wanita |
| Kebutuhan Harian | Sembako, Perawatan |
| Lainnya | Umum |

Filtering by a parent category shows all its children. Category chips appear on the landing page and catalog filter bar.

## Demo credentials

Seeded by `migrate:fresh --seed`. Every account's password is `password`.

| Username   | Role(s)              | Notes                                                                                                   |
| ---------- | --------------------- | -------------------------------------------------------------------------------------------------------- |
| `admin`    | Admin (`is_admin`)    | Admin monitoring dashboard: user counts, overdue sweep, promo/voucher management                           |
| `seller1`  | Seller                | Store "Toko Berkah" (3 products). Also: seller2..seller7 (masing-masing 4-8 produk)                   |
| `buyer1`   | Buyer                 | Dompet Rp 500.000, 1 alamat, beberapa order lintas status (Sedang Dikemas/Menunggu/Sedang Dikirim/Selesai) |
| `driver1`  | Driver                | 1 job aktif (Sedang Dikirim) + 2 selesai (earnings terlihat)                                           |
| `multi1`   | Buyer, Seller, Driver  | Store "Warung Mama Lia" (3 produk). Dompet Rp 300.000                                                    |
| `driver2`  | Driver                | 2 job selesai (earnings terlihat)                                                                        |

### Demo discount codes (`DiscountSeeder`)

| Code           | Kind  | Effect                            | Notes                             |
| ------------- | ------ | --------------------------------- | --------------------------------- |
| `HEMAT10`     | Voucher | 10% off, capped at Rp 20.000      | Usage limit 5, no min spend      |
| `HEMAT50`     | Promo  | Rp 5.000 off                       | No min spend                      |
| `PROMO20K`    | Promo  | Rp 20.000 off                     | Requires min spend Rp 100.000     |
| `EXPIRED5K`   | Voucher | Rp 5.000 off                       | Expired — always rejected         |
| `EXPIREDPROMO`| Promo  | 15% off, capped at Rp 30.000      | Expired — always rejected          |
| `HABIS`        | Voucher | Rp 15.000 off                      | Usage exhausted — always rejected  |
| `NONAKTIF`     | Voucher | 20% off                            | Inactive — always rejected         |

## Demo path (Level 1 — auth, catalog, reviews)

1. Guest: landing (`/`) → catalog (`/catalog`) → product detail → submit a
   review (`/reviews`).
2. Log in as `multi1` → pick a role from the modal → land on that role's
   dashboard → switch role from the sidebar badge.
3. Log in as `seller1` or `driver1` → single role, no modal, straight to
   the dashboard shell.
4. Log in as `admin` → dashboard shows live platform counts (users per
   role); admin tooling for promo/voucher management.

## Demo path (Level 2 — stores, products)

1. Log in as `seller1` → sidebar "Toko Saya" shows the seeded store; "Produk"
   shows its 3 seeded products (card grid, Edit/Delete).
2. Create a new product with an image upload → it appears in the seller
   list and in the public catalog (`/catalog`) immediately.
3. Guest: catalog → search by name / filter by category → open a product →
   see the store info block → open the store's public page (`/stores/{slug}`)
   → see only that store's active products with gradient avatar and meta.
4. Log in as `multi1` (switch to seller) → try opening `seller1`'s product
   edit URL directly by id → **403** (cross-seller ownership enforced by
   `ProductPolicy`).
5. Toggle language **ID / EN** from the navbar or Settings → the whole UI
   flips instantly, no reload; a logged-in user's choice persists after
   logout/login.
6. `GET /api/v1/catalog` mirrors the same active-only data; Swagger UI at
   `/api/documentation` lists the `Catalog` endpoints.

## Demo path (Level 3 — wallet, cart, checkout)

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
   Besok Sampai / Reguler) → the summary ledger shows subtotal, discount,
   delivery fee, and **PPN 12%** → confirm → wallet debited, stock reduced,
   order created **Sedang Dikemas**, cart cleared.
5. "Pesanan Saya" lists it with the money breakdown and status timeline.
   Switch to `seller1` → "Pesanan Masuk" shows the same order (read-only).
6. Lower the wallet balance below the order total and try checkout again →
   the "Bayar Sekarang" button is disabled with a clear reason.
7. `/api/v1/buyer/*` mirrors the whole flow (Sanctum, role-scoped token);
   Swagger UI lists the `Buyer Wallet`, `Buyer Addresses`, `Buyer Cart`,
   `Checkout`, and `Buyer Orders` tags.

## Demo path (Level 3)

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
   delivery fee, and **PPN 12%** → confirm → wallet
   debited, stock reduced, order created **Sedang Dikemas**, cart cleared.
5. "Pesanan Saya" lists it with the money breakdown and status timeline.
   Switch to `seller1` → "Pesanan Masuk" shows the same order (read-only).
6. Lower the wallet balance below the order total and try checkout again →
   top-ups elsewhere) and try checkout again → the "Bayar Sekarang" button
   is disabled with a clear reason and a "Top up dompet" recovery link.
7. `/api/v1/buyer/*` mirrors the whole flow (Sanctum, role-scoped token);
   Swagger UI lists the `Buyer Wallet`, `Buyer Addresses`, `Buyer Cart`,
   `Checkout`, and `Buyer Orders` tags.

### Locked rules this level depends on

- **Single-store cart (§5.8):** one cart per buyer, locked to whichever
  store the first item came from. Adding a product from another store is
  rejected (422) with a "Clear & add" recovery action.
- **PPN 12% base (§5.2):** `taxable_base = subtotal − discount_total`;
  `tax_amount = round(taxable_base × 0.12)`. Delivery fee is **not** taxed.
  `grand_total = taxable_base + tax_amount + delivery_fee`.
- **Unified wallet model (§5.1b):** every buyer/seller/driver has exactly
  one wallet; every movement is an immutable `wallet_transactions` row
  (`type` + `direction` + `balance_after`) written inside a locked
  transaction — never a raw balance write.
- **Fake top-up gateway (§9):** `PAYMENT_GATEWAY=fake` (default) credits
  the wallet instantly via `FakeGateway` — no external dependencies needed.
  `IpaymuGateway` is scaffolded behind the same `PaymentGateway` interface.

## Demo path (Level 4 — discount, seller process)

1. Log in as `buyer1` → add enough units to clear Rp 100.000 → checkout →
   enter `PROMO20K` in **Kode Promo** and `HEMAT10` in **Kode Voucher**,
   apply each → distinct **Diskon Promo** and **Diskon Voucher** lines,
   **PPN (12%)** recomputes on the discounted subtotal → pay.
2. Try `EXPIRED5K` or `EXPIREDPROMO` → inline error under the field.
   Try `PROMO20K` with a cart under Rp 100.000 → min-spend error.
   Try `HABIS` (usage exhausted) → rejected.
3. Switch to `seller1` → "Pesanan Masuk" → open the buyer's order →
   **"Proses Pesanan"** (confirm dialog) → status moves to **Menunggu
   Pengirim**, timeline updates on both pages. Re-opening shows no button
   (only `Sedang Dikemas` orders can be processed).
4. "Laporan" (sidebar, both roles) → `buyer1` sees total spent + per-status
   breakdown; `seller1` sees total income split into incoming vs. processed.
5. `/api/v1/admin/{promos,vouchers}` generate/list codes;
   `/api/v1/buyer/reports` and `/api/v1/seller/reports` mirror the reports.

### Locked rules this level depends on

- **Discount combination (§5.3):** one Promo **and** one Voucher may
  combine; each evaluated independently against the original subtotal,
  `discount_total = min(promo_amount + voucher_amount, subtotal)`. Expired
  codes, usage-exhausted voucher, or unmet min-spend rejected with specific,
  per-field message.
- **Discount before PPN (§5.2):** `taxable_base = subtotal − discount_total`;
  `tax_amount = round(taxable_base × 0.12)`. Delivery fee is still not taxed.
- **Voucher usage locked at commit (§6):** `used_count` incremented inside the
  same locked transaction as stock/wallet movements, with re-check after lock —
  two concurrent checkouts on a voucher's last use cannot both succeed.
  `used_count` is **not** restored on a later overdue refund.

## Demo path (Level 5 — delivery, escrow, overdue)

1. Log in as `driver1` → "Pesanan Tersedia" lists available orders with
   **earning preview** (80% of delivery fee) → **"Ambil Pesanan"** → moves to
   **Sedang Dikirim**; "Selesaikan Pengiriman" → **Pesanan Selesai**, earnings
   credited, seller's income released from escrow.
2. Race `driver1` and `driver2` for the same job → exactly one succeeds; loser
   sees "sudah diambil kurir lain" toast. Driver who already holds an active
   job gets `422` on a second take.
3. As `admin` → "Majukan Hari" → seeded overdue order auto-refunds: wallet
   credited, stock restored, status becomes **Dikembalikan**. Idempotent on
   re-run; `php artisan seapedia:advance-day` does the same headlessly.
4. Admin dashboard: resource counts (users by role, stores, products, orders
   by status, overdue-eligible count) reflect seeded data live. Promo/Voucher
   management: list with status badges, create, toggle active.
5. `buyer1`'s and `seller1`'s reports exclude refunded orders.
6. `/api/v1/driver/*`, `/api/v1/admin/clock/advance`,
   `/api/v1/admin/dashboard` mirror the web flows above.

### Locked rules this level depends on

- **Escrow payment model:** buyer debited full `grand_total` at checkout,
  seller **not** credited there. `order.seller_income_amount` (taxable_base)
  released to seller inside `DeliveryService::complete()`. Refunded orders never
  paid the seller — `OverdueService::sweep()` needs **no seller reversal**.
- **Driver earning 80% of delivery fee:** `intdiv(delivery_fee * 80, 100)`,
  credited in the same locked transaction as seller's income release.
- **One active delivery per driver:** `DeliveryService::take()` rejects (`422`)
  a driver who already holds a `taken` delivery, checked inside the same
  locked transaction that claims the job.
- **Delivery SLA + time simulation (§5.7):** `sla_due_at` stamped at checkout;
  `ClockService::advance(1)` moves simulated time and runs `OverdueService::sweep()`.
  No background scheduler — manual/command trigger only.
- **Overdue auto-refund idempotent (§5.9):** selects non-final orders past
  `sla_due_at` with `refunded_at IS NULL`; each refunded inside its own locked
  transaction that re-checks after the lock — no double-refund possible.
  Voucher `used_count` **not** restored on refund (consumed at apply time).
- **Reports exclude refunded orders:** buyer spending drops `Dikembalikan` orders;
  seller income based on `Pesanan Selesai` orders only.

## Demo path (Level 6 — landing, auth, API)

1. Open `/` — landing page: promo bar (gradient), asymmetric split-hero,
   trust band, category grid (animated), role cards, popular stores section.
2. Click **"Masuk"** → auth layout with teal brand panel (desktop); log in
   as `buyer1` → dashboard directly; log in as `multi1` → role-select
   screen with three branded cards.
3. All UI strings are in Indonesian; page titles and labels are localised.
4. Bottom navigation (mobile): role-aware tabs for guest/buyer/seller/driver/admin.
5. Open `/api/documentation` (Swagger UI) → **SEAPEDIA API** spec documents
   33 endpoints across 13 tags. Try key flows directly in Swagger:
   `GET /api/v1/catalog` (public) · `POST /api/v1/buyer/wallet/topup` (buyer token)
   · `POST /api/v1/admin/clock/advance` (admin token).

### Security notes (OWASP audit — Level 6)

| Finding | Status |
| ------- | ------ |
| **API1 BOLA** — `DeliveryPolicy::view()` enforces driver ownership (Level 5 fix, regression-tested) | ✅ Fixed |
| **API2 Auth** — Sanctum Bearer tokens; `throttle:login` on `/api/v1/login`; role-scoped tokens via `POST /role/select`; logout revokes the current token | ✅ |
| **API3 Object property exposure** — `CatalogService`, `StoreService`, `AppReviewService` column-scope their responses; no `user_id`/`stock` leaked on public endpoints | ✅ |
| **API4 Unrestricted consumption** — `?q` search capped at `max:200` (runtime + spec); `page` min:1; `throttle:login` | ✅ |
| **API5 Function-level auth** — `is_admin` middleware on all `/admin/*` routes; `active_role:buyer/seller/driver` on role-scoped prefixes | ✅ |
| **API6 Sensitive flows** — checkout, wallet debit, and delivery take all run inside `DB::transaction` + `lockForUpdate()`; idempotency sentinels prevent double-refund / double-take | ✅ |
| **API8 Misconfiguration** — `securitySchemes` (Sanctum Bearer) and `servers` block added to the OpenAPI spec | ✅ Fixed in Level 6 |
| **API9 Inventory** — all 33 `/api/v1` routes are documented; no shadow/undocumented endpoints | ✅ |
| **Top-up gateway** — iPaymu v2 scaffolded behind `PaymentGateway` interface; `PAYMENT_GATEWAY=fake` (default) runs `FakeGateway` — no external callback, no webhook. Real iPaymu path removed; dummy top-up reads as a gateway flow. | Accepted scope |

### Creating the admin account

The `admin` seed user is created automatically by `DatabaseSeeder` via the role
seeder. If you ever need to promote an existing user manually:

```bash
./vendor/bin/sail artisan tinker --execute="App\Models\User::where('username','admin')->update(['is_admin'=>true]);"
```

### API token expiry

Sanctum tokens expire after **480 minutes** by default
(`SANCTUM_TOKEN_EXPIRATION=480` in `.env`, read by `config/sanctum.php`).
Adjust the env variable if you need longer-lived tokens for demo sessions.

## Production deploy (Depacloud VPS)

**Live URL: https://seapedia.web.id** (IP: `103.253.244.92`)

The `docker-compose.prod.yml` runs three services: `app` (php-fpm), `web`
(nginx with baked static files), `db` (MySQL 8 with named volume). Docker
is required on the host — no other runtime dependency.

### One-time server setup

```bash
# 1. Install Docker
curl -fsSL https://get.docker.com | sh

# 2. Clone the repo
git clone https://github.com/Elnathz/seapedia.git && cd seapedia

# 3. Copy and fill in the prod env
cp .env.example .env
# Edit .env: APP_KEY, APP_URL=http://<IP>, DB_PASSWORD, APP_DEBUG=false

# 4. Generate APP_KEY (requires PHP; easiest via Docker)
docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
# → paste the output as APP_KEY=... in .env

# 5. Build images and start
docker compose -f docker-compose.prod.yml up -d --build

# 6. Migrate + seed
#    DB::prohibitDestructiveCommands blocks migrate:fresh in production, so
#    we clear the config cache, override APP_ENV, then re-cache after seeding.
docker compose -f docker-compose.prod.yml exec app php artisan config:clear
docker compose -f docker-compose.prod.yml exec -e APP_ENV=local app \
    php artisan migrate:fresh --seed --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache

# 7. Create storage symlink (needed for product images)
docker compose -f docker-compose.prod.yml exec app php artisan storage:link

# 8. Open http://<PUBLIC_IP>
```

### HTTPS via Let's Encrypt (Certbot)

```bash
# Stop nginx to free port 80 for the ACME challenge
docker compose -f docker-compose.prod.yml stop web
certbot certonly --standalone -d seapedia.web.id --agree-tos -m your@email.com --non-interactive
docker compose -f docker-compose.prod.yml up -d
```

Certs are stored on the host at `/etc/letsencrypt/live/<domain>/` and mounted read-only into
the `web` container. Certbot installs an automatic renewal cron — no manual renewal needed.

### Re-deploy after a git pull

```bash
git pull
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

## Current status

All Level 1-6 features implemented: multi-role auth, stores/products, wallet/cart/checkout (PPN 12%, escrow), discount codes, delivery/driver, admin monitoring, overdue auto-refund, adaptive crop (produk 1:1, banner 2.5:1/3:2), all-condition seeders (7 stores, 3-8 produk each, 5 order statuses). OWASP audit clean, 197 tests green, OpenAPI spec (33 endpoints), production Docker deploy.

**Live at https://seapedia.web.id**

Plan and progress docs live under `docs/planning/`.

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
