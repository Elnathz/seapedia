# Sprint 1 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Roles foundation — `feat(role): add roles, pivot and user relations`
- [x] T2 · Wallet entry point — `feat(wallet): add wallet table and user relation`
- [x] T3 · Settings table — `feat(db): add settings key-value table`
- [x] T4 · Sanctum API tokens — `feat(auth): issue role-scoped sanctum tokens`
- [x] T5 · Active-role core — `feat(role): enforce active-role via EnsureActiveRole middleware`
- [ ] T6 · Registration fields — `feat(auth): capture username and phone on registration`
- [ ] T7 · Public reviews — `feat(review): add public app reviews with guest submission`
- [ ] T8 · UI foundation — `feat(ui): add ocean palette and layout foundation`
- [ ] T9 · Pages — `feat(ui): add landing, catalog and role dashboards`
- [ ] T10 · Demo seeder — `feat(db): seed demo users and roles`

## Tests

- [x] Multi-role login → role-selection; single-role auto-select (T5)
- [x] `EnsureActiveRole` 403 on role mismatch (T5)
- [x] Buyer token blocked from seller API route (T5)
- [ ] Duplicate username rejected on registration (T6)
- [ ] Guest submits valid review; invalid rating → 422 (T7)

## Visual QA (Playwright MCP)

- [ ] T8 — layouts + role badge/switcher screenshotted at 360/768/1280
- [ ] T9 — all pages + empty/loading/error states screenshotted at 360/768/1280
- [ ] Dark-mode pass

## Notes / deviations recorded

- Deployment relocated to Sprint 6 (deviation #1).
- Ocean palette final hex tokens — to be recorded in `plan.md` deviation #2 after T8.
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
