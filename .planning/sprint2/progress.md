# Sprint 2 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Stores foundation — `feat(store): add seller store create and update`
- [x] T2 · Product CRUD + image upload — `feat(product): add seller product crud with image upload`
- [x] T3 · Real public catalog — `feat(catalog): read public catalog and detail from database`
- [x] T4 · Public store detail page — `feat(store): add public store detail page`
- [x] T5 · Catalog API + Swagger — `feat(api): expose public catalog endpoints with swagger`
- [x] T6 · i18n infrastructure — `feat(i18n): add hybrid id/en locale infrastructure`
- [x] T7 · Locale switcher + translate — `feat(i18n): add navbar and settings language switcher`
- [ ] T8 · Demo seed (stores + products) — `feat(db): seed demo stores and products`

## Tests

- [x] Duplicate store name → 422; cross-seller store update → 403 (T1)
- [x] Cross-seller product update → 403; cross-seller delete → 403; CRUD own-only; invalid price/stock → 422 (T2)
- [x] Catalog returns only active products of active stores; search filters; inactive detail → 404 (T3)
- [x] Store page lists only that store's active products; inactive store → 404 (T4)
- [x] `GET /api/v1/catalog` returns only active products (T5)
- [x] Default locale `id`; `SetLocale` applies cookie locale (T6)
- [x] Authenticated locale update persists to `users.locale`; guest update sets cookie (T7)

## Visual QA (Playwright MCP)

- [x] Catalog (grid/search/empty/loading/error) at 360/768/1280/1920, both locales, light + dark
- [x] Product detail + store info block at all breakpoints/locales/themes
- [x] Public store detail page
- [x] Seller store onboarding + product list + create/edit form (image upload + delete dialog)
- [x] Navbar locale toggle (guest + authed) + Settings locale preference

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
- T2: `products.slug` given a DB-level `unique()` even though TDD §7 doesn't say UNIQUE for it
  (only `stores.name`/`stores.slug` are marked UNIQUE) — needed because the public catalog resolves a
  product by slug alone (`CatalogService::find($slug)`), and two different stores could otherwise sell
  same-named products and collide. Same `uniqueSlug()` collision-suffix pattern as `StoreService`.
  Simplest §5-consistent choice for an underspecified column, per CLAUDE.md "When unsure."
- T2: product creation resolves the store implicitly via `$request->user()->store` (no `{store}`
  route param) — there is no cross-seller create attack surface to test, unlike update/delete which
  use a `{product}` route param + `ProductPolicy` so a cross-seller attempt is representable/testable.
- T2: hit a real `defineOptions()` compiler limit in `seller/products/Form.vue` — its content is
  hoisted out of `<script setup>`'s closure, so it cannot reference `props` (tried a dynamic
  breadcrumb title for create vs. edit). Fixed by dropping to a single static "Produk" breadcrumb;
  `<Head :title="...">` already carries the create/edit distinction. Caught by `sail npm run build`
  failing — `vue-tsc`/lint did not catch it.
- T2: owner ceded `migrate`/`seed`/`storage:link` to Claude too (memory updated) — verbatim "jalankan
  sendiri mulai sekarang" after being asked twice to run `sail artisan migrate` for `stores`/`products`
  on the dev DB. Still hand off: `sail up`, `sail npm run dev`, `shadcn-vue add`.
- T2: visual QA used a hand-built minimal valid JPEG (no `PIL`/ImageMagick in the sandbox) — confirms
  upload → preview → storage → thumbnail render end-to-end, even though the image itself is a blank
  1×1 swatch (not a meaningful visual check of image *content*, only of the upload pipeline).
- T3: the "both locales" visual QA checkbox below is intentionally left unchecked — ID-only QA passed
  (search band, grid, pagination via reka-ui `PaginationRoot`, store info block, empty-search state,
  360/768/1280/1920, light+dark) but EN doesn't exist until T6/T7. Will re-verify both locales once
  i18n ships, then check the box.
- T3: catalog grid's product-card store name is plain text (not a link to the store page) — that link
  only exists on the **product detail's** store info block, added in T4. Keeps T3 self-contained/
  buildable without referencing a route T4 hasn't created yet.
- T3: temporarily seeded 13 extra factory products via tinker to visually confirm pagination renders
  (>12 items); deleted them after the QA pass — dev-DB-only, not part of any seeder/migration.
- T4: `Web/StoreController` (public, singular) sits alongside the existing `Web/SellerStoreController`
  (authenticated seller side) — two controllers by design, not a naming collision; route names
  `stores.show` (public) vs `seller.store.show` (own dashboard) stay unambiguous too.
- T4: as planned in T3's notes, the catalog product detail's store info block went from plain text to
  a real `<Link>` to `/stores/{slug}` only now that the target route exists — added `stores/` to
  `app.ts`'s `GuestLayout` switch (same bucket as `catalog/`/`reviews/`, since it's public).
  Catalog **grid** card's store name stays plain text (not linked) — only the plan's two named spots
  (product-detail store info block, store page itself) got the link treatment.
- T5: `darkaonline/l5-swagger` (+ `zircote/swagger-php`) was already a Sprint 1 composer dependency
  (TDD §2/§12.5) but had zero annotations yet — `l5-swagger:generate` failed with "Required @OA\Info()
  not found" until one `#[OA\Info(version: '1.0.0', title: 'SEAPEDIA API')]` attribute was added (on
  `Api\CatalogController`, the first real annotated controller — no dedicated "API base" class added,
  keeps the diff minimal). Used PHP 8 attributes (`#[OA\Get(...)]`), matching the project's existing
  attribute style (`#[Fillable]`) rather than classic docblock `@OA` annotations.
- T5: added `/storage/api-docs` to `.gitignore` — the generated `api-docs.json` is a build artifact
  (regenerated by `sail artisan l5-swagger:generate`, fully derived from source annotations), same
  treatment as the already-gitignored `/public/build` (Vite's build output). Re-run after any `#[OA\...]`
  change.
- T5: `Api/CatalogController` returns raw Eloquent JSON (paginator / model `toJson()`), no API
  Resource class — matches `Api/MeController`'s existing precedent of hand-built `response()->json([...])`,
  and the plan doesn't ask for Resources this sprint. Revisit if/when the API surface grows enough to
  need consistent envelope shaping.
- T6: resolved the "vue-i18n SSR hydration" risk from the design doc differently than its own fallback
  assumption — instead of seeding the i18n instance from `withApp`'s reactive page state (timing with
  Inertia's plugin registration is ambiguous), `app.ts` reads the locale synchronously straight out of
  the embedded `<script data-page>` JSON in the HTML (present before Vue ever mounts) via a small
  `resolveInitialLocale()` helper. Confirmed via Playwright: `<html lang>` and the shared `locale` prop
  always agree, zero console warnings on boot, in both locales.
- T6: added `App\Enums\Locale` (`Indonesian = 'id'`, `English = 'en'`) — a fixed two-value set, same
  treatment as `RoleName` (golden rule 13: enums for fixed sets, no magic strings). `SetLocale`
  validates against it so a garbage/unsupported cookie value can never reach `App::setLocale()`.
  Verified directly: a `locale=fr` cookie falls back to the default rather than erroring.
  Test gotcha: Pest's `withCookie()` test helper **encrypts** the value before sending (matching
  prod browser+`EncryptCookies` behavior) — since `'locale'` is in `encryptCookies(except: [...])`,
  the middleware never decrypts it, so a `withCookie()`-set value arrives as ciphertext, not plaintext.
  Must use `withUnencryptedCookie()` in tests for any cookie added to that `except` list.
- T6: `lang/id/validation.php` is a full Indonesian translation of Laravel's stock
  `validation.php` (published once via `artisan lang:publish`, both files customized with our own
  `attributes` array — name/username/price/stock/image/etc. — so messages read like "harga harus..."
  not "the :attribute..."). `artisan lang:publish` also generated `auth.php`/`pagination.php`/
  `passwords.php` — deleted those (out of T6's stated scope: "validation + a flash file" only); not
  needed for English (Laravel's vendor-shipped `en` lang ships those as a fallback) and not requested
  for Indonesian this sprint.
- T6: "a flash file" was implemented as **`lang/id.json` + `lang/en.json`** (JSON translation files at
  the lang root), not a PHP array file — because every existing flash call
  (`Inertia::flash('toast', ['message' => __('Store created.')])` in Seller{Store,Product}Controller,
  pre-existing `__('Profile updated.')`/`__('Password updated.')` in Settings controllers) already uses
  Laravel's "translation string as the key" pattern, which is JSON-file territory, not array-key
  territory. This translates those four pre-existing Sprint-1/2 calls into Indonesian automatically
  with zero changes to their PHP — confirmed via tinker (`__('Store created.')` → "Toko berhasil
  dibuat." under `id`, unchanged under `en`).
- T6: `.env`/`.env.example` (`APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`) are owner-edited only per a new
  hard rule — Claude never touches `.env*` files in this project, full stop (not even with permission
  asked first); see CLAUDE.md-adjacent memory, not written into CLAUDE.md itself.
- T6: `bootstrap/app.php`'s `encryptCookies(except: [...])` gained `'locale'` alongside the existing
  `appearance`/`sidebar_state` — same category (small client-readable preference cookie, not a secret).
  `SetLocale` is registered **before** `HandleInertiaRequests` in the `web` group so `App::getLocale()`
  is already correct by the time Inertia shares props for that request.
- T7: scoped translation strictly to what the plan named — Navbar/Footer/BottomNav (+ the
  visually-adjacent `AppSidebar`/`NavMain`/`RoleBadge` chrome pieces, same nav surface) and the
  catalog/product/store pages built in T1–T4. Sprint 1's `dashboard/*`, `reviews/Index`, `Welcome`,
  `auth/*`, and `settings/{Profile,Security}` pages stay Indonesian/English-as-already-written —
  deliberately out of scope, same as Sprint 1 left chrome English while content was Indonesian. Flagged
  here, not a silent gap.
- T7: found and fixed a real cross-navigation bug the design doc didn't anticipate — vue-i18n's
  `locale` ref only gets seeded from the server **once**, at the very first full page load
  (`resolveInitialLocale()` in `app.ts`). A guest browsing in `en` (cookie) who then logs into an
  account whose saved `users.locale` is `id` would keep seeing English after the post-login SPA
  redirect, because Inertia visits don't re-run `app.ts`'s boot code. Fixed with a
  `router.on('navigate', ...)` listener in `app.ts` that re-syncs `i18n.global.locale.value` to
  `event.detail.page.props.locale` after every Inertia visit — confirmed via Playwright: logged out,
  set the guest cookie to `en` directly, logged back in as `multi1` (DB `locale: 'id'`) through the
  real login form (SPA navigation throughout, no hard reload), and the dashboard correctly rendered
  Indonesian immediately.
- T7: the plan only said "LocaleToggle in Navbar (works for guests)" — `Navbar.vue` is the
  **guest-style** header (`GuestLayout`-driven pages: home/catalog/reviews/stores). Authenticated
  dashboard pages use a completely different chrome (`AppSidebar` + `AppSidebarHeader`), which never
  renders `Navbar.vue` at all — so an authenticated seller on `/seller/store` etc. would have had *no*
  quick toggle, only the Settings-page route. Added `<LocaleToggle />` to `AppSidebarHeader.vue` too
  (visible on every authenticated page) so both chrome surfaces get the one-click switch, matching the
  design doc's "two switch points" intent rather than just the plan's literal wording.
- T7: known limitation, not fixed — breadcrumb titles passed through `defineOptions({layout:
  {breadcrumbs}})` (e.g. "Toko Saya" / "Produk" in `seller/store/Show.vue`, `seller/products/Form.vue`)
  stay in whichever language they were hardcoded in and do **not** flip with the toggle. Same root
  cause as the T2 deviation: `defineOptions()`'s content is hoisted out of `<script setup>`'s closure,
  so it cannot call `t(...)` (a composable). The page heading directly below each breadcrumb (which
  *does* use `t(...)`) is correct in both languages — only the small breadcrumb trail above it lags.
  Fixing this properly would mean changing the shared `BreadcrumbItem` contract used by several
  Sprint-1 pages outside this sprint's scope; left as a cosmetic gap rather than expanding scope.
- T7: Pest gotcha distinct from T6's — `TestResponse::assertCookie($name, $value)` defaults
  `$encrypted = true` and tries to **decrypt the response cookie** before comparing, throwing the same
  `DecryptException("The payload is invalid.")` for any cookie in `encryptCookies(except: [...])`. Fix:
  `assertCookie('locale', 'en', false)` — third arg `false` skips the decrypt attempt. Different fix
  from T6's `withUnencryptedCookie()` (that's for *outgoing* test-request cookies; this is for
  *incoming* response-cookie assertions).
