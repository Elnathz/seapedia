# Sprint 1 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Roles foundation — `feat(role): add roles, pivot and user relations`
- [x] T2 · Wallet entry point — `feat(wallet): add wallet table and user relation`
- [x] T3 · Settings table — `feat(db): add settings key-value table`
- [x] T4 · Sanctum API tokens — `feat(auth): issue role-scoped sanctum tokens`
- [x] T5 · Active-role core — `feat(role): enforce active-role via EnsureActiveRole middleware`
- [x] T6 · Registration fields — `feat(auth): capture username and phone on registration`
- [x] T7 · Public reviews — `feat(review): add public app reviews with guest submission`
- [x] T8 · UI foundation — `feat(ui): add ocean palette and layout foundation`
- [ ] T9 · Pages — `feat(ui): add landing, catalog and role dashboards`
- [ ] T10 · Demo seeder — `feat(db): seed demo users and roles`

## Tests

- [x] Multi-role login → role-selection; single-role auto-select (T5)
- [x] `EnsureActiveRole` 403 on role mismatch (T5)
- [x] Buyer token blocked from seller API route (T5)
- [x] Duplicate username rejected on registration (T6)
- [x] Guest submits valid review; invalid rating → session errors, no row created (T7)

## Visual QA (Playwright MCP)

- [x] T8 — layouts + role badge/switcher screenshotted at 360/768/1280/1920
- [ ] T9 — all pages + empty/loading/error states screenshotted at 360/768/1280/1920
- [x] Dark-mode pass (T8 only; T9 still pending)

## Notes / deviations recorded

- Deployment relocated to Sprint 6 (deviation #1).
- T8: Ocean palette finalized via `ui-ux-pro-max` skill (`--design-system` + `--domain color`
  searches grounded in "marketplace ecommerce ocean trustworthy" and "ocean blue teal marketplace
  trustworthy" queries). Chose the teal/blue "Trust teal + professional blue" result over the
  generic marketplace purple suggestion — purple doesn't read as "Ocean" and TDD's marketplace-trust
  framing favors blue/green per the skill's own Marketplace pattern notes. Deviated from the skill's
  suggested "Vibrant & Block-based" style (too playful/decorative for a campus payment marketplace);
  kept the kit's existing clean shadcn layout and only swapped color tokens + typography is unchanged
  (Instrument Sans) — no new font import added, since plan.md never asked for a font change.
  Final tokens (HSL, written into `resources/css/app.css` `:root` / `.dark`), all foreground/background
  text pairs verified ≥4.5:1 (WCAG AA) by direct calculation:
  - Light: background `hsl(165 33.3% 97.6%)` / foreground `hsl(186.9 61.4% 11.2%)` (14.5:1); primary
    `hsl(175.3 77.4% 26.1%)` on white (5.5:1); secondary `hsl(204 93.8% 93.7%)` / secondary-fg
    `hsl(202 80.3% 23.9%)` (8.2:1); muted-fg `hsl(185.8 16.4% 37.1%)` on muted `hsl(180 23.1% 94.9%)`
    (5.2:1); accent `hsl(167.2 85.5% 89.2%)` / accent-fg `hsl(175.9 60.8% 19%)` (8.4:1); border
    `hsl(174.8 31.5% 85.7%)` (subtle, matches kit's original low-contrast divider intent, not a text
    pair).
  - Dark: background `hsl(188.6 63.6% 6.5%)` / foreground `hsl(175.7 43.8% 93.7%)` (16.4:1); primary
    `hsl(172.5 66% 50.4%)` / primary-fg `hsl(173.6 77.8% 7.1%)` (9.2:1); secondary
    `hsl(196 68.2% 17.3%)` / secondary-fg `hsl(200.6 94.4% 86.1%)` (9.2:1); muted-fg
    `hsl(183.2 20.4% 63.5%)` on muted `hsl(187.7 47.7% 12.7%)` (6.5:1); accent `hsl(184.8 65.8% 14.9%)`
    / accent-fg `hsl(168.4 83.8% 78.2%)` (9.7:1).
  - Chart palette (5 colors, light/dark variants) and sidebar tokens derived from the same hue family
    (teal/sky-blue primary, amber/coral for chart variety) — see `app.css` for exact values.
  - Layout/UX risks flagged by the skill review, applied to T8's component build: bottom nav capped
    at ≤5 items with icon+label and a highlighted active state (`bottom-nav-limit`, `nav-state-active`);
    sidebar (desktop ≥1024px) and BottomNav (mobile) are alternates by breakpoint, never shown together
    (`adaptive-navigation`, `avoid-mixed-patterns`); all nav/role-switcher touch targets ≥44×44px
    (`touch-target-size`); StatCard figures use tabular/monospaced numerals to avoid layout jitter on
    balance updates (`number-tabular`); fixed BottomNav reserves bottom padding on scrollable content
    so it never covers the last list item (`fixed-element-offset`); role badge always pairs color with
    the role's text label, never color alone (`color-not-only`).
- Implementation runs on Claude Sonnet (deviation #7); visual QA via Playwright MCP (deviation #8).
- Enum named `RoleName` (not `RoleEnum` as drafted in plan.md) to match TDD §2.5's exact example name.
- T1 known transient failure: `RegistrationTest::test_new_users_can_register` fails after T1 because
  `username` is now required on `users` but Fortify's `CreateNewUser` doesn't set it yet — fixed by T6
  (registration fields). Tracked here so it isn't mistaken for a regression; must be green by end of T6.
- T2: removed kit's `WithoutModelEvents` trait from `DatabaseSeeder` — it would silently suppress the
  new `UserObserver` (auto-creates a `Wallet` on `User::created`) for every seeded user, breaking the
  §5.1b "one wallet per user" invariant for T10's demo seeder. Not a TDD deviation — just removing a
  kit default that conflicted with a TDD-mandated invariant.
- T4: scope limited to Sanctum token infrastructure (`RoleService::issueApiToken`/`activeRoleFromToken`)
  and a read-only `GET /api/v1/me`. `EnsureActiveRole` middleware and the role-selection
  controller/route stay in T5 per plan.md's file grouping. Verified via `route:list` + an authenticated
  curl call with a real Bearer token (manual tinker traversal of `currentAccessToken()` doesn't work —
  that accessor is only populated by the `auth:sanctum` guard during a real HTTP request).
- T5: `EnsureActiveRole` resolves the active role via one guard-agnostic `RoleService::resolveActiveRole()`
  — Sanctum `PersonalAccessToken` → token ability; anything else (session guard, or a stateful-SPA
  `TransientToken` if ever enabled) → session `active_role`. One middleware class satisfies §4.3 for
  both web and api, registered as the `active_role` alias in `bootstrap/app.php`.
- T5: a custom Fortify `LoginResponse` (bound in `FortifyServiceProvider::register()`) implements §4.2:
  admin → dashboard (no real admin dashboard yet — Sprint 5 — so it's the generic kit dashboard for
  now); exactly one owned role → session-set + dashboard; 2+ owned roles → `role.select`, never a
  dashboard. Zero owned roles (not a case the TDD defines) falls through to the generic dashboard,
  matching the kit's existing `AuthenticationTest` expectation for a bare factory user.
- T5: full TDD §8 API auth surface (`POST /api/v1/login`, `POST /api/v1/role/select`) is **not** built —
  plan.md's T4/T5 file lists only ever scoped `/api/v1/me` + the token-issuance service method, not a
  standalone API login flow. The TDD-mandated mechanism (a role-scoped Sanctum token enforced by
  `EnsureActiveRole`) is fully implemented and tested; only the convenience network endpoint to mint
  that token is deferred. Tests mint tokens directly via `RoleService::issueApiToken`.
- T5: new controllers follow TDD §2.5's `Http/Controllers/Web/` + `Http/Controllers/Api/` split exactly
  (`Web/RoleController`, already-existing `Api/MeController`). The kit's pre-existing
  `Controllers/Settings/*` controllers predate this convention and are left as-is — out of scope to
  relocate.
- Unrelated tiny lint fix surfaced while running `npm run lint` for this slice: an unused `Link` import
  in `resources/js/pages/settings/Profile.vue`, left over from the earlier 2FA/passkey strip commit.
  Committed separately (`fix(ui): remove unused Link import in profile settings`), not bundled into T5.
- T6: `username`/`phone` validation rules added as their own `usernameRules()`/`phoneRules()` methods on
  `ProfileValidationRules` rather than merged into the shared `profileRules()` — merging would have made
  them required on every profile *update* too, breaking the settings page (which never submits those
  fields). Verified `ProfileUpdateTest` only posts name/email; no regression.
- T6: no standalone `RegisterRequest` FormRequest class exists. Fortify's `CreatesNewUsers::create(array
  $input)` contract is called directly by Fortify's own internal controller — there is no FormRequest
  injection point for it. Validation is done manually via `Validator::make()->validate()` inside
  `CreateNewUser`, matching the kit's pre-existing pattern (`PasswordValidationRules`/
  `ProfileValidationRules` traits). Not a TDD violation — the TDD never mandates a literal FormRequest
  class for this specific Fortify extension point, and §10's validation rules are still fully enforced.
- T6: extracted `RoleService::resolvePostAuthRedirect()` as the single shared §4.2 decision point reused
  by both `LoginResponse` and the new `RegisterResponse`, so registering with 2+ roles routes to
  `role.select` exactly like multi-role login does, instead of duplicating the admin/single/multi branch
  in two Response classes.
- T6: registration form now lets the user pick role(s) via checkboxes (TDD line 704 "user registers,
  picks role"). At least one role is required; multiple roles are allowed in one registration.
- T7: no `/api/v1` mirror built, matching T5's precedent — plan.md's T7 file list never lists an Api
  controller for reviews, only the web Inertia surface (`Web\AppReviewController`). TDD §8 documents
  `GET /reviews` / `POST /reviews` as public, which the web routes alone already satisfy.
- T7: the 1–5 rating input is a plain native `<input type="radio">` group, not the shadcn `Select`
  (confirmed it *could* bubble through the native `<Form>` via its `name` prop) — simpler and more
  semantically correct for a small discrete choice; no shadcn "rating" component exists to substitute
  for, so this isn't a Golden Rule 12 violation.
- T7: shadcn `Textarea` was installed (`npx shadcn-vue add textarea`) ahead of its originally-planned T8
  slot, since the review comment field needed a real multi-line input now. Installed before use per
  Golden Rule 12, not hand-rolled.
- T7: discovered this app's `bootstrap/app.php` registers `shouldRenderJsonWhen(fn ($r) =>
  $r->is('api/*'))`, overriding Laravel's default `expectsJson()` check. Consequence: a failed
  FormRequest validation on any web-only route (like `/reviews`, which has no API mirror) always
  renders as a 302 redirect with flashed session errors — never a 422 JSON body, regardless of an
  `Accept: application/json` header. The initial test wrongly assumed `postJson()` + `assertStatus(422)`
  would work; it doesn't, and chasing the resulting `assertStatus` failure-message crash wasted time
  before the real cause (wrong assumption, not an app bug) was found. Fixed by testing the same way
  `RegistrationTest.php` already does for web validation: `assertSessionHasErrors('rating')` +
  `assertDatabaseMissing`. No app code changed for this — it's the correct, intentional behavior given
  T7's web-only scope.
- T8: extended `HandleInertiaRequests::share()` to expose `auth.roles` (owned role values) and
  `auth.activeRole` (server-resolved via `RoleService::resolveActiveRole()`) on every page, plus a
  matching `Auth`/`RoleName` TS type. Required so the role badge/switcher and the Pinia `authStore`
  can read role state without ever trusting a client-set value (Golden Rule 4) — `authStore` is a thin
  computed wrapper over `usePage().props.auth`, no client-side mutation of the active role.
- T8: `getRoleNavItems`-style per-role nav differentiation was deliberately NOT built. Every role
  currently has exactly one real destination (`/dashboard`); T9 introduces the actual per-role
  routes (catalog, products, deliveries), so a role-aware nav helper would have been dead scaffolding
  today. The "role-aware sidebar" requirement is satisfied by the `RoleBadge` (shows the active role's
  label) and switcher (`Link` to the existing T5 `role.select` flow) mounted in `AppSidebar`'s header.
- T8: dropped the kit's `NavFooter` (GitHub/Laravel-docs links) from `AppSidebar` and deleted the
  now-unused component — irrelevant chrome for SEAPEDIA end users, inconsistent with rebranding
  `AppLogo` from "Laravel Starter Kit" to "SEAPEDIA".
- T8: swapped `app.ts`'s default/`settings/*` layout from the kit's `AppLayout` to the new
  `DashboardLayout`, and `reviews/*` from `null` to the new `GuestLayout` (trimmed that page's own
  "back to home" header chrome to avoid double nav, since `Navbar`/`Footer` now own that). `Welcome.vue`
  and `role/*` intentionally stay on `null` layout — the landing page redesign is T9's scope, and the
  role-selection modal is meant to render chrome-less.
- T8: found and fixed a real responsive bug during Playwright QA — `BottomNav` used `md:hidden`
  (hides at `min-width: 768px`) while the shadcn `SidebarProvider`'s mobile detection uses
  `max-width: 768px` (inclusive). At exactly 768px both were simultaneously true, so neither the
  sidebar nor the bottom nav was reachable. Changed `BottomNav` to `min-[769px]:hidden` to exactly
  complement the sidebar's breakpoint.
- T8: Playwright QA also surfaced pre-existing Vue hydration-mismatch console warnings on `/dashboard`
  (`inertia.ssr.enabled` was already `true` in the kit's `config/inertia.php`, predating T8). Cause:
  `PlaceholderPattern.vue`'s SVG pattern IDs are non-deterministic between the SSR pass and the client
  pass, and the sidebar's open/closed icon can differ by viewport at SSR vs hydration time. Not
  introduced by T8 (component untouched), check-only per Vue's own warning (no functional break, no
  production DOM rewrite) — left as-is; out of scope to fix SSR determinism in a UI-foundation slice.
- T8: per request, Playwright visual QA breakpoints extended to 360/768/1280/**1920** (not just the
  plan's 360/768/1280) — the build target display for the competition demo is desktop 1920×1080.
  Apply this extra breakpoint to T9's visual QA pass too.
- T8: registered Pinia in `app.ts` via Inertia's `withApp` hook (`app.use(createPinia())`) rather than
  adding an explicit `setup()` callback — the kit's `createInertiaApp` call had no `setup` already, and
  `withApp` is the documented zero-`setup` way to extend the Vue app instance in this Inertia version.
- T8: a `vue-tsc --noEmit` pass (first real type-check run on this project) surfaced 8 pre-existing
  errors, none caused by T8's diff. Fixed as part of this slice's "format/lint/types before commit"
  step: deleted `components/AppHeader.vue` + `layouts/app/AppHeaderLayout.vue` — the kit's unused
  alternate "header" layout variant, confirmed unreferenced anywhere (this app only ever uses the
  sidebar variant), which is why their `auth.user` usage had never been null-checked. Added a `!`
  non-null assertion to `NavUser.vue`/`Profile.vue`'s `user` computed — both only ever render behind
  the `auth` middleware, so `auth.user` is never actually null there, but the shared `Auth.user: User |
  null` type doesn't know that. `npm run lint`, `vue-tsc --noEmit`, `pint`, `artisan test` (27/27), and
  `npm run build` all pass clean after this fix.
- T8: per request, the `sail npm run lint` / `npm run format` / `npx vue-tsc --noEmit` / `npm run
  build` commands are now run by Claude directly going forward (same carve-out as the existing
  test/pint/wayfinder delegation) — `npm run dev`/`migrate`/`seed`/`shadcn-vue add` remain user-run.
- Owner requested an Indonesian/English UI language toggle right after T8 shipped. It was briefly
  planned as a Sprint 1 T9 (`docs(planning): add sprint1 T9 i18n task`), then the owner decided to
  defer it instead ("kayaknya skip ke sprint 2 aja") — reverted before any code was written; see
  deviation #9 in `plan.md`. T9/T10 below are back to their original Pages/Demo-seeder scope.
