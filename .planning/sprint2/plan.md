# Sprint 2 Plan — Level 2 (Seller Store + Products + Real Catalog) + i18n (ID/EN)

> Maps to TDD §13 "Day 2". Schema §7 (`stores`, `products`). Design doc:
> `docs/superpowers/specs/2026-06-24-sprint2-store-product-catalog-i18n-design.md` (owner-approved).
> Deviations from the TDD are flagged in **"Decisions beyond / different from the TDD"**.

## Goal

A seller (active role: seller) creates their one store (unique name) and manages products
(create/edit/delete, with image upload) that only they can touch — a cross-seller edit returns 403.
Those products appear in a **DB-backed public catalog** (search + pagination) with a product detail
page that links to a public store page. Across the whole app, a user can switch the UI language
between **Indonesian and English** from a navbar toggle (also available to guests) or a persisted
Settings preference; server validation/flash messages follow the chosen locale. Order-status display
labels stay Indonesian in both locales (none exist yet — principle only). Everything still runs on
Sail/local.

## Scope (challenge criteria covered)

- **Level 2 — Seller store + products + real catalog** (the gradable Day-2 milestone):
  - Store create/update with **unique name** (DB unique + FormRequest).
  - Product CRUD by seller, `ProductPolicy` own-only (cross-seller mutation → 403).
  - Seller dashboard product list.
  - Public catalog + product detail read from DB (replaces Sprint 1's dummy `CatalogService`);
    store info block on the product page; public store detail page.
- **i18n ID/EN toggle** — owner-requested, carried from Sprint 1 deviation #9 (not a TDD level; see
  deviation #1 below).

## Locked decisions referenced

- **§7 schema** — `stores` (`user_id` UNIQUE, `name` UNIQUE, `slug` UNIQUE, `description`,
  `is_active`); `products` (`store_id`, `name`, `slug`, `description`, `price` money, `stock` uint,
  `image_path` nullable, `is_active`; INDEX store_id + is_active).
- **§5.1** — money is integer IDR (`BIGINT UNSIGNED`); price stored as integer, no floats.
- **§15.3** — order-status display labels remain Indonesian in BOTH locales; the i18n toggle never
  translates order-status text or any backend enum value. (Not exercised this sprint — no orders —
  but the dictionary structure honors it: status labels live outside the toggle's namespace.)
- **§4** — role-gating via `EnsureActiveRole` (`active_role:seller`); active role server-side;
  never trust role from request body.
- **Golden rules** — 1–2 (thin controllers → one Service call), 4 (Policies for ownership), 5
  (integer IDR), 8 (Eloquent only; no `v-html` on UGC), 11–13 (shadcn-vue, install-before-use, §2.5
  structure), 16 (eager-load; no N+1 on catalog), 17 (components presentation-only).

## Decisions BEYOND / DIFFERENT FROM the TDD (must stay visible)

1. **➕ i18n ID/EN toggle is not in the TDD.** Owner-requested feature, deferred from Sprint 1
   (deviation #9). Approach (owner-approved in the design doc): **hybrid** — `vue-i18n` for frontend
   chrome/pages (instant switch, no reload), Laravel `lang/id` + `lang/en` for server
   validation/flash, a `SetLocale` middleware, locale source of truth = **`users.locale`** column
   (auth) + **cookie** (guest), shared via Inertia, switchable from navbar + Settings. Default `id`.
2. **➕ `vue-i18n` dependency added.** CLAUDE.md forbids libraries without concrete need; the concrete
   need is instant, no-reload language switching from a navbar toggle (pure server-side i18n forces a
   reload per switch). Justified and recorded.
3. **➕ `users.locale` column** added (string, default `id`) — not in TDD §7 `users`; required to
   persist a logged-in user's language preference across devices.
4. **🔀 Visual identity stays "Ocean" + Instrument Sans.** `ui-ux-pro-max` again suggested a
   purple "Vibrant & Block-based" marketplace style; rejected for the same reason as Sprint 1
   (deviation #2) — purple doesn't read as "Ocean" and the marketplace-trust framing favors
   teal/blue. We adopt the skill's **Marketplace/Directory pattern** (search-as-CTA, store/trust
   signals) but keep the existing Ocean tokens and Instrument Sans (no new font import).
5. **➕ Product image upload uses the local `public` disk** (`storage:link`). TDD §7 only specifies
   `image_path` nullable; it doesn't mandate a storage mechanism. Local disk chosen (owner-approved);
   no cloud storage this sprint.
6. **ℹ️ Process unchanged from Sprint 1:** implementation runs on **Claude Sonnet** after this plan
   is approved; every UI slice is **visually verified via Playwright MCP** at 360/768/1280/1920, both
   locales, light + dark. Commit locally only (owner pushes).

## Instructions for the Sonnet implementer (READ FIRST)

You are implementing Sprint 2. Opus wrote and the owner approved this plan — do not re-plan.

- **Authoritative sources, in order:** this `plan.md` → the design doc
  (`docs/superpowers/specs/2026-06-24-sprint2-store-product-catalog-i18n-design.md`) →
  `SEAPEDIA_TDD.md` (§ cited per task) → `CLAUDE.md` golden rules. **If they ever conflict, the TDD
  wins**; flag the conflict in `progress.md`.
- **One slice = one commit.** Implement T1→T8 in order. Do not batch tasks into one commit. Commit
  messages follow the `commit-message` skill.
- **Follow the `vertical-feature` skill order** for every slice (migration → model → factory/seeder →
  FormRequest → policy → service → controller → route → api → page → test → commit).
- **Honor the golden rules:** thin controllers → one Service call, logic in Services, Eloquent only,
  no `v-html` on UGC, integer IDR, Policies for ownership, `EnsureActiveRole` for role-gating, active
  role resolved server-side, eager-load (no N+1). Models use the kit's `#[Fillable]` attribute style.
- **Before every commit:** run `./vendor/bin/sail pint` + `npm run lint`; run the `code-review` skill
  as a self-check; run the slice's Pest tests. A slice is not done until format + lint + tests pass.
- **Commands the OWNER runs (ask them, don't run yourself):** `migrate`/`seed`, `npm run dev`,
  `php artisan storage:link`. `npm install vue-i18n` belongs to T6. No `shadcn-vue add` is
  needed — all required components are already installed.
- **Visual QA via Playwright MCP** for every UI slice: screenshot at 360/768/1280/1920, **both
  locales (ID/EN)**, light + dark, with empty/loading/error states; fix layout issues before commit.
- **Any new deviation from the TDD you introduce MUST be added to the deviations list above**, with
  the reason — hard requirement from the owner.
- **Pushing:** commit locally only; the owner pushes. Commits carry no AI attribution.

## shadcn-vue components needed

Already installed (verified in `resources/js/components/ui/`): `table`, `pagination`, `tabs`,
`select`, `dialog`, `card`, `button`, `input`, `label`, `badge`, `skeleton`, `dropdown-menu`,
`alert`, `sonner`, `breadcrumb`. **No new `shadcn-vue add` required this sprint.**

New non-shadcn dependency: **`vue-i18n`** (`npm install vue-i18n`) — registered in `app.ts` as the
first i18n step (T6), before any `useI18n()` import.

## UI/UX direction (from `ui-ux-pro-max` + `frontend-design`)

- **Pattern:** Marketplace/Directory — search is the primary CTA. Catalog leads with a calm
  **tidal-gradient search band** (the sprint's one "signature" element, per `frontend-design`'s
  "spend boldness in one place"); everything else stays quiet and disciplined.
- **Product card:** reserved image aspect-ratio (`aspect-ratio` / width+height) to avoid layout
  shift (CLS); `loading="lazy"` images with descriptive `alt`; price in **tabular numerals**;
  whole card is a single clear link; hover transition 150–300ms; `cursor-pointer`.
- **Store info block (product detail) & store page header:** trust signal — store name + avatar
  initials + active badge, linking buyer to the store's catalog.
- **Seller product table:** shadcn `table`; row actions Edit / Delete; **Delete is a confirm dialog**,
  visually separated, semantic danger color (`destructive-emphasis`, `confirmation-dialogs`).
- **Forms (store + product):** visible labels (not placeholder-only), required indicators, **error
  below the field**, image upload with live preview, submit shows loading then success/error toast.
- **States:** every list/detail has **empty / loading (skeleton) / error** states (catalog no-results,
  store with no products, product 404, seller with no store → onboarding empty state).
- **Quality floor:** responsive 360/768/1280/1920, dark-mode parity, visible keyboard focus,
  `prefers-reduced-motion` respected, contrast ≥4.5:1, color never the only signal.
- **Copy:** end-user voice, sentence case, ID + EN; action labels consistent through the flow
  ("Simpan"/"Save" → toast "Tersimpan"/"Saved").

## Task breakdown (ordered vertical slices — one commit each)

Each slice follows the `vertical-feature` skill order (migration → model → factory/seeder →
FormRequest → policy → service → controller → route → api → page → test → commit). Run `pint` +
ESLint/Prettier + relevant Pest before each commit; `code-review` skill as self-check; commit per the
`commit-message` skill.

- **T1 · Stores foundation (seller-side)**
  - Files: `stores` migration (§7); `Store` model (`#[Fillable]`, `belongsTo User`, `hasMany Product`,
    slug from name); `StoreFactory`; `StorePolicy` (own-only); `StoreStoreRequest`/`UpdateStoreRequest`
    (name UNIQUE + required, description); `StoreService` (create/update, unique-name guard, slug);
    `Web/SellerStoreController` (show onboarding / create / edit / update) behind `active_role:seller`;
    routes; seller "create/edit store" Inertia page (onboarding empty state when none).
  - Business rules: §7 unique name/slug; §4 `active_role:seller`; Golden rules 1–2, 4.
  - Acceptance: a seller with no store sees onboarding → creates a store; duplicate store name → 422;
    seller can edit only their own store.
  - Tests (Pest): duplicate store name → 422; a seller cannot update another seller's store (403).
  - Commit: `feat(store): add seller store create and update`

- **T2 · Product CRUD (seller-side) with image upload**
  - Files: `products` migration (§7); `Product` model (`belongsTo Store`, casts price/stock int,
    slug); `ProductFactory`; `ProductPolicy` (own-only via `product.store.user_id`);
    `StoreProductRequest`/`UpdateProductRequest` (name, integer price ≥0, stock ≥0, image mime/size
    nullable); `ProductService` (create/update/delete + image store/replace/delete on `public` disk);
    `Web/SellerProductController` (index/create/store/edit/update/destroy) behind `active_role:seller`
    + `ProductPolicy`; routes; seller product **list** page (shadcn `table`) + create/edit form page
    (image upload + preview); delete confirm dialog. `storage:link` documented.
  - Business rules: §5.1 integer price; §7 product schema; §4 + `ProductPolicy` own-only; Golden
    rules 1–2, 4, 5, 8 (no `v-html` on description render), 16.
  - Acceptance: seller creates/edits/deletes own products with an image; **cross-seller edit/delete →
    403**; price persisted as integer IDR.
  - Tests (Pest): cross-seller product update → 403; cross-seller delete → 403; product CRUD own-only
    happy path; invalid price/stock → 422.
  - Commit: `feat(product): add seller product crud with image upload`

- **T3 · Real public catalog (DB-backed)**
  - Files: rewrite `CatalogService` to Eloquent — `index()` paginated, `?q` name search, eager-load
    `store`, **only `is_active` products of `is_active` stores** (no N+1); `find($slug)` for detail
    (404 when missing/inactive); update `Web/CatalogController`; refresh `catalog/Index.vue` (card
    grid + search band + pagination + empty/loading/error) and `catalog/Show.vue` (detail + store
    info block) to consume real props; remove the dummy data array.
  - Business rules: §7 indexes; Golden rules 8, 16 (eager-load), 17.
  - Acceptance: products created in T2 appear in the public catalog; search filters by name;
    inactive product/store hidden; unknown slug → 404 page.
  - Tests (Pest): catalog returns only active products of active stores; search filters correctly;
    inactive product detail → 404.
  - Commit: `feat(catalog): read public catalog and detail from database`

- **T4 · Public store detail page**
  - Files: `StoreService::publicShow($slug)` (active store + its active products, eager-loaded);
    `Web/StoreController@show` (public); route; `stores/Show.vue` (store header + product grid +
    empty state); link product detail's store info block → this page.
  - Business rules: §7; Golden rules 16, 17.
  - Acceptance: clicking a store name opens its public page listing only that store's active products;
    inactive/unknown store → 404.
  - Tests (Pest): store page lists only that store's active products; inactive store → 404.
  - Commit: `feat(store): add public store detail page`

- **T5 · Catalog API + Swagger (core flow)**
  - Files: `Api/CatalogController` (`GET /api/v1/catalog`, `GET /api/v1/catalog/{slug}`) reusing
    `CatalogService`; routes; Swagger/OpenAPI annotations.
  - Business rules: §1.2 backend-API requirement; Services reused by web + API (Golden rule 2).
  - Acceptance: `/api/v1/catalog` returns the same active-only data as the web catalog; Swagger lists
    the endpoints.
  - Tests (Pest): `GET /api/v1/catalog` returns only active products (JSON shape).
  - Commit: `feat(api): expose public catalog endpoints with swagger`

- **T6 · i18n infrastructure (hybrid)**
  - Files: `users.locale` migration (string, default `id`); `User` fillable/cast; install + register
    `vue-i18n` in `app.ts` with `resources/js/i18n/{id,en}.ts` dictionaries (seed initial locale from
    the Inertia-shared value to avoid SSR flash); `lang/id/*` + `lang/en/*` (validation + a `flash`
    file); `SetLocale` middleware (`App::setLocale()` from `users.locale` or cookie) registered in
    `bootstrap/app.php`; share `locale` in `HandleInertiaRequests`.
  - Business rules: §15.3 (status labels excluded from toggle); deviations #1–#3.
  - Acceptance: app boots with locale resolved server-side; `App::getLocale()` follows cookie/user;
    no console/hydration locale mismatch.
  - Tests (Pest): default locale `id`; `SetLocale` applies cookie locale to the request.
  - Commit: `feat(i18n): add hybrid id/en locale infrastructure`

- **T7 · Locale switcher (navbar + settings) + translate chrome/pages**
  - Files: `LocaleController@update` (writes cookie always; `users.locale` when authenticated) + route;
    `LocaleToggle` component in `Navbar` (works for guests); locale preference control on the
    Settings page; replace hardcoded strings across `Navbar`/`Footer`/`BottomNav` and the
    catalog/product/store/seller pages with `t(...)` keys in both dictionaries.
  - Business rules: never trust client for the persisted value beyond the user's own row; §15.3 keeps
    status labels out of scope.
  - Acceptance: toggling in the navbar switches the whole UI instantly (no reload); a logged-in user's
    choice persists across sessions; a guest's persists via cookie; server validation errors render in
    the chosen language; Sprint 1's mixed-language chrome is gone.
  - Tests (Pest): authenticated locale update persists to `users.locale`; guest update sets cookie.
  - Commit: `feat(i18n): add navbar and settings language switcher`

- **T8 · Demo seed (stores + products)**
  - Files: extend the demo seeder so `seller1` and `multi1` each own a store with several
    image-bearing products (sample images committed under the public disk / seeded path); README note
    on `storage:link`. Keep role-assignment in the service layer (as in Sprint 1's `DemoUserSeeder`).
  - Business rules: §12 seed/demo data; §5.1 integer prices.
  - Acceptance: `migrate:fresh --seed` yields a populated public catalog with real images, ready to
    demo end-to-end.
  - Tests: none (covered by T1–T4 feature tests).
  - Commit: `feat(db): seed demo stores and products`

## Demo checklist (end of sprint)

1. Log in as `seller1` → seller store page (already onboarded by seed) → product list shows seeded
   products.
2. Create a new product with an image → it appears in the seller list and in the **public catalog**.
3. Guest: catalog → search by name → open a product → see the **store info block** → open the
   **store detail page** → see only that store's products.
4. Log in as `multi1` (switch to seller) → try to edit `seller1`'s product by URL → **403**.
5. Toggle language **ID ⇄ EN** from the navbar (as guest and as a logged-in user) → UI switches
   instantly; trigger a form validation error → message appears in the chosen language; a logged-in
   user's choice persists after logout/login.
6. Responsive + dark-mode pass at **360 / 768 / 1280 / 1920** in both locales — verified via
   Playwright MCP screenshots (empty/loading/error states included).

## Risks / open questions

- **vue-i18n SSR hydration:** the Inertia-shared initial locale must be applied before first render
  (`inertia.ssr.enabled = true`) to avoid a flash/mismatch. Assumption if unresolved: seed the i18n
  instance from the shared prop inside the `withApp` hook (same place Pinia was wired in S1). Verify
  in Playwright QA.
- **Image upload under Sail/WSL2 Docker bind mounts:** confirm `storage:link` + `public` disk serve
  uploaded files. Assumption: standard `php artisan storage:link`; if the symlink misbehaves over the
  bind mount, document the workaround in the README (consistent with S1's WSL notes).
- **Slug uniqueness:** derive slug from name; on collision append a short unique suffix. Assumption:
  `Str::slug(name)` + `-{n}` increment within a transaction.
- **npm dependency install** (`vue-i18n`) is a one-off setup step; per the project's command split it
  is run as part of the i18n slice (T6) and verified by `npm run build` passing.

## Out of scope (deferred to later sprints)

- `wallet_transactions`, top-up, addresses, cart, checkout → **Sprint 3**.
- Discounts, seller order processing, reports → **Sprint 4**.
- Driver, `ClockService` / clock advance, overdue sweep, admin dashboard → **Sprint 5**.
- Deployment (prod Docker, nginx, caddy, GCP), security pass, Swagger polish, README finalize →
  **Sprint 6** (deployment relocated per Sprint 1 deviation #1).
- Product categories/tags, multi-image galleries, store logos/banners — not in TDD §7; not built.
