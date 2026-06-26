# Sprint 3 Plan — Level 3 (Buyer Wallet + Cart + Checkout) — HEAVIEST DAY (20 pts)

> Maps to TDD §13 "Day 3" and brief Level 3. Schema §7 (`wallet_transactions`, `addresses`,
> `carts`, `cart_items`, `orders`, `order_items`, `order_status_histories`, `topups`).
> Sources of authority, in order: `docs/KetentuanPanitia.pdf` (Level 3) → this `plan.md` →
> `docs/SEAPEDIA_TDD.md` (§ cited per task) → `CLAUDE.md`. **If the TDD/plan conflict with the
> brief, the brief wins**; flag it in `progress.md`.

## Goal

A buyer (active role: buyer) tops up their wallet with a dummy/fake gateway, manages delivery
addresses, fills a cart that is locked to a single store, and checks out: the app previews
subtotal → discount (placeholder this sprint) → delivery fee → PPN 12% → total, then commits the
order inside one locked transaction that charges the wallet, reduces stock safely (never negative,
no oversell under concurrency), creates the order in status **Sedang Dikemas** with a status-history
row, and clears the cart. The buyer sees the order in their history with a status timeline; the
seller sees it in an incoming-orders list. Wallet, cart, checkout, and order history are also
exposed under `/api/v1` with Swagger. Everything runs on Sail/local with `PAYMENT_GATEWAY=fake`.

## Scope (challenge criteria covered)

**Level 3 — Buyer Wallet, Cart, and Checkout (20 pts), brief pp. Level 3:**

- **Build Buyer Wallet and Address Management (5 pts)** — wallet balance + dummy/fake top-up +
  stored transaction history; delivery address CRUD; balance + top-up history on the buyer dashboard.
- **Implement Cart Management (5 pts)** — add/update-qty/remove/clear, cart summary endpoint + UI,
  **single-store checkout rule** (reject or clear-and-add), visible in UI + documented in README.
- **Create Checkout and Basic Orders (10 pts)** — checkout endpoint; delivery methods Instant /
  Next Day / Regular; subtotal + delivery fee + PPN 12% + total; checkout summary before confirm;
  single-store order; safe stock reduction (no negative); buyer order history + detail; seller
  incoming-order list; order status history with timestamps; initial status **Sedang Dikemas**;
  insufficient balance blocks checkout.

## Locked decisions referenced

- **§5.1 / §5.1b** — money is integer IDR (`BIGINT UNSIGNED`); **one wallet per user**, every
  movement is a `wallet_transactions` row with `type` + `direction` + `balance_after`; all wallet
  mutations go through `WalletService` inside a transaction with a row lock.
- **§5.2 Checkout order** — `taxable_base = subtotal − discount_total`; `tax = round(taxable_base ×
  0.12)`; delivery fee is **not** taxed; `grand_total = taxable_base + tax + delivery_fee`. Discount
  is a **placeholder/zero** this sprint (real vouchers/promos = Sprint 4) but the math + summary
  slots exist now. Documented in README.
- **§5.4 Delivery fees** — Instant 20 000 (SLA 1 tick), Next Day 10 000 (2 ticks), Regular 5 000
  (4 ticks). `sla_due_at = created_sim_at + SLA` precomputed at checkout (SLA enforcement = Sprint 5).
- **§5.6 Order lifecycle** — checkout success creates the order in **Sedang Dikemas** and writes an
  `order_status_histories` row. Status is set **only** via `OrderService` using the locked transition
  table; no status string set on the model elsewhere. Later transitions (process/take/complete/
  overdue) are out of scope this sprint.
- **§5.7 Time simulation** — `ClockService::now()` returns `settings.simulated_now` or real now;
  introduced here as checkout is its first consumer (`created_sim_at`). Advance/sweep = Sprint 5.
- **§5.8 Cart rule** — one active cart per buyer, nullable `store_id`; first item sets the store;
  adding from a different store → **422** with a clear message; UI offers "Clear & add".
- **§6 Concurrency** — checkout wraps `DB::transaction` + `Product::lockForUpdate()` per item,
  re-checks `stock ≥ qty` after the lock, rejects on shortfall (no negative stock). Wallet charge
  uses the locked `WalletService`.
- **§9.1–9.3 iPaymu / top-up** — top-up is the ONLY gateway touchpoint; checkout never calls a
  gateway (pays from wallet). `PaymentGateway` interface + `FakeGateway` fallback; `IpaymuGateway`
  is scaffolded but its real v2 call is deferred to Sprint 6. Seed/demo uses `fake`.
- **Golden rules** — 1–2 (thin controllers → one Service call), 3 (locked txn on balance/stock/
  status), 4 (Policies for ownership; `active_role` gating), 5 (integer IDR), 6 (ClockService time),
  7 (status only via OrderService + history), 8 (Eloquent only; no `v-html`), 9 (FormRequest per
  write), 10 (gateway behind interface; checkout pays wallet only), 11–13 (shadcn-vue, install-
  before-use, §2.5 structure), 15 (idempotent retryable effects — fake top-up), 16 (eager-load).

## Decisions BEYOND / DIFFERENT FROM the TDD (must stay visible)

1. **🔀 Visual identity stays "Ocean" + Instrument Sans.** `ui-ux-pro-max` again recommends a
   "Vibrant & Block-based"/purple marketplace style; rejected for the third time — purple doesn't
   read as Ocean and the trust framing favours teal/blue. We DO adopt the skill's **Marketplace
   "Trust & Authority"** pattern and **success-green semantics for money-in** (top-up/income/refund
   credits) vs muted/red for money-out (payment/reversal debits), within the existing Ocean tokens.
2. **➕ Signature element (per `frontend-design`): the wallet balance card + the checkout summary
   ledger.** Boldness is spent in exactly one family — a calm tidal-gradient **balance card** (the
   one vivid surface on the buyer/wallet view) and the **checkout summary as a tabular money
   ledger** (the focal "money moment"). Every form, list, and table around them stays quiet and
   disciplined. Money is the material: tabular numerals everywhere, credit/debit shown by colour
   **and** icon/label (never colour alone), `balance_after` visible in the ledger.
3. **➕ Discount is a zero placeholder this sprint.** The brief introduces vouchers/promos in Level
   4; the checkout summary already reserves the discount line and the §5.2 math handles a zero
   discount, so Sprint 4 only fills the value — no checkout rework.
4. **➕ `IpaymuGateway` scaffolded, not wired.** §9.2 mandates the interface + fallback both exist;
   the real iPaymu v2 redirect/webhook is Sprint 6 (needs the deployed HTTPS notify URL). This
   sprint ships the interface + a fully working `FakeGateway`; `IpaymuGateway` exists as a class that
   throws "not yet enabled" / is documented, so `PAYMENT_GATEWAY=fake` is the only live path.
5. **ℹ️ Process unchanged:** implementation runs on **Claude Sonnet** after approval; every UI slice
   is visually verified via **Playwright MCP** at 360 / 768 / 1280 / **1920**, both locales (ID/EN),
   light + dark, with empty/loading/error states. Commit locally only (owner pushes; no AI attribution).

## Instructions for the Sonnet implementer (READ FIRST)

You are implementing Sprint 3 — the heaviest day. Opus wrote and the owner approved this plan.

- **Authority order:** `docs/KetentuanPanitia.pdf` (Level 3) → this `plan.md` → `docs/SEAPEDIA_TDD.md`
  (§ cited) → `CLAUDE.md`. If the TDD/plan conflict with the brief, the brief wins; flag in `progress.md`.
- **One slice = one commit.** Implement T1→T8 in order; do not batch. Follow the `vertical-feature`
  skill order for every slice (migration → model → factory/seeder → FormRequest → policy → service →
  controller → route → api → page → test → commit). Commit messages per the `commit-message` skill.
- **Use the implementation skills for their slices:** `money-and-checkout` (T5 — the §5.2 math and
  locked-transaction commit; this is where the 20 pts and the race-correctness points live),
  `order-lifecycle` (T5/T6 — create→Sedang Dikemas via OrderService + history; the locked transition
  table), `ipaymu-topup` (T2 — `PaymentGateway` interface, `FakeGateway`, idempotent top-up).
- **Honor the golden rules**, especially: rule 3 — any balance/stock/status mutation runs inside
  `DB::transaction` + `lockForUpdate`; rule 6 — time only via `ClockService::now()`; rule 7 — order
  status only via `OrderService` + a history row; rule 10 — checkout pays from wallet, never a
  gateway; rule 15 — fake top-up is idempotent (replay-safe via `processed_at`).
- **Carry-overs from Sprint 2** (`planning/sprint2/plan.md` → "Perlu dikerjakan next sprint"):
  when you touch `StoreService::publicShow` or any public payload, apply the same column-scoping fix
  (don't leak `user_id`/timestamps); prefer API Resources if the API envelope starts repeating.
  These are not blocking T1–T8 but fold them in where you naturally pass through that code.
- **Before every commit:** `./vendor/bin/sail pint` + `npm run lint`; run the `code-review` skill as
  a self-check; run the slice's Pest tests. A slice is not done until format + lint + tests pass.
- **Owner-run commands (ask, don't run yourself):** `migrate`/`seed`, `storage:link`, `npm run dev`,
  **`npx shadcn-vue@latest add radio-group`** (first UI step), and any `.env` change
  (`PAYMENT_GATEWAY=fake`, iPaymu keys left blank this sprint). Everything else (test/pint/lint/
  format/build/vue-tsc/wayfinder `--with-form`) you run yourself.
- **Visual QA via Playwright MCP** for every UI slice: 360/768/1280/1920, both locales, light+dark,
  empty/loading/error states. Translate all new chrome/pages (ID + EN dictionaries) — i18n shipped
  in Sprint 2, so new strings use `t(...)` from the start, not hardcoded text.
- **Any new TDD deviation you introduce MUST be added to the deviations list above**, with the reason.

## shadcn-vue components needed

**New (add as the FIRST UI step):** `radio-group` — delivery-method selector (Instant / Next Day /
Regular radio cards showing fee + ETA). Owner runs `npx shadcn-vue@latest add radio-group`; confirm
`resources/js/components/ui/radio-group/` exists before importing.

**Already installed (verified in `resources/js/components/ui/`):** `card`, `button`, `input`,
`label`, `badge`, `select`, `table`, `tabs`, `dialog` (reused for clear-cart / delete-address
confirms, consistent with Sprint 2's delete pattern), `separator` (summary dividers), `skeleton`,
`sonner` (toasts), `alert`, `breadcrumb`, `avatar`, `spinner`. No others needed.

## UI/UX direction (from `ui-ux-pro-max` + `frontend-design`)

- **Pattern:** Marketplace "Trust & Authority" — money is shown plainly and confidently. Success-
  green for credits, muted/red for debits, always paired with an icon/label (`color-not-only`).
- **Wallet (signature #1):** a tidal-gradient **balance card** with the balance in large tabular
  numerals; below it, a quiet **transaction ledger** (table: date, type badge, direction, amount,
  running `balance_after`). Top-up is a small amount form (numeric input, quick-pick chips) → submit
  shows loading → success toast → balance + ledger update.
- **Checkout (signature #2):** the **summary ledger** is the focal block — subtotal, discount
  (placeholder), delivery fee, PPN 12%, grand total, each right-aligned tabular. One primary CTA
  ("Bayar sekarang" / "Pay now"); it is **disabled with a clear reason** when the wallet balance <
  grand total, with a "Top up" recovery link. Delivery method = `radio-group` cards (fee + ETA per
  option). Address = select from saved addresses (empty → inline "Add address" CTA). Confirm via a
  dialog summarizing what will be charged.
- **Cart:** line items with qty steppers, per-line + running subtotal (tabular); the **single-store
  rule** is visible (header note "Satu keranjang, satu toko") and enforced — adding from another
  store opens a dialog: "Keranjang berisi produk dari {toko}. Kosongkan dulu?" → "Clear & add".
- **Addresses:** list of address cards (default badge); create/edit in a dialog; delete = confirm
  dialog (`confirmation-dialogs`, High); set-default action.
- **States:** every list/detail has empty / loading (skeleton) / error — empty cart, no addresses,
  no transactions, no orders, insufficient balance, out-of-stock-at-checkout.
- **Forms:** visible labels + required indicators, error **below** the field, numeric `inputmode`
  for amounts/qty, `tel` for phone; submit shows loading then success/error toast; action labels
  stay consistent through the flow (button "Bayar" → toast "Pembayaran berhasil").
- **Quality floor:** responsive 360/768/1280/1920, dark-mode parity, visible focus, reduced-motion
  respected, contrast ≥4.5:1. **Order-status labels render Indonesian in both locales** (§15.3) —
  they live outside the i18n toggle namespace.

## Task breakdown (ordered vertical slices — one commit each)

- **T1 · Wallet ledger + locked `WalletService`**
  - Files: `wallet_transactions` migration (§7: type/direction/amount/balance_after/reference_type/
    reference_id/description, INDEX(wallet_id,created_at)); `WalletTransaction` model; enums
    `WalletTransactionType` (topup,payment,refund,income,earning,reversal,adjustment) +
    `WalletDirection` (credit,debit); `WalletService::credit()/debit()` inside `DB::transaction` +
    `lockForUpdate` on the wallet row, recording `balance_after`, returning the transaction;
    `WalletFactory`/`WalletTransactionFactory`. (`wallets` table already exists from Sprint 1.)
  - Business rules: §5.1b, §6 (wallet race), golden rules 3, 5.
  - Acceptance: crediting/debiting moves `wallets.balance` and appends a ledgered transaction with a
    correct `balance_after`; a debit beyond balance is rejected (no negative balance).
  - Tests (Pest): concurrent credit+debit keep balance consistent (locked); over-debit rejected.
  - Commit: `feat(wallet): add wallet ledger and locked WalletService`

- **T2 · Fake top-up (PaymentGateway interface + FakeGateway) + buyer wallet page**
  - Files: `topups` migration (§7); `Topup` model; `TopupStatus` enum (pending,paid,failed,expired);
    `PaymentGatewayType` enum (ipaymu,fake); `PaymentGateway` interface (§9.2); `FakeGateway`
    (instantly marks paid + credits via `WalletService`, **idempotent** on `processed_at`);
    `IpaymuGateway` scaffold (throws/“not enabled”; real call → Sprint 6); gateway bound by
    `PAYMENT_GATEWAY` env; `TopupService`; `StoreTopupRequest` (amount integer ≥ min);
    `Web/BuyerWalletController` (show wallet + history, create top-up) behind `active_role:buyer`;
    routes; buyer wallet page (balance card + top-up form + transaction/top-up history table).
  - Business rules: §9.1–9.3, golden rules 10, 15 (idempotent), 1–2.
  - Acceptance: a fake top-up immediately credits the wallet, writes a `paid` topup + a `topup`
    transaction, and is replay-safe; balance + history reflect it.
  - Tests (Pest): fake top-up credits wallet and writes ledger; replaying the same topup does not
    double-credit (idempotent via `processed_at`).
  - Commit: `feat(wallet): add fake top-up via payment gateway interface`

- **T3 · Delivery address management (CRUD)**
  - Files: `addresses` migration (§7: recipient_name/phone/full_address/is_default, INDEX(user_id));
    `Address` model; `AddressPolicy` (own-only); `StoreAddressRequest`/`UpdateAddressRequest`
    (recipient, phone `tel`, full_address required); `AddressService` (create/update/delete +
    single-default invariant — setting one default unsets the others in a transaction);
    `Web/BuyerAddressController` behind `active_role:buyer`; routes; buyer addresses page (cards +
    create/edit dialog + delete confirm + set-default).
  - Business rules: §7, golden rules 1–2, 4.
  - Acceptance: buyer manages only their own addresses; exactly one default at a time.
  - Tests (Pest): cross-user address update → 403; setting a new default unsets the previous one.
  - Commit: `feat(buyer): add delivery address management`

- **T4 · Cart with single-store guard**
  - Files: `carts` (user_id UNIQUE, nullable store_id) + `cart_items` (UNIQUE(cart_id,product_id),
    price_snapshot) migrations; `Cart`/`CartItem` models; `CartService` (resolve-or-create cart;
    `addItem` with **single-store guard** → throws a domain exception rendered as **422** with the
    §5.8 message; `updateQty`; `removeItem`; `clear`; `summary` eager-loaded, no N+1; snapshot price
    on add); `StoreCartItemRequest`/`UpdateCartItemRequest` (product_id, qty ≥ 1); `Web/
    BuyerCartController` behind `active_role:buyer`; routes; cart page (items, qty steppers, running
    subtotal, single-store conflict dialog "Clear & add", empty state).
  - Business rules: §5.8, golden rules 1–2, 8, 16.
  - Acceptance: first item sets the store; adding from another store → 422 + clear message; clear &
    add works; qty update/remove reflect in the summary.
  - Tests (Pest): add from a different store → 422; clear-then-add succeeds; qty update changes the
    line subtotal.
  - Commit: `feat(cart): add buyer cart with single-store guard`

- **T5 · Checkout preview + commit (the 10-pt core) + OrderService + ClockService**
  - Files: `orders` + `order_items` + `order_status_histories` migrations (§7, all money columns,
    INDEX buyer_id/store_id/status/sla_due_at); `Order`/`OrderItem`/`OrderStatusHistory` models;
    `OrderStatus` enum (sedang_dikemas,menunggu_pengirim,sedang_dikirim,pesanan_selesai,dikembalikan);
    `DeliveryMethod` enum (instant/next_day/regular → fee + SLA ticks, §5.4); `ClockService::now()`
    (settings.simulated_now or real now); `OrderService::createFromCheckout()` (locked-transition
    create → **Sedang Dikemas** + history row; the §5.6 transition primitive); `CheckoutService`
    — `preview()` returns the §5.2 summary (subtotal/discount=0/fee/PPN12%/total); `commit()` inside
    one `DB::transaction`: `lockForUpdate` each product, re-check `stock ≥ qty` (reject → 422, no
    negative), reduce stock, charge wallet via `WalletService::debit` (insufficient → 422, nothing
    persisted), snapshot the chosen address, create order+items+history, clear the cart;
    `PreviewCheckoutRequest`/`StoreCheckoutRequest` (address_id, delivery_method); `Web/
    CheckoutController` (preview + commit) behind `active_role:buyer`; routes; checkout page (address
    select, delivery `radio-group`, summary ledger, confirm dialog).
  - Business rules: §5.2, §5.4, §5.6, §5.7, §6, golden rules 3, 5, 6, 7, 10. Use the
    `money-and-checkout` + `order-lifecycle` skills.
  - Acceptance: checkout previews the correct money breakdown; on commit the wallet is charged, stock
    drops (never negative), the order is created **Sedang Dikemas** with a history row, and the cart
    is cleared; insufficient balance or out-of-stock blocks the order with a clear 422 and zero side
    effects.
  - Tests (Pest, CRITICAL): two concurrent checkouts on the last unit → exactly one succeeds, the
    other rejected, stock never negative (oversell guard); insufficient balance → rejected, no order,
    no stock change, no wallet debit.
  - Commit: `feat(checkout): charge wallet and reduce stock in a locked transaction`

- **T6 · Buyer order history + detail + Seller incoming orders + status timeline**
  - Files: `OrderService` read methods (buyer's own orders; seller's orders for their store; both
    eager-loaded, no N+1); `StatusTimeline` component (order-status history with timestamps, ID
    labels per §15.3); `Web/BuyerOrderController` (index + show) behind `active_role:buyer`;
    `Web/SellerOrderController` (incoming index — read-only; the process action is Sprint 4) behind
    `active_role:seller`; routes; buyer orders list + order detail (items, money breakdown, timeline)
    and seller incoming-orders list.
  - Business rules: §5.6 (display), golden rules 16, 17, 8.
  - Acceptance: buyer sees only their own orders with the full money breakdown + timeline; seller
    sees only orders for their store; status labels are Indonesian in both locales.
  - Tests (Pest): buyer cannot see another buyer's order (scoped); seller sees only their store's
    orders.
  - Commit: `feat(order): add buyer order history and seller incoming list`

- **T7 · API mirror + Swagger for the buyer flows**
  - Files: `Api/BuyerWalletController` (GET wallet + history, POST top-up), `Api/BuyerAddressController`
    (CRUD), `Api/BuyerCartController` (summary + add/update/remove/clear), `Api/CheckoutController`
    (POST preview, POST checkout), `Api/BuyerOrderController` (index + show) — all reusing the same
    Services (golden rule 2), Sanctum + active-role enforced; routes under `/api/v1/buyer/...`;
    OpenAPI annotations (`#[OA\...]`) matching the §8 surface; regenerate `l5-swagger`.
  - Business rules: brief Level 3 "Backend APIs for wallet, address, cart, checkout, tax calculation,
    and basic order history"; golden rule 2.
  - Acceptance: `/api/v1/buyer/checkout` creates an order from a single-store cart and returns it;
    insufficient balance → 422; Swagger lists the buyer endpoints.
  - Tests (Pest): API checkout happy path returns the order; API checkout with insufficient balance
    → 422.
  - Commit: `feat(api): expose buyer wallet, cart and checkout endpoints`

- **T8 · Demo seed (wallets, addresses, a sample order) + README**
  - Files: extend the demo seeder so `buyer1` and `multi1` have a topped-up wallet (via
    `WalletService`/`TopupService`, not raw inserts), at least one saved address, and one seeded
    order through `CheckoutService`/`OrderService` so order history + seller incoming list are
    populated for the demo; README gains the **single-store checkout rule**, the **PPN 12% base
    (discounted subtotal)** rule, the **unified wallet model**, and the **fake top-up** note (§9),
    plus a "Demo path (Sprint 3)" section.
  - Business rules: §12 seed/demo, §5.1 integer prices; brief "document single-store + PPN rules".
  - Acceptance: `migrate:fresh --seed` yields buyers with balance, addresses, and a visible order on
    both buyer and seller sides — ready to demo top-up → cart → checkout end-to-end.
  - Tests: none (covered by T1–T7 feature tests).
  - Commit: `feat(db): seed buyer wallets, addresses and demo order`

## Visual polish bundle → DEFERRED to the final sprint (owner's decision)

Discussed and **deferred to the final sprint** (Sprint 6 polish), so the heaviest day stays on the
20-pt core. Not built in Sprint 3:

- **Marketplace homepage** — replace the generic `Welcome.vue` splash with a real storefront home
  (search + hero banner + featured products + categories + store highlights + reviews), keeping
  `/catalog` as the full listing. The brief Level 1 allows a "landing page OR home page" and requires
  it to read as "a marketplace, not only a single-store catalog"; a product-rich home (Shopee/
  Tokopedia/MegaMart style) scores best on the UI bonus. **Assets already prepared by the owner** in
  `public/images/` (`banner/`, `banner/hero/`, `banner/side/`, `category/`).
- **Real demo product photos + product re-theme** — `public/images/product/` holds real photos
  (iPhone 17 / 17 Pro Max colourways, Tenda, rel pancing, kantong plastik vakum, pelapis kabel
  insulasi). When the homepage is built, re-theme the demo stores/products to these (electronics +
  outdoor/tools) and swap the seeder's solid-colour GD placeholders for them (1 image/product per §7).

Sprint 3 keeps the existing campus demo seed + placeholder images; visuals are upgraded as one
coherent pass in the final sprint.

## Demo checklist (end of sprint)

1. Log in as `buyer1` → wallet shows seeded balance + top-up history; top up again → balance +
   ledger update instantly.
2. Manage addresses (add / edit / set default / delete with confirm).
3. Browse catalog → add a product to cart → try adding from a **different store** → blocked with the
   single-store dialog → "Clear & add".
4. Checkout: pick address + delivery method (Instant/Next Day/Regular) → summary shows subtotal +
   delivery fee + **PPN 12%** + total → pay from wallet → order created **Sedang Dikemas**, stock
   reduced, cart cleared, wallet charged.
5. Buyer order history + detail show the money breakdown and status timeline; the seller sees the
   order in their incoming list.
6. Insufficient balance → checkout blocked with a clear reason + top-up recovery link.
7. `/api/v1/buyer/checkout` mirrors the flow; Swagger lists the endpoints.
8. Responsive + dark-mode + both-locale pass at 360/768/1280/1920 via Playwright (empty/loading/
   error states), with order-status labels staying Indonesian in EN.

## Risks / open questions

- **PPN base when discount = 0:** with no discounts this sprint, `taxable_base = subtotal`, so PPN =
  round(subtotal × 0.12). Assumption: keep the full §5.2 pipeline with `discount_total = 0` so Sprint
  4 drops in cleanly. Documented in README.
- **Stock vs cart qty:** stock is authoritative only at checkout (locked re-check). Assumption: the
  cart may hold a qty that later exceeds stock; checkout rejects with a clear 422 ("Stok {produk}
  tinggal {n}") rather than silently clamping. Cart UI may soft-warn but does not hard-block.
- **`created_sim_at` source:** `ClockService::now()` returns `settings.simulated_now` if set, else
  real now. The advance/sweep mechanism is Sprint 5; this sprint only reads the clock.
- **Order `code` uniqueness:** generate a human-readable unique order code (e.g. `INV-{ymd}-{rand}`)
  with a uniqueness retry, same spirit as the slug helpers. Assumption unless TDD says otherwise.
- **Seller incoming list before "process" exists:** T6 ships it read-only; the process action
  (Sedang Dikemas → Menunggu Pengirim) is Sprint 4 — flagged so the seller page isn't expected to
  mutate status yet.

## Perlu dikerjakan next sprint (carry-over)

To be picked up by Sprint 4+; Sprint 4's Sonnet instructions must reference this section.

- **Discount value** — checkout summary reserves the discount line at 0 this sprint; Sprint 4 fills
  it with real voucher/promo validation + the §5.3 combination rule and increments `used_count`
  inside the checkout transaction.
- **Seller "process" action** — T6's seller incoming list is read-only; Sprint 4 adds Sedang Dikemas
  → Menunggu Pengirim via `OrderService` + history + the timeline on both sides.
- **Real iPaymu** — `IpaymuGateway`'s real v2 call + webhook (needs the deployed HTTPS notify URL) →
  Sprint 6.
- **Carried from Sprint 2** (still open): public store-page payload column-scoping; API Resource
  shaping if the envelope repeats; breadcrumb i18n.

## Out of scope (deferred to later sprints)

- Vouchers/promos, seller order processing, buyer/seller reports → **Sprint 4**.
- Driver/delivery jobs, `seapedia:advance-day` + clock advance UI, `OverdueService::sweep()`, admin
  dashboard, voucher/promo management UI → **Sprint 5**.
- Real iPaymu integration, security pass, Swagger polish, README finalize, deployment → **Sprint 6**.
- **Marketplace homepage + real product photos + product re-theme** (assets in `public/images/`) →
  **final sprint** (owner's decision; see "Visual polish bundle" above).
- Moving `planning/` into `docs/` → **final sprint** (owner's instruction).
