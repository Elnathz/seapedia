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
`DiscountSeeder` + `BuyerDemoSeeder` + `DeliverySeeder`). Every account's
password is `password`.

| Username  | Role(s)                | Notes                                   |
| --------- | ----------------------- | ---------------------------------------- |
| `admin`   | Admin (`is_admin`)      | Lands on the admin monitoring dashboard — live resource counts, the "advance simulated day" control, and links to promo/voucher management |
| `seller1` | Seller                  | Single role — skips the role-select step. Owns store "Toko Berkah" (3 products, seeded images). Has one incoming order from `buyer1` (**Sedang Dikemas**, discounted with PROMO20K + HEMAT10), one already processed into an **available delivery** a driver can take, and one **overdue-eligible** order (`sla_due_at` already in the past) |
| `buyer1`  | Buyer                   | Wallet topped up via `TopupService` (Rp 500.000, with a real ledger entry); one saved address; three placed orders against "Toko Berkah" (see `seller1`'s row) |
| `driver1` | Driver                  | Single role — skips the role-select step. Sees the takeable job seeded above under "Pesanan Tersedia" |
| `multi1`  | Buyer, Seller, Driver   | Multi-role — shows the role-select modal on login. Owns store "Warung Mama Lia" (3 products, seeded images). Wallet topped up (Rp 300.000) and one saved address as the buyer role |

### Demo discount codes (`DiscountSeeder`)

| Code           | Kind    | Effect                                   | Notes                              |
| -------------- | ------- | ----------------------------------------- | ----------------------------------- |
| `HEMAT10`      | Voucher | 10% off, capped at Rp 20.000              | Usage limit 5, no min spend         |
| `PROMO20K`     | Promo   | Rp 20.000 off                             | Requires min spend Rp 100.000       |
| `EXPIRED5K`    | Voucher | Rp 5.000 off                               | Expired yesterday — always rejected |
| `EXPIREDPROMO` | Promo   | 15% off, capped at Rp 30.000              | Expired yesterday — always rejected |

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
  transaction — never a raw balance write. *(Superseded by Sprint 5: seller
  income no longer settles at checkout — see the escrow model under
  "Demo path (Sprint 5)" below. This sprint's checkout still debits the
  buyer the same way; only the seller-credit timing changed.)*
- **Fake top-up gateway (§9):** `PAYMENT_GATEWAY=fake` (the default) credits
  the wallet instantly via `FakeGateway`, so the demo never depends on
  iPaymu uptime. `IpaymuGateway` is scaffolded behind the same
  `PaymentGateway` interface; its real v2 call ships in Sprint 6. No
  minimum top-up amount is locked by the TDD — 10,000 IDR
  (`config('payment.topup.min_amount')`) was picked as the simplest
  reasonable floor.

## Demo path (Sprint 4)

1. Log in as `buyer1` → add a few units of "Kopi Susu Gula Aren" to the
   cart (enough to clear Rp 100.000) → "Lanjut ke Checkout" → enter
   `PROMO20K` in **Kode Promo** and `HEMAT10` in **Kode Voucher**, apply
   each → the summary shows a distinct **Diskon Promo** and **Diskon
   Voucher** line, and **PPN (12%)** recomputes on the discounted subtotal
   → pay → order created with both codes recorded.
2. Try `EXPIRED5K` or `EXPIREDPROMO` → inline error under the field, total
   unchanged. Try `PROMO20K` with a cart under Rp 100.000 → inline
   min-spend error.
3. Switch to `seller1` → "Pesanan Masuk" → open the buyer's order →
   **"Proses Pesanan"** (confirm dialog) → status moves to **Menunggu
   Pengirim**, the timeline updates on both the seller and the buyer's own
   order detail page. Re-opening the same order no longer shows the button
   (only `Sedang Dikemas` orders can be processed); a different seller's
   account gets a 403 on the same URL.
4. "Laporan" (sidebar, both roles) → `buyer1` sees total spent + order
   count + a per-status breakdown; `seller1` sees total income split into
   incoming vs. processed orders — both reconcile with the seeded order's
   numbers above.
5. `/api/v1/admin/{promos,vouchers}` (admin token) generate/list/view a
   code; `/api/v1/buyer/checkout/preview` accepts `promo_code` /
   `voucher_code`; `/api/v1/seller/orders/{order}/process` mirrors the
   process action; `/api/v1/buyer/reports` and `/api/v1/seller/reports`
   mirror the reports — all listed in Swagger UI at `/api/documentation`.

### Locked rules this sprint depends on

- **Discount combination (§5.3):** one Promo **and** one Voucher may
  combine; each is evaluated independently against the **original**
  subtotal, then `discount_total = min(promo_amount + voucher_amount,
  subtotal)` — never stacked sequentially, never more than the subtotal.
  Expired codes, a voucher with no remaining usage, or an unmet min-spend
  are all rejected with a specific, per-field message.
- **Discount before PPN (§5.2):** `taxable_base = subtotal −
  discount_total`; `tax_amount = round(taxable_base × 0.12)`. The discount
  reduces the PPN base, not just the final total. Delivery fee is still
  not taxed and the math is otherwise unchanged from Sprint 3.
- **Voucher usage is locked, not just checked (§6):** `used_count` is
  incremented inside the same `DB::transaction` as the stock/wallet
  movements, under `lockForUpdate()` on the voucher row, with the
  remaining-usage check re-run after the lock — two concurrent checkouts
  on a voucher's last use can never both succeed. **`used_count` is not
  restored on a later overdue refund** — a deliberate Sprint 5 carry-over,
  not an oversight.
- **Processing is the only seller-initiated transition this sprint
  (§5.6):** `Sedang Dikemas → Menunggu Pengirim`, gated by `OrderPolicy`
  (owning seller only) and run through `OrderService::transition()`'s
  existing locked table — the same one used for every other status
  change, so a history row is always written and an invalid move (e.g.
  processing an already-processed order) is rejected, never silently
  ignored.
- **Reports were unfiltered totals as of Sprint 4** — no `Dikembalikan`
  (overdue-refund) orders existed yet, so buyer spending summed
  `grand_total` and seller income summed `seller_income_amount` across
  every order unconditionally. *(Superseded by Sprint 5 — see "Demo path
  (Sprint 5)" below: both reports now exclude `Dikembalikan` orders, and
  seller income is based on `Pesanan Selesai` orders only.)*

## Demo path (Sprint 5)

1. Log in as `driver1` → "Pesanan Tersedia" lists the seeded `seller1`
   order (already processed by the seller) with an **earning preview**
   (80% of the delivery fee, shown before taking) → open it → **"Ambil
   Pesanan"** (confirm dialog) → order moves to **Sedang Dikirim**; the
   driver dashboard now shows it as the active job. Open it again →
   **"Selesaikan Pengiriman"** (confirm) → order moves to **Pesanan
   Selesai**; the driver's total earnings rise by the 80% share, and
   (switch to `seller1`) the seller's wallet is credited
   `seller_income_amount` **only now** — not at checkout. Switch back to
   `buyer1`'s order detail page to see the same driver name + delivery
   status on the timeline.
2. Seed a second driver (or reuse `multi1` in the driver role) and race
   both for the same available job → exactly one "Ambil Pesanan" succeeds;
   the loser is redirected with a "sudah diambil kurir lain" toast (web) or
   a literal `409` (`/api/v1/driver/jobs/{id}/take`). A driver who already
   holds an active job gets a `422` on a second take, both web and API.
3. Log in as `admin` → the dashboard's "Tanggal Simulasi" card shows the
   current simulated day → **"Majukan Hari"** (confirm dialog) → the
   seeded **overdue** order (its `sla_due_at` is already in the past)
   auto-refunds: `buyer1`'s wallet shows a `refund` of the order's
   `grand_total`, the product's stock is restored, the order's status
   becomes **Dikembalikan** with a history row — and `seller1`'s balance
   does **not** move (it was never paid for an order that never
   completed). Click "Majukan Hari" again → nothing changes (idempotent —
   `refunded_at` is already set). `php artisan seapedia:advance-day` does
   the same headlessly.
4. Still as `admin` → the dashboard's resource-count cards (users by role,
   stores, products, orders by status, deliveries by status, overdue-
   eligible count) reflect the seeded + just-refunded data live; the
   Promo/Voucher summary cards link to **"Kelola Promo"** / **"Kelola
   Voucher"** → list with active/expired/used-up badges → create a new
   code via the dialog (inline validation errors on a bad input) →
   toggle one's active state → the badge updates immediately.
5. `buyer1`'s and `seller1`'s reports (`/buyer/reports`, `/seller/reports`)
   no longer count the just-refunded order in their totals.
6. `/api/v1/driver/*`, `/api/v1/admin/clock/advance`, and
   `/api/v1/admin/dashboard` mirror the web flows above — all listed in
   Swagger UI at `/api/documentation`.

### Locked rules this sprint depends on

- **Escrow payment model (Decision 3, this sprint's headline change):** the
  buyer is still debited the full `grand_total` at checkout, but the seller
  is **not** credited there anymore. `order.seller_income_amount` is
  computed at checkout and held — released to the seller's wallet only
  inside `DeliveryService::complete()`, at the same moment the order
  reaches **Pesanan Selesai**. An order that's refunded instead of
  completed never paid the seller, so `OverdueService::sweep()` needs **no
  seller reversal** — there is nothing to claw back. There is no separate
  "platform wallet" entity; between checkout and completion the buyer's
  payment is simply debited and not credited anywhere. Money still
  balances: completion always pays out seller + driver from the same
  `grand_total` the buyer was debited, and a refund always returns the
  full `grand_total` to the buyer — the platform's retained cut (tax + the
  driver's 20%) is implicit, never materialized as a ledger row.
- **Driver earning is 80% of the delivery fee, floored (§5.5):**
  `earning_amount = intdiv(delivery_fee * 80, 100)`, computed once at
  `DeliveryService::complete()` and credited to the driver's wallet
  (`type: earning`) in the same locked transaction as the seller's income
  release. The three locked delivery fees (Rp 5.000 / 10.000 / 20.000)
  divide evenly by 5, so the floor never actually trims anything — it's
  there for integer-IDR safety, not because rounding is observed in
  practice.
- **One active delivery per driver:** `DeliveryService::take()` rejects
  (`422`) a driver who already holds a `taken` delivery, checked inside the
  same locked transaction that claims the job — a driver must complete (or
  never take) their current job before taking another.
- **Delivery SLA + time simulation (§5.7):** every order's `sla_due_at` is
  stamped at checkout (`created_sim_at + slaTicks(delivery_method)` days).
  Nothing in business logic ever calls `now()`/`Carbon::now()` directly —
  `ClockService::now()` is the only clock, reading `settings.simulated_now`
  (falling back to real time until an admin first advances it).
  `ClockService::advance(1)` moves that setting forward one day; both
  `POST /admin/clock/advance` and `php artisan seapedia:advance-day` call
  it and then immediately run `OverdueService::sweep()` — there is no
  background scheduler, by design (the brief accepts a manual/command
  trigger).
- **Overdue auto-refund is idempotent (§5.9, §6):** `sweep()` selects
  non-final orders (`Sedang Dikemas` / `Menunggu Pengirim` / `Sedang
  Dikirim`) past their `sla_due_at` with `refunded_at IS NULL`; each is
  refunded inside its own locked transaction that re-checks `refunded_at`
  after acquiring the lock, so a re-run (or a race with a driver completing
  the same order) can never double-refund or double-restore stock. Voucher
  `used_count` is **not** restored on refund — an explicit Sprint 4
  carry-over decision, not an oversight (a voucher use is consumed the
  moment it's applied, regardless of what happens to the order later).
- **Reports now exclude refunded orders:** `ReportService::buyerSpending()`
  drops `Dikembalikan` orders from both the total and the per-status
  breakdown; `ReportService::sellerIncome()` additionally bases
  `total_income` (and its breakdown) on `Pesanan Selesai` orders only,
  consistent with the escrow model above — an order that hasn't paid the
  seller yet shouldn't count as income yet.

## Demo path (Sprint 6)

1. Open `/` — landing page: asymmetric split-hero ("Satu akun. Tiga peran."),
   trust band (PPN / escrow / kurir), featured product strip, role trio cards,
   and a public review form at the bottom. Submit a review as a guest.
2. Click **"Masuk"** in the navbar → the auth layout shows a teal brand panel
   on the left (desktop); log in as `buyer1` → hits the dashboard directly
   (single-buy role); log in as `multi1` → **role-select screen** shows three
   branded cards (Pembeli / Penjual / Kurir).
3. All UI strings are in Indonesian; page titles, breadcrumbs, and role labels
   are fully localised.
4. Open `/api/documentation` (Swagger UI, served by `darkaonline/l5-swagger`)
   → the **SEAPEDIA API** spec documents 33 endpoints across 13 tags.
   Expand **Auth → POST /api/v1/login** → try it out → enter `buyer1 /
   password` → copy the returned `token` → click **Authorize** (top-right)
   → paste the token → now every protected endpoint sends `Bearer <token>`.
5. Exercise the key flows directly in Swagger UI:
   - `GET /api/v1/catalog` — public, no auth needed.
   - `POST /api/v1/buyer/wallet/topup` (buyer token) → body `{"amount": 50000}`
     → 201 with a pending topup; `GET /api/v1/buyer/wallet/topup/{id}` polls it.
   - `POST /api/v1/buyer/checkout/preview` → shows the full §5.2 money breakdown.
   - `POST /api/v1/admin/clock/advance` (admin token) → advances the simulated
     day and returns `refunded_count`.

### Security notes (OWASP audit — Sprint 6)

| Finding | Status |
| ------- | ------ |
| **API1 BOLA** — `DeliveryPolicy::view()` enforces driver ownership (Sprint 5 fix, regression-tested) | ✅ Fixed |
| **API2 Auth** — Sanctum Bearer tokens; `throttle:login` on `/api/v1/login`; role-scoped tokens via `POST /role/select`; logout revokes the current token | ✅ |
| **API3 Object property exposure** — `CatalogService`, `StoreService`, `AppReviewService` column-scope their responses; no `user_id`/`stock` leaked on public endpoints | ✅ |
| **API4 Unrestricted consumption** — `?q` search capped at `max:200` (runtime + spec); `page` min:1; `throttle:login` | ✅ |
| **API5 Function-level auth** — `is_admin` middleware on all `/admin/*` routes; `active_role:buyer/seller/driver` on role-scoped prefixes | ✅ |
| **API6 Sensitive flows** — checkout, wallet debit, and delivery take all run inside `DB::transaction` + `lockForUpdate()`; idempotency sentinels prevent double-refund / double-take | ✅ |
| **API8 Misconfiguration** — `securitySchemes` (Sanctum Bearer) and `servers` block added to the OpenAPI spec | ✅ Fixed in Sprint 6 |
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

## Production deploy (Oracle Cloud Free Tier)

The `docker-compose.prod.yml` runs three services: `app` (php-fpm), `web` (nginx with baked static files), `db` (MySQL 8 with named volume).

### One-time VM setup

```bash
# 1. Clone the repo
git clone https://github.com/<user>/seapedia.git && cd seapedia

# 2. Copy and fill in the prod env
cp .env.example .env
# Edit .env: set APP_KEY, APP_URL, DB_PASSWORD, APP_DEBUG=false

# 3. Generate app key (run on the host if PHP available, or inside container)
docker run --rm -v "$(pwd)":/app -w /app php:8.3-cli php artisan key:generate --show
# → paste the output as APP_KEY=... in .env

# 4. Build images and start
docker compose -f docker-compose.prod.yml up -d --build

# 5. Migrate + seed
docker compose -f docker-compose.prod.yml exec app php artisan migrate:fresh --seed --force

# 6. Open http://<PUBLIC_IP>
```

### Open firewall ports (Oracle Cloud — two layers)

**VCN Security List (Oracle console):** add Ingress TCP 80 + 443 from `0.0.0.0/0`.

**Ubuntu iptables (SSH into VM):**
```bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
sudo apt-get install -y iptables-persistent
sudo netfilter-persistent save
```

### Install Docker on the VM

```bash
sudo apt-get update && sudo apt-get install -y ca-certificates curl git
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker ubuntu   # then re-login
```

### Re-deploy after a git pull

```bash
git pull
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

## Current status

Sprint 6 (final sprint) is complete — security hardening, landing page
redesign, auth/role-select rework, all seller/buyer/driver/admin UI reworks,
OpenAPI spec (33 endpoints, OWASP audit clean), and production Docker deploy.
See `planning/sprint6/progress.md` for the task log. Earlier sprint logs:
`planning/sprint5/` · `planning/sprint4/` · `planning/sprint3/` ·
`planning/sprint2/` · `planning/sprint1/`.

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
