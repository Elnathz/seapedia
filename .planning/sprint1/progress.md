# Sprint 1 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [x] T1 · Roles foundation — `feat(role): add roles, pivot and user relations`
- [x] T2 · Wallet entry point — `feat(wallet): add wallet table and user relation`
- [x] T3 · Settings table — `feat(db): add settings key-value table`
- [x] T4 · Sanctum API tokens — `feat(auth): issue role-scoped sanctum tokens`
- [ ] T5 · Active-role core — `feat(role): enforce active-role via EnsureActiveRole middleware`
- [ ] T6 · Registration fields — `feat(auth): capture username and phone on registration`
- [ ] T7 · Public reviews — `feat(review): add public app reviews with guest submission`
- [ ] T8 · UI foundation — `feat(ui): add ocean palette and layout foundation`
- [ ] T9 · Pages — `feat(ui): add landing, catalog and role dashboards`
- [ ] T10 · Demo seeder — `feat(db): seed demo users and roles`

## Tests

- [ ] Multi-role login → role-selection; single-role auto-select (T5)
- [ ] `EnsureActiveRole` 403 on role mismatch (T5)
- [ ] Buyer token blocked from seller API route (T5)
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
