# Sprint 2 Design — Level 2 (Store + Product + Real Catalog) + i18n (ID/EN)

> Status: approved (brainstorming). Feeds `.planning/sprint2/plan.md` (sprint-planner skill).
> Authoritative sources: `SEAPEDIA_TDD.md` §13 Day 2, §7 schema, §5 locked decisions; `CLAUDE.md` golden rules.

## Scope

1. **Level 2 (TDD §13 Day 2):** real `stores` + `products`, seller store onboarding, product CRUD
   (own-only), public catalog + product detail read from DB (replaces dummy `CatalogService`),
   store detail page.
2. **i18n retrofit (hybrid ID/EN):** cross-cutting; translates all chrome + new pages. Carried over
   from Sprint 1 deviation #9 (deferred to Sprint 2). Owner confirmed combining it into this sprint.

## Decisions made during brainstorming (owner-confirmed)

- **Sprint scope = Level 2 + i18n combined** (owner chose to bundle, not split).
- **i18n approach = hybrid**, not a single mechanism:
  - Frontend chrome + pages: **vue-i18n** (1 new dependency, justified by the explicit need for
    instant, no-reload language switching from a navbar toggle). Dictionaries
    `resources/js/i18n/{id,en}.ts`.
  - Backend validation + flash messages: **Laravel `lang/id` & `lang/en`**, locale set by a
    `SetLocale` middleware from the resolved preference.
  - **Locale source of truth:** `users.locale` column for authenticated users + a cookie for guests,
    shared via `HandleInertiaRequests`. `LocaleController@update` writes both.
  - **Two switch points, one state:** compact ID/EN toggle in the **Navbar** (also works for guests)
    + a persisted preference on the **Settings page** (writes `users.locale`).
  - **Default locale = Indonesian.**
- **Product images = local upload** to the `public` disk (`storage:link`), validated by FormRequest
  (mime/size). Catalog + detail render images. Old file removed on replace.
- **One store per seller** (schema `stores.user_id` UNIQUE) → sellers with no store hit a "create
  store" onboarding step before product management.

## Locked decisions referenced

- §7 schema for `stores` / `products` (names/slugs UNIQUE, money = BIGINT UNSIGNED IDR, FK indexes).
- §5.1 integer IDR money.
- §15.3: order-status **display labels stay Indonesian in BOTH locales** — the i18n toggle never
  translates order-status text or any backend enum value (not exercised in S2 — no orders yet — but
  the principle is honored when those pages arrive).
- Golden rules: 1–2 (thin controllers → services), 4 (Policies for ownership, role via
  `EnsureActiveRole`, active role server-side), 8 (Eloquent only, no `v-html` on UGC), 11–13 (use
  shadcn-vue, install-before-use, §2.5 structure), 16 (eager-load, avoid N+1), 17 (components
  presentation-only).

## Data model

| Table / change | Columns | Notes |
|---|---|---|
| `stores` (new) | `user_id` FK UNIQUE, `name` UNIQUE, `slug` UNIQUE, `description`, `is_active` | 1 seller = 1 store |
| `products` (new) | `store_id` FK, `name`, `slug`, `description`, `price` (BIGINT), `stock` (uint), `image_path` (nullable), `is_active` | INDEX(store_id), INDEX(is_active) |
| `users.locale` (alter) | `locale` string, default `id` | i18n preference |

Factories for `Store` + `Product`. Seeder: `seller1` and `multi1` each get a store + several
image-bearing products so the public catalog is populated for the demo.

## Backend

- **Services:** `StoreService` (create/update with unique-name guard), `ProductService` (CRUD + image
  store/replace/delete), `CatalogService` rewritten to Eloquent — eager-load `store`, paginate,
  `?q` search, only `is_active` products belonging to `is_active` stores (no N+1).
- **Policies:** `StorePolicy` + `ProductPolicy` — own-only via `product.store.user_id`; cross-seller
  mutation → **403**.
- **FormRequests:** `StoreStoreRequest` / `UpdateStoreRequest`, `StoreProductRequest` /
  `UpdateProductRequest` (name, integer price, stock, **image mime/size**).
- **Controllers (thin):** `Web/SellerStoreController`, `Web/SellerProductController` (behind
  `active_role:seller`); public `Web/CatalogController` + `Web/StoreController`. `/api/v1` catalog
  endpoint (core flow) with a Swagger annotation.
- **i18n backend:** `SetLocale` middleware (`App::setLocale()` from `users.locale` or cookie);
  `LocaleController@update`; `lang/id/*` + `lang/en/*`.
- **Storage:** `storage:link`; uploads on `public` disk; delete prior file on replace.

## i18n architecture (frontend)

- `vue-i18n` registered in `app.ts`; `resources/js/i18n/{id,en}.ts` dictionaries; instant switch, no
  reload. Initial locale seeded from the Inertia-shared value so SSR/first paint match the
  stored/cookie preference.
- Sprint 1's still-English chrome (`Navbar`, `Footer`, `BottomNav`) gets translated here → the
  known temporary mixed-language UI is resolved.

## UI/UX direction

Refined with the `ui-ux-pro-max` + `frontend-design` skills when `plan.md` is written.

- **Catalog:** product-card grid (image, name, price, store), `?q` search, pagination,
  empty/loading/error states.
- **Product detail:** image, price, stock, **store info block** linking to the store page.
- **Store detail:** store header + that store's product grid.
- **Seller:** store-onboarding ("create store" when none), product list (shadcn `table`), create/edit
  form with image upload + preview.
- Continue the Ocean palette; responsive at 360 / 768 / 1280 / **1920**; dark mode; all three states.
- shadcn-vue needed: `table`, `pagination`, `tabs` — verify installed (planned in S1), `add` if missing.

## Testing (Pest — correctness-critical)

- Cross-seller product edit → **403** (`ProductPolicy`).
- Duplicate store name → **422** (DB unique + FormRequest).
- Product CRUD own-only (seller A cannot touch seller B's product).
- Catalog returns only active products of active stores.
- Locale switch persists (`users.locale` / cookie) and renders the chosen language.

## Visual QA (Playwright MCP)

Per deviation #8 / memory: after `npm run dev`, screenshot every new/changed page (catalog, product
detail, store detail, seller store onboarding, product list, product form, navbar locale toggle,
settings locale preference) at **360 / 768 / 1280 / 1920**, both locales, light + dark, with
empty/loading/error states.

## Out of scope (later sprints)

- `wallet_transactions`, top-up, addresses, cart, checkout → **Sprint 3**.
- Discounts, seller order processing, reports → **Sprint 4**.
- Driver, `ClockService` / clock advance, overdue sweep, admin dashboard → **Sprint 5**.
- Deployment (prod Docker, nginx, caddy, GCP), security pass, Swagger polish, README finalize →
  **Sprint 6** (deployment relocated per Sprint 1 deviation #1).

## Risks / open questions

- vue-i18n SSR hydration: the shared initial locale must be read before first render to avoid a
  flash/mismatch (kit has `inertia.ssr.enabled = true`). Verify during Playwright QA.
- Image upload under Sail/Docker bind mounts: confirm `storage:link` + `public` disk serve correctly
  in the WSL2 Docker setup.
- Slug collisions on near-duplicate names: derive slug from name with a uniqueness suffix.
