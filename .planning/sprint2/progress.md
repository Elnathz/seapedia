# Sprint 2 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Stores foundation — `feat(store): add seller store create and update`
- [ ] T2 · Product CRUD + image upload — `feat(product): add seller product crud with image upload`
- [ ] T3 · Real public catalog — `feat(catalog): read public catalog and detail from database`
- [ ] T4 · Public store detail page — `feat(store): add public store detail page`
- [ ] T5 · Catalog API + Swagger — `feat(api): expose public catalog endpoints with swagger`
- [ ] T6 · i18n infrastructure — `feat(i18n): add hybrid id/en locale infrastructure`
- [ ] T7 · Locale switcher + translate — `feat(i18n): add navbar and settings language switcher`
- [ ] T8 · Demo seed (stores + products) — `feat(db): seed demo stores and products`

## Tests

- [x] Duplicate store name → 422; cross-seller store update → 403 (T1)
- [ ] Cross-seller product update → 403; cross-seller delete → 403; CRUD own-only; invalid price/stock → 422 (T2)
- [ ] Catalog returns only active products of active stores; search filters; inactive detail → 404 (T3)
- [ ] Store page lists only that store's active products; inactive store → 404 (T4)
- [ ] `GET /api/v1/catalog` returns only active products (T5)
- [ ] Default locale `id`; `SetLocale` applies cookie locale (T6)
- [ ] Authenticated locale update persists to `users.locale`; guest update sets cookie (T7)

## Visual QA (Playwright MCP)

- [ ] Catalog (grid/search/empty/loading/error) at 360/768/1280/1920, both locales, light + dark
- [ ] Product detail + store info block at all breakpoints/locales/themes
- [ ] Public store detail page
- [ ] Seller store onboarding + product list + create/edit form (image upload + delete dialog)
- [ ] Navbar locale toggle (guest + authed) + Settings locale preference

## Notes / deviations recorded

- T1: `app/Http/Controllers/Controller.php` (kit base) had no `AuthorizesRequests` trait, so
  `$this->authorize(...)` was undefined. Added `use AuthorizesRequests;` to the base class — needed
  by every future ownership-gated controller (T2/T4), not just this slice. No `AuthServiceProvider`
  policy registration was needed; Laravel's default policy auto-discovery (`App\Policies\{Model}Policy`)
  picked up `StorePolicy` for `Store` with zero config.
- T1: route design for `seller.store.update` keeps `{store}` as a route param (not an implicit
  "current user's store") specifically so a cross-seller 403 attempt is representable/testable — the
  normal UI flow always submits its own store's id since the edit form is rendered from
  `$request->user()->store`.
- T1: `StorePolicy::create()` denies a seller who already owns a store (§7 `user_id` UNIQUE) — returns
  403 on a direct repeat POST, rather than letting it fall through to a DB unique-constraint 500. Added
  as its own Pest case beyond the plan's two required tests.
- T1: `AppSidebar.vue`'s nav is now role-aware (`useAuthStore().activeRole === 'seller'` adds a
  "Toko Saya" link) — not in the plan's file list, but required for the feature to be reachable at all
  without typing the URL by hand; verified it correctly disappears when switching to buyer/driver.
- T1: hit the documented wayfinder `--with-form` footgun again (bare `sail artisan wayfinder:generate`
  dropped `.form()` from every generated route/action) — re-ran with `--with-form`. Also: `npm run
  build` fails on the **host** (no native `php`, so the wayfinder vite plugin's own internal
  regenerate-on-build shells out and fails with "php not found") — must run builds via
  `sail npm run build` instead of bare `npm run build` whenever wayfinder-tracked routes changed.
- T1: Playwright MCP was connected to a stale plugin config (`playwright/unknown/.mcp.json` lacked
  `--executable-path`, unlike a sibling cached copy that had the fix) — patched that file directly and
  had the owner run `/mcp` reconnect. Visual QA then passed at 360/768/1280/1920, light+dark: onboarding
  empty state, create flow, pre-filled edit state, role-aware sidebar (link hidden for buyer/driver),
  and a direct `/seller/store` hit while active-role=buyer correctly renders 403.
- T1: dev DB (`seapedia`) needed `sail artisan migrate` run by the owner before the page would load
  (table only existed in the `testing` DB via Pest's `RefreshDatabase`) — expected, not a bug.
