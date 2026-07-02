# Sprint 7 Plan — UX Hardening & Flow Polish (Cart · Checkout · Seller · Driver)

> Post-Level batch. Levels 1–6 are already DONE. This sprint fixes reported bugs and
> raises the UX quality of the buyer purchase flow, the seller workspace, and the driver
> delivery flow — without changing any graded money/lifecycle rule. Debug-first, then the
> designed features, all in one batch (per the agreed sequencing).

## Goal
When this sprint is done: the mobile catalog category menu and order-status filters behave
correctly; the buyer can complete a cart → checkout → order flow that is fully responsive and
visually polished (single-store cart, add-address modal, a sea-themed promo/voucher picker with
voucher usage progress, a redesigned payment summary, and a post-checkout success screen that
links straight to "Pesanan Saya"); a new seller is guided from empty account → store profile →
first product with a richer dashboard; a driver sees available jobs sorted by nearest and a live
delivery animation on order detail, bounded by a concurrent-pickup cap; and every role can open
its mobile sidebar by swiping (button still present). Top-up is bounded to Rp5.000–Rp100.000.000.

## Scope (what this strengthens / fixes)
Not a new level — this hardens the UX of already-graded criteria and fixes user-visible bugs:
- **Level 3 (Cart/Checkout)** — responsive checkout, add-address, single-store cart clarity, delivery-method UI. (strengthen)
- **Level 4 (Discounts)** — buyer-facing promo/voucher discovery modal; keeps §5.3 rules intact. (strengthen)
- **Level 5 (Delivery)** — driver nearest-job sort, delivery animation, concurrent-pickup rule. (strengthen)
- **Level 6/Seller** — seller dashboard detail, onboarding guidance, store-profile completeness. (strengthen)
- **Bug fixes** — order-status filter, mobile category modal stacking, seller phantom badge. (fix)

## Locked decisions referenced (must NOT break)
- **§5.2** money math — PPN base = discounted subtotal; delivery fee not taxed; `grand_total = taxable_base + tax + delivery_fee`.
- **§5.3** discount — 1 Promo + 1 Voucher combinable; both off original subtotal; `discount_total = min(promo+voucher, subtotal)`. Voucher `used_count` incremented in the checkout txn under lock. Vouchers have `usage_limit`+`used_count`; promos have expiry only.
- **§5.4** delivery fee — fixed per method (Instan 20k / Besok 10k / Reguler 5k). **Spec (line 278) only requires the fee to differ per method** — it does not mandate these exact numbers, so a region-tier surcharge is spec-legal but is an OPEN DECISION (see Risks). Default: keep fixed.
- **§5.5** driver earning — 80% of delivery_fee on completion. Distance sort must NOT change fee or earning.
- **§5.6** order lifecycle — statuses: sedang_dikemas → menunggu_pengirim → sedang_dikirim → pesanan_selesai (+ dikembalikan on overdue). All transitions via `OrderService`, history logged. No new status.
- **Single-store cart** (spec Level 3, lines 67–69, 255–258) — one cart = one store; adding from another store must be prevented / offer to clear.

## shadcn-vue components needed (add before use, golden rule 12)
Verify each exists under `resources/js/components/ui/<name>/`; run `npx shadcn-vue@latest add <name>` for any missing FIRST:
- `progress` (voucher usage bar) — likely missing, ADD.
- `radio-group` (delivery method, payment-method selection) — verify/ADD.
- `separator` — verify.
- `dialog`, `sheet`, `card`, `badge`, `button`, `input`, `label` — already present.
- (Onboarding stepper: no standard shadcn-vue stepper — build a small custom step indicator on `card`+`badge`, not a new dependency.)

## Design direction (extends the existing SEAPEDIA identity; per-slice run `ui-ux-pro-max` + `frontend-design`)
The app already has an established sea identity; we extend it, not reinvent it. Each UI slice below
still invokes `ui-ux-pro-max` (+ `frontend-design`, + `motion-design` for animated slices) at build time.
- **Palette:** primary teal `#21C8B9` (existing), deep-sea ink for text, sand/foam neutrals; success emerald, warn amber, danger rose (matches `orderStatus.ts` fills).
- **Type & spacing:** keep the current scale/rhythm; tabular-nums for money; generous section spacing on checkout.
- **Motion:** "Premium" personality already in use — 350–500ms, `cubic-bezier(0.22,1,0.36,1)`; wave/tide motifs; respect `prefers-reduced-motion`.
- **Per-page signature elements:**
  - Checkout: a sticky, sea-tinted **payment summary rail** (desktop) / bottom sheet (mobile) as the anchor.
  - Voucher/Promo modal: **physical "voucher ticket" cards** (notched edge) with a usage **progress bar**; expired/used-up tickets desaturated and sunk to the bottom.
  - Seller dashboard: a **tide-line KPI header** + status-segmented order pipeline, not a grid of identical StatCards.
  - Delivery detail: a **route ribbon** with an animated vehicle traveling seller → buyer.

---

## Task breakdown (ordered vertical slices)

### Workstream A — Bug fixes ✅ DONE (committed this batch)
- **A1** `fix(order): match status tabs to real order enum` — `c1aa062`. Buyer/seller order pages had `menunggu_pembayaran`/`dibatalkan` tabs absent from the enum; `tryFrom()`→null dropped the filter and returned all orders (paid orders showed under "Belum Bayar"). Tabs now mirror the enum; controllers normalize `currentStatus`; removed the always-on "!" seller badge (item 8 phantom).
- **A2** `fix(catalog): close sheet before category modal` — `1fe2331`. Mobile "Semua Kategori" Dialog was covered by the still-open category Sheet; Sheet is now controlled and closed before the Dialog opens.

### Workstream F — Wallet (small, do early)
- **F1: Top-up bounds**
  - Files: `app/Http/Requests/StoreTopupRequest.php` (or the current top-up FormRequest), Indonesian messages; wallet top-up page min/max hints.
  - Rules: min `5_000`, max `100_000_000` (integer IDR). No §5 impact.
  - Acceptance: top-up below 5k or above 100jt rejected with a clear Indonesian message; valid amounts pass.
  - Tests: Pest — boundary cases (4999 reject, 5000 ok, 100000000 ok, 100000001 reject).
  - Commit: `feat(wallet): bound top-up between 5rb and 100jt`

### Workstream B — Cart & Checkout (buyer)
- **B1: Cart mobile layout rework**
  - Files: `resources/js/pages/buyer/cart/Index.vue` (+ any cart item component).
  - Fix: line price overflowing the card on mobile; move Kosongkan Keranjang, Subtotal, and "Lanjut ke Checkout" into a right-aligned summary column (CTA below subtotal). Make single-store rule explicit (banner + clear-first conflict handling).
  - Acceptance: at 375px no overflow; summary right-aligned; adding a 2nd-store product offers "clear cart first" clearly.
  - Tests: none (presentation) — CartService single-store guard already tested.
  - Commit: `fix(cart): stop price overflow and align mobile summary`
- **B2: Responsive checkout + payment summary redesign**
  - Files: `resources/js/pages/buyer/checkout/Show.vue`.
  - Add breakpoints; desktop = 2-col (form left, sticky summary rail right), mobile = stacked with a summary bottom-sheet/section; redesign the summary (subtotal, discount, delivery fee, PPN 12%, total) per §5.2 order.
  - Acceptance: clean at 375 / 768 / 1024+; all five money lines visible and correct.
  - Tests: none (money math already covered by CheckoutService tests).
  - Commit: `feat(checkout): make layout responsive with summary rail`
- **B3: Add-address modal on checkout**
  - Files: `checkout/Show.vue`, reuse `BuyerAddressController@store` + `StoreAddressRequest`; a Dialog form (region cascader reuse).
  - Acceptance: "Tambah Alamat" opens a modal; on save, the new address is selected without leaving checkout.
  - Tests: none new (address store already validated) — add one if the select-after-create wiring is non-trivial.
  - Commit: `feat(checkout): add inline add-address modal`
- **B4: Sea-themed promo/voucher picker modal**
  - Files: new read endpoint (e.g. `CheckoutController@availableDiscounts` or `DiscountController@indexForBuyer`) returning active promos + active vouchers (with expiry, min_spend, value/type, and for vouchers usage_limit/used_count); `checkout/Show.vue` trigger + modal component; `progress` component.
  - Behavior: manual code input at top (still works); Promo section then Voucher section below; each rendered as a voucher-ticket card showing code, syarat (min_spend), diskon (value/type, max_discount), and — for vouchers — a usage progress bar (used/limit). Active-but-exhausted/expired items still shown, desaturated, sorted to the bottom.
  - Rules: display only — actual validation/stacking stays in `DiscountService` (§5.3). Endpoint must not leak inactive/expired as usable.
  - Acceptance: modal lists current promos & vouchers with correct badges/progress; selecting one fills the code and re-previews; exhausted ones are visually "habis" and at the bottom.
  - Tests: Pest — endpoint returns only appropriate discounts; exhausted voucher flagged.
  - Commit: `feat(discount): add buyer promo and voucher picker modal`
- **B5: Post-checkout success screen**
  - Files: `checkout` success page/route (or the existing redirect target); `feat(checkout)`.
  - Behavior: after successful commit, show a success state with order code + a primary "Lihat Pesanan Saya" button to `buyer/orders`.
  - Acceptance: completing checkout lands on a success screen with a working link to the order and to the orders list.
  - Tests: none new.
  - Commit: `feat(checkout): add post-checkout success screen`

### Workstream C — Seller
- **C1: Store-profile completeness**
  - Files: verify `stores` schema vs desired fields (logo/photo, banner, description, address/region, phone, operational note); migration if fields missing; `seller/store/Show.vue` (edit form) + `SellerStoreController`/service + FormRequest.
  - Acceptance: seller can fill a detailed store profile (photo + address at minimum) and it renders on the storefront.
  - Tests: Pest — store update validation.
  - Commit: `feat(store): expand seller store profile fields`
- **C2: Seller onboarding guidance**
  - Files: `seller/**` dashboard/index; a small custom step indicator; conditional CTAs.
  - Behavior: no store → prominent "Lengkapi Data Toko" step + button (blocks product step); store but no product → "Tambah Produk" step; both done → normal dashboard.
  - Acceptance: brand-new seller is guided store → product with clear buttons.
  - Tests: none (presentation/conditional).
  - Commit: `feat(store): add seller first-run onboarding steps`
- **C3: Seller dashboard detail + visual rework**
  - Files: `DashboardController` (seller branch) + seller dashboard page; a seller stats service/query.
  - Content: revenue (released income), orders by status pipeline, units sold, low-stock products, top products, recent orders. Real count badge for "perlu diproses" (replaces the removed static "!").
  - Acceptance: dashboard shows accurate, eager-loaded stats (no N+1); reads as a designed pipeline, not identical StatCards.
  - Tests: Pest — stats numbers correct for a seeded store.
  - Commit: `feat(store): enrich seller dashboard with pipeline stats`
- **C4: Product create — live-repro fix**
  - First reproduce live (create with/without image). Static analysis shows the product persists and returns in the list, so likely an image-upload edge or client list refresh. Fix only the confirmed defect.
  - Acceptance: creating a product reliably shows it (with its image) on the product page.
  - Tests: Pest — product create persists + appears; feature test around image path if that's the defect.
  - Commit: `fix(product): <root cause once reproduced>`

### Workstream D — Driver
- **D1: Nearest-job sort (region-hierarchy proxy)**
  - Files: `DeliveryService`/`JobController` job listing; a distance-proxy helper (same village < district < city < province < other) using stored address region strings; no coordinates.
  - Rules: sort/label only; buyer fee and driver earning unchanged (§5.4/§5.5).
  - Acceptance: available jobs ordered nearest-first with a "dekat/…" label; fee/earning untouched.
  - Tests: Pest — ordering by proxy given seeded regions.
  - Commit: `feat(delivery): sort driver jobs by nearest region`
- **D2: Delivery route animation**
  - Files: `buyer/orders/Show.vue`, `seller/orders/Show.vue`, `driver/jobs/Show.vue`; a shared route-ribbon component; `motion-design`.
  - Behavior: animated vehicle traveling seller → buyer along a route ribbon while status is `sedang_dikirim` (static endpoints otherwise); reduced-motion safe.
  - Acceptance: all three detail views show the animation appropriately per status.
  - Tests: none (presentation).
  - Commit: `feat(delivery): add delivery route animation to order detail`
- **D3: Concurrent-pickup cap**
  - Files: `DeliveryService` take-job path; config for the cap (default 3); Indonesian message.
  - Rules: a driver may hold at most N active deliveries (`sedang_dikirim`). Spec-legal (spec is silent). Keep the existing lock that prevents double-take.
  - Acceptance: taking a 4th active job is rejected with a clear message; ≤3 allowed.
  - Tests: Pest — cap enforced; concurrent take still safe.
  - Commit: `feat(delivery): cap driver active pickups at three`

### Workstream E — Cross-cutting
- **E1: Swipe-to-open mobile sidebar (all roles + admin)**
  - Files: the authenticated app layout / sidebar sheet; a touch/drag handler (edge-swipe from left → open, swipe back → close). Navbar toggle button stays.
  - Acceptance: on mobile dashboards for buyer/seller/driver/admin, an edge swipe opens the sidebar; button still works; no scroll-jank; reduced-motion safe.
  - Tests: none (interaction) — manual demo.
  - Commit: `feat(ui): open mobile sidebar with edge swipe`

## Demo checklist (end of sprint)
- Mobile: category "Semua Kategori" opens cleanly (not covered). ✅ (A2)
- Buyer orders: paid+packed order shows under "Dikemas", never "Belum Bayar". ✅ (A1)
- Top-up: 4.999 rejected, 5.000 ok, 100jt ok, 100jt+1 rejected.
- Cart at 375px: no price overflow; right-aligned subtotal + CTA; 2nd-store add offers clear-first.
- Checkout responsive at 375/768/1024; add-address modal works; promo/voucher modal shows tickets + usage bars, exhausted at bottom; summary shows subtotal/discount/fee/PPN/total per §5.2.
- After checkout: success screen → "Lihat Pesanan Saya" opens the order.
- New seller: guided store → product; dashboard shows real pipeline stats; product create reliably appears.
- Driver: jobs sorted nearest-first; delivery animation on all three detail views; 4th active pickup rejected.
- Any role on mobile: edge-swipe opens the sidebar.

## Risks / open questions (defaults applied if unanswered)
1. **Delivery fee formula vs fixed** — spec allows a formula (only requires per-method difference); TDD locked fixed values. **Default: keep fixed**; optionally add a region-tier surcharge (B-workstream) only if you confirm — it would require updating TDD §5.4 + `money-and-checkout` + Pest + README. *Your call.*
2. **Promo usage limit** — spec permits it but it blurs the required voucher/promo distinction. **Default: promos stay expiry-only (progress bar = vouchers only).** *Your call.*
3. **Driver distance** — no coordinates stored, so distance is a region-hierarchy ordinal proxy, not km. **Default: proxy sort/label** (you chose "sort by nearest").
4. **Concurrent-pickup cap value** — **Default: 3** (config-driven, easy to change).
5. **Product-not-showing** — needs live reproduction before a fix is written; no data-loss found in static analysis.
6. **Repo location** — editing the C:\ working copy (this session's cwd, current repo). If you build from the WSL copy, confirm so edits land in the right tree.
7. **Store schema** — C1 may need a migration if profile fields (logo/banner/region) are missing; verified at build.

## Out of scope (deferred)
- Real geocoding / km-accurate distances (needs a coordinate dataset or API).
- Real-time driver GPS tracking (the animation is decorative, not live telemetry).
- Admin-side discount/monitoring redesigns (Level 6 admin UI already exists).
- Any change to the core §5.6 lifecycle or §5.2/§5.3 money/discount math.
