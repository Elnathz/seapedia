---
name: vertical-feature
description: Use whenever building or modifying any SEAPEDIA feature that touches the database (store, product, wallet, cart, checkout, order, delivery, discount, review, admin). Defines the exact file-by-file order to scaffold a full slice consistently.
---
# Building a vertical feature

Follow this order. Do not skip steps.

1. **Migration** — per TDD §7 schema. Index FKs. Money = BIGINT UNSIGNED.
2. **Model** — set `$fillable`, casts, relationships. Never `$guarded = []`.
3. **Factory + Seeder** — realistic demo data; wire into `DatabaseSeeder` (§12).
4. **FormRequest** — validate per §10 (types, ranges, required).
5. **Policy** (if the resource has an owner) — own-only access; register in `AuthServiceProvider`.
6. **Service method** — all logic here. If it mutates balance/stock/used_count/status → `DB::transaction` + `lockForUpdate`. Use `ClockService::now()` for time.
7. **Controller (web)** — thin; validate → service → Inertia render/redirect.
8. **Route** — behind `auth` + `EnsureActiveRole:<role>` (or `is_admin`) as required.
9. **API mirror** — for core flows, add `/api/v1` controller + Sanctum + Swagger annotation.
10. **Inertia page + Vue** — FIRST invoke the `ui-ux-pro-max` skill (+ `frontend-design` for visual direction) to decide palette/type/hierarchy/signature, THEN build on the shadcn-vue UI kit. Don't ship raw shadcn defaults (uniform StatCard grids, badge+count rows). Responsive; empty/error/loading states; never `v-html` user content.
11. **Pest test** — required for concurrency/idempotency-critical paths.
12. **Commit** — one focused conventional commit (see commit-message skill).

## Verification checklist

- [ ] Migration matches §7; FKs indexed; money is BIGINT UNSIGNED.
- [ ] Model has `$fillable`; no `$guarded = []`.
- [ ] Write path has a FormRequest; owned resource has a Policy.
- [ ] Money/stock/status mutation is inside a locked transaction.
- [ ] Time comes from ClockService, not now().
- [ ] Route is behind the correct role middleware.
- [ ] UI step ran through `ui-ux-pro-max`/`frontend-design` — deliberate palette/type/hierarchy/signature, not raw shadcn defaults.
- [ ] One conventional commit; files created/changed listed.
