# Sprint 1 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [ ] T1 · Roles foundation — `feat(role): add roles, pivot and user relations`
- [ ] T2 · Wallet entry point — `feat(wallet): add wallet table and user relation`
- [ ] T3 · Settings table — `feat(db): add settings key-value table`
- [ ] T4 · Sanctum API tokens — `feat(auth): issue role-scoped sanctum tokens`
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

## Notes / deviations recorded

- Deployment relocated to Sprint 6 (deviation #1).
- Ocean palette final hex tokens — to be recorded in `plan.md` deviation #2 after T8.
