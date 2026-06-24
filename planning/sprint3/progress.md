# Sprint 3 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Wallet ledger + locked WalletService — `feat(wallet): add wallet ledger and locked WalletService`
- [x] T2 · Fake top-up (gateway interface + FakeGateway) + wallet page — `feat(wallet): add fake top-up via payment gateway interface`
- [x] T3 · Delivery address management — `feat(buyer): add delivery address management`
- [x] T4 · Cart with single-store guard — `feat(cart): add buyer cart with single-store guard`
- [x] T5 · Checkout preview + commit + OrderService + ClockService — `feat(checkout): charge wallet and reduce stock in a locked transaction`
- [x] T6 · Buyer order history + detail + seller incoming list — `feat(order): add buyer order history and seller incoming list`
- [x] T7 · API mirror + Swagger for buyer flows — `feat(api): expose buyer wallet, cart and checkout endpoints`
- [x] T8 · Demo seed (wallets, addresses, sample order) + README — `feat(db): seed buyer wallets, addresses and demo order`

## Deferred to final sprint (owner's decision)

- Marketplace homepage (replace Welcome splash) + real product photos + product re-theme.
  Assets already in `public/images/`. Sprint 3 keeps the existing campus seed + placeholders.

## Tests

- [x] Concurrent credit+debit stay consistent (locked); over-debit rejected (T1)
- [x] Fake top-up credits wallet + writes ledger; replay is idempotent (T2)
- [x] Cross-user address update → 403; new default unsets previous (T3)
- [x] Add from different store → 422; clear-then-add succeeds; qty update changes line subtotal (T4)
- [x] Oversell: two concurrent checkouts on last unit → one succeeds, one rejected, no negative stock (T5)
- [x] Insufficient balance → rejected, no order/stock/charge side effects (T5)
- [x] Buyer scoped to own orders; seller scoped to own store's orders (T6)
- [x] API checkout happy path returns order; insufficient balance → 422 (T7)

## Visual QA (Playwright MCP) — 360/768/1280/1920, both locales, light + dark

Ran after a full session restart got Playwright MCP reconnected (the plugin-reload-only fix from
mid-T2 turned out insufficient — the running MCP server process needed a full restart to pick up
the patched `--executable-path`, not just a config-file edit). Covered 360/1280/1920, light+dark,
ID+EN, across both real interactions (top-up, add-to-cart, single-store conflict, full checkout
commit, locale toggle) and static states, rather than a fixed breakpoint × locale × theme matrix.

- [x] Wallet: balance card + top-up form (loading/success) + transaction ledger — 1280/360,
  light+dark; top-up interaction verified end-to-end (balance + ledger update live)
- [x] Addresses: list + create/edit dialog + delete confirm + set-default — 1280; create dialog,
  "Jadikan utama", delete confirm dialog all verified interactively
- [x] Cart: items, qty steppers, single-store conflict dialog, empty state — 1280/360; conflict
  dialog message matches §5.8 wording exactly ("Keranjang berisi produk dari Toko Berkah...");
  qty stepper updates subtotal live; empty state after checkout clears the cart
- [x] Checkout: address select + delivery radio-group + summary ledger + confirm — 1280/1920,
  light+dark; full commit verified end-to-end (PPN 12% math correct, wallet debited, order created)
- [x] Buyer order history + detail — 1280; money breakdown + status timeline render correctly;
  toggled to EN locale and confirmed all UI chrome translates while the order status badge stays
  "Sedang Dikemas" (per the T6 decision)
- [x] Seller incoming orders list — 1280/1920, dark mode; shows both of buyer1's orders against
  Toko Berkah

**Bug found and fixed during this pass:** `SidebarInset`/`AppContent` had no horizontal padding of
their own, and the sidebar's "inset" margin (`md:peer-data-[variant=inset]:m-2`) only applies at
`md:` and up — every dashboard page's content (old pages from Sprint 1/2 included, not just this
sprint's new ones) sat flush against the viewport edge on mobile. Fixed once in
`DashboardLayout.vue` (wraps `<slot />` in a padded div) instead of patching each page individually,
and removed `SettingsLayout`'s own padding to avoid doubling up. See `fix(ui)` commit.

**Noted, not fixed (out of this sprint's scope):** the buyer and seller dashboard widgets
(`dashboard/Buyer.vue`, `dashboard/Seller.vue`) show "Pesanan Aktif: 0" / "Produk Aktif: 0" even
when orders/products exist — looks like a Sprint 1 dashboard summary query that was never wired up
to the real data added in Sprints 2-3. Flagging for whoever picks up dashboard polish; not a T1-T8
deliverable.

## Notes / deviations recorded

- **T1:** Fixed a latent bug in `WalletFactory` (Sprint 1 artifact, never exercised until now) —
  `UserObserver` already provisions a wallet on every new user, so the factory's own insert
  collided with `wallets.user_id`'s unique constraint. `WalletFactory::create()` now resolves the
  user first, then `updateOrCreate`s that user's existing wallet row instead of inserting a second
  one. Fixes both direct `Wallet::factory()` calls and `WalletTransactionFactory`'s nested usage.
- **T1 test strategy:** `WalletServiceTest` proves the ledger/balance bookkeeping and the
  over-debit rejection deterministically (sequential ops, no real DB-level race). The literal
  dual-connection row-lock proof (one transaction holds the row, a second genuinely blocks/fails)
  is reserved for T5's checkout oversell test, since that's the plan's explicitly "CRITICAL" path
  and reuses the same `lockForUpdate` technique on the `products` row.
- **T2 deviation from plan:** `ClockService` introduced now, not at T5 as the plan assumed.
  `topups.processed_at` is a business-logic timestamp under golden rule 6 ("never `now()` directly
  in business logic"), so it needed the simulated clock from its first use. T5 will just consume the
  already-existing service.
- **T2 assumption:** no minimum top-up amount is locked by the TDD; picked 10,000 IDR
  (`config('payment.topup.min_amount')`) as the simplest reasonable floor — to confirm/restate in
  the README (T8).
- **T3:** "Set as default" is its own button per non-default address card, not a checkbox inside
  the create/edit dialog (matches the plan's wording exactly: "create/edit in a dialog; ... ;
  set-default action" are listed as separate things). `AddressService` still accepts an optional
  `is_default` key so the T8 seeder can mark one explicitly when seeding.
- **T4:** `cart_items.price_snapshot` is taken when an item is *added* to the cart (display only —
  shows the buyer what price they saw). It is deliberately NOT what gets charged: per TDD §7,
  `order_items` has its own `price_snapshot`, taken fresh from the live `product.price` under lock
  at checkout commit (T5). If a seller changes a price while it's in someone's cart, the checkout
  preview shows the current price, not the stale cart one — no extra cart-side warning needed.
  `CartService::addItem` accepts a `$replaceStore` flag (clear-then-add in one request/transaction)
  instead of a separate clear-and-add endpoint. Ownership is enforced via a new `CartItemPolicy`
  (`update`/`delete`), matching the `AddressPolicy`/`ProductPolicy` convention, not inline checks in
  the service. `BuyerCartController::store` redirects `back()` (not to a fixed route) since it's
  called from the catalog/product page as well as the cart page itself.
- **T5 design call (flagged, not literally in TDD):** seller income is credited to the seller's
  wallet INSTANTLY at checkout (amount = `taxable_base`, excluding tax and delivery fee — neither
  is seller revenue), not deferred to "Pesanan Selesai". The TDD's own wording is ambiguous
  ("credit when an order is completed/paid") and the money-and-checkout skill's 5-step list doesn't
  explicitly list a seller-credit step. Went with instant settlement because: (a) §5.1b explicitly
  calls seller income "spendable... instant settlement", and (b) §5.9's overdue-refund step
  ("reverse seller income") only makes sense if the seller was ALREADY credited before the order
  could become overdue — an order that's overdue, by definition, never reached Pesanan Selesai. If
  this is wrong, the fix is confined to `CheckoutService::commit()` removing the seller credit call
  and adding it to the (Sprint 4+) "Pesanan Selesai" transition instead.
- **T5 schema deviation:** `orders` has no `promo_id`/`voucher_id` columns yet — `promos`/`vouchers`
  tables don't exist until Sprint 4, so a real FK constraint isn't possible yet. `discount_total`
  exists now (placeholder 0); Sprint 4 adds the two FK columns when it builds those tables.
- **T5 test-isolation bug found and fixed:** the new `CheckoutConcurrencyTest` uses
  `DatabaseTruncation` (not `RefreshDatabase`) so a second real DB connection can contend for the
  same row — but unlike `RefreshDatabase`, `DatabaseTruncation` commits real rows instead of rolling
  back, so without an explicit `tearDown()` truncate, it leaked a `roles` row into whichever test
  ran next (intermittent `Duplicate entry 'buyer'` failures elsewhere in the suite). Fixed by calling
  `truncateTablesForAllConnections()` in `tearDown()`. Worth remembering for any future test that
  reaches for `DatabaseTruncation`.
- **T5 ordering note:** the order is created *before* the wallet debit (the money-and-checkout
  skill's literal step list debits first) so `wallet_transactions.reference_id` can point at the
  order. Transaction atomicity makes this equivalent — a failed debit still rolls back the
  just-created order, order_items, and stock decrement together.
- **T6:** order status labels (Sedang Dikemas, etc.) are hardcoded Indonesian in
  `resources/js/lib/orderStatus.ts`, deliberately bypassing vue-i18n — they're the system's fixed
  vocabulary (printed on both buyer and seller views), not translatable UI chrome. Seller only gets
  the incoming list this sprint, no per-order detail page (matches the plan's task list exactly;
  the "process" action and any seller order detail view are Sprint 4). Found and fixed a real Vue
  compiler error along the way: `defineOptions()` cannot reference `props.*` (it's hoisted out of
  setup()) — the order-detail breadcrumb dropped the dynamic order code and uses a static label.
- **T7:** API routes follow §8's representative paths exactly (`POST /buyer/cart/items`,
  `POST /buyer/checkout/preview`), which differ from the web routes' own paths (`/buyer/cart`,
  no separate preview route — the web checkout page precomputes all three delivery methods up
  front instead). Both surfaces call the identical Services underneath; only the route shape
  differs per surface, per golden rule 2.
- **T8:** Removed the old raw `$buyer->wallet->update(['balance' => 500_000])` from
  `DemoUserSeeder` (Sprint 1) and replaced it with a real `TopupService::create()` call in the new
  `BuyerDemoSeeder` — the plan explicitly asked for service-based seeding so the wallet ledger has
  a real entry, not just a balance number with no history behind it. `BuyerDemoSeeder` runs after
  `StoreProductSeeder` in `DatabaseSeeder` (needs seller1's product to exist before it can build a
  cart and check out). Verified end-to-end via tinker after `migrate:fresh --seed`: buyer1 balance
  474,840 (500,000 − 25,160 grand_total), 2 ledger rows, 1 address, 1 order (Sedang Dikemas, 1 item,
  1 status history); seller1's store shows the same order in its incoming list with 18,000 credited.
