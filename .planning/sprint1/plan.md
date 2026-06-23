# Sprint 1 Plan — Infra-light Foundation + Level 1 (auth · multi-role · reviews · UI kit)

> Maps to TDD §13 "Day 1". All implementation references trace to `SEAPEDIA_TDD.md`.
> Deviations from the TDD are flagged explicitly in **"Decisions beyond / different from the TDD"** below.

## Goal

A guest can browse landing → catalog → product detail and submit an app review (rendered as
plain text, never `v-html`). A user can register (with username/phone), log in, and — if they own
multiple non-admin roles — is held at a **role-selection modal** before any dashboard loads.
Active role is resolved **server-side** (session for web, role-scoped Sanctum token for `/api/v1`),
enforced by `EnsureActiveRole` (403 on mismatch), shown as a top-bar badge, and switchable. Each
role lands on its own dashboard shell showing a **balance placeholder**. Everything runs on
Sail/local — no deployment this sprint.

## Scope (Level 1 criteria covered)

- Basic auth — register / login / logout (Fortify, §5.0) + Sanctum token issuance for the API.
- Multi-role correctness trap (§4) — owned roles, per-session active role, role selection on
  multi-role login, `EnsureActiveRole` 403 gating, never trust role from request body.
- "token / JWT / session" question → answered: **Sanctum** (§5.0).
- "balance across roles" → unified single wallet entry point + placeholder per-role summary (§5.1b).
- Public app reviews — guest allowed, text-rendered (§13 Day 1).
- Reusable UI foundation — 4 pts + UI bonus (§11): layouts, responsive nav, role-aware shells.

## Locked decisions referenced

- §4 — active role is per-session state, NOT a DB column; authz is on active role, not owned roles.
- §5.0 — Sanctum (session for web, opaque role-scoped token for API). JWT deliberately not used.
- §5.1 — money is integer IDR (`BIGINT UNSIGNED`).
- §5.1b — one wallet per user, shared across roles; movement via `wallet_transactions` (table itself
  deferred to Sprint 3 — see deviation #6).
- §2.5 — project structure & naming; §7 — schema; Golden rules 4, 8 (no `v-html`), 11, 12, 13.

## Decisions BEYOND / DIFFERENT FROM the TDD (must stay visible)

1. **🔀 Deployment moved Day 1 → final sprint (Sprint 6).** The TDD calls live GCP deploy "the single
   highest-leverage thing today." Per project owner's decision, Sprint 1 is **local/Sail only** and
   ships no prod infra (no `docker-compose.prod.yml`, nginx, caddy, or GCP). *Risk accepted: lose the
   early-deploy safety net; deploy is now a Sprint 6 task.*
2. **🔀 Visual identity = "Ocean" palette selected by `ui-ux-pro-max`** (filtered marketplace + ocean,
   accessibility/contrast-scored) and refined with the `frontend-design` skill. The TDD leaves the
   palette open (§11 only mandates shadcn-vue + one accent). **Final chosen hex tokens will be
   recorded here once the palette step runs in T8.**
3. **ℹ️ Auth is Fortify** (the Vue starter kit's built-in), not hand-written. The TDD says "extend the
   built-in auth"; Fortify *is* that built-in. 2FA / passkey / password-reset already stripped (git
   history: `chore(auth): strip 2fa, passkey and reset flows`).
4. **ℹ️ Models use PHP 8 attributes** `#[Fillable(...)]` / `#[Hidden(...)]` (kit convention, see
   `app/Models/User.php`) instead of the `$fillable` array property. The DoD wording says "$fillable";
   we follow the kit's attribute style consistently across all models.
5. **➕ `users` table extended** with `username` (UNIQUE), `phone`, `is_admin` (bool, default false).
   The kit's default `users` table lacks these; TDD §7 requires them.
6. **⏸️ `wallet_transactions` deferred to Sprint 3.** Day 1 lists wallets as the "entry point" only.
   Sprint 1 seeds the `wallets` table (balance) and the per-role balance summary is a **placeholder**;
   real ledger + top-up arrive in Sprint 3.
7. **➕ Process: implementation runs on Claude Sonnet.** Opus authored/approved this plan; the slices
   T1–T10 are implemented in this session after the owner switches `/model` → Sonnet. The TDD says
   nothing about model choice — this is a workflow decision. See "Instructions for the Sonnet
   implementer" below.
8. **➕ Process: visual QA via Playwright MCP.** UI slices (T8, T9) and the end-of-sprint demo are
   verified by driving a real browser through the Playwright MCP server (navigate, screenshot,
   check responsive breakpoints), not by eyeballing code alone. The TDD asks for empty/error/loading
   states and responsiveness (§11) — Playwright is how we prove it.

## Instructions for the Sonnet implementer (READ FIRST)

You are implementing Sprint 1. Opus already wrote and the owner approved this plan — do not re-plan.

- **Authoritative sources, in order:** this `plan.md` → `SEAPEDIA_TDD.md` (§ cited per task) →
  `CLAUDE.md` golden rules. If they ever conflict, the TDD wins; flag the conflict in `progress.md`.
- **One slice = one commit.** Implement T1→T10 in order. Do not batch multiple tasks into one commit.
- **Follow the `vertical-feature` skill order** for every slice (migration → model → factory/seeder →
  FormRequest → policy → service → controller → route → api → page → test → commit).
- **Honor the golden rules:** thin controllers, logic in Services, Eloquent only, no `v-html` on UGC,
  integer IDR, Policies for ownership, `EnsureActiveRole` for role-gating, active role resolved
  server-side. Models use the kit's `#[Fillable]` attribute style (see deviation #4).
- **Before every commit:** run `./vendor/bin/sail pint` and `npm run lint`; run the `code-review`
  skill as a self-check; run relevant Pest tests. A slice is not done until format + lint + tests pass.
- **Use the skills:** `vertical-feature` (each slice), `money-and-checkout`/`order-lifecycle` (later
  sprints, n/a here), `commit-message` (every commit), `ui-ux-pro-max` + `frontend-design` (T8/T9
  visuals), `security-pass` (light check on the review/auth slices).
- **Update `progress.md`** — check off each task and its tests as you commit. Record the final Ocean
  palette hex tokens into deviation #2 of this file after T8.
- **Any new deviation from the TDD you introduce MUST be added to the deviations list above**, with
  the reason — that is a hard requirement from the owner.
- **Pushing:** commit locally only. The environment has no GitHub credentials; the owner pushes.

## Visual verification (Playwright MCP)

For T8, T9, and the demo, after assets build (`sail npm run dev`), drive the app via the Playwright
MCP server (`mcp__playwright__*` tools):

1. Navigate to each new/changed page (landing, catalog, product detail, login, register, each role
   dashboard, the review page).
2. Screenshot at **360px, 768px, 1280px** (TDD §11 responsive targets) — confirm no overflow/broken
   layout; mobile shows `BottomNav`, desktop shows sidebar.
3. Verify the **empty / loading / error states** render (e.g. catalog with no items, review list empty).
4. Verify the **active-role badge + role-switcher** flow visually: multi-role login → role modal →
   pick role → correct dashboard → switch role.
5. Light **dark-mode** pass (kit's `HandleAppearance` is wired).
6. Attach/notes the screenshots in the slice's verification; fix layout issues before committing T8/T9.



## shadcn-vue components needed

Already installed (`resources/js/components/ui/`): button, input, card, badge, dialog, select,
dropdown-menu, avatar, sidebar, sheet, separator, navigation-menu, label, alert, sonner, skeleton,
tooltip, breadcrumb, collapsible, checkbox.

**To `npx shadcn-vue@latest add` as the FIRST UI step (T8), before any import:**
`textarea`, `table`, `pagination`, `tabs`.

## Task breakdown (ordered vertical slices — one commit each)

Each slice follows the `vertical-feature` skill order. Run `pint` + ESLint/Prettier before each commit
(`code-review` skill as the self-check). Commit messages follow the `commit-message` skill.

- **T1 · Roles foundation**
  - Files: migration extend `users` (username/phone/is_admin) + create `roles`, `role_user`;
    `app/Enums/RoleEnum.php`; `User` ↔ roles relations + helpers (`hasRole`, `ownedRoles`);
    `RoleFactory`; `RoleSeeder` (buyer, seller, driver).
  - Rules: §7 schema; §4.1 owned roles via pivot; admin via `users.is_admin`.
  - Acceptance: `migrate:fresh --seed` creates 3 roles; a user can own multiple via pivot.
  - Tests: none (covered by T5).
  - Commit: `feat(role): add roles, pivot and user relations`

- **T2 · Wallet entry point**
  - Files: `wallets` migration (user_id UNIQUE, balance BIGINT UNSIGNED default 0); `Wallet` model;
    `User` 1:1 relation; `WalletFactory`; auto-create wallet on user creation (observer or seeder).
  - Rules: §5.1 integer IDR; §5.1b one wallet per user. (No transactions table — deviation #6.)
  - Acceptance: every seeded user has exactly one wallet row.
  - Tests: none.
  - Commit: `feat(wallet): add wallet table and user relation`

- **T3 · Settings table**
  - Files: `settings` migration (key UNIQUE, value text); `Setting` model; seeder placeholder row.
  - Rules: §5.7 (table only; `ClockService` deferred to Sprint 5).
  - Acceptance: settings row readable/writable by key.
  - Commit: `feat(db): add settings key-value table`

- **T4 · Sanctum API tokens**
  - Files: token issuance on role select (`createToken('session', ['role:'.$role])`); `/api/v1/me`
    controller + route; API `EnsureActiveRole` reads token ability.
  - Rules: §5.0 role-scoped opaque tokens; §4.3 token ability carries active role.
  - Acceptance: token minted for `buyer` reports active role buyer via `/api/v1/me`.
  - Tests: buyer-scoped token cannot hit a seller-only API route (in T5 suite).
  - Commit: `feat(auth): issue role-scoped sanctum tokens`

- **T5 · Active-role core**
  - Files: `RoleController` (select / switch); session `active_role`; `EnsureActiveRole` middleware
    (web + api) returning 403 on mismatch; role-selection modal component; login redirect logic
    (§4.2: admin→admin, 1 role→auto-select, multi→modal).
  - Rules: §4 in full; never trust role from request body.
  - Acceptance: multi-role login shows modal, no dashboard leak; mismatched role → 403.
  - Tests (Pest): multi-role login → role-selection (no dashboard); single-role → auto-select;
    `EnsureActiveRole` 403 on mismatch; buyer token blocked from seller API route.
  - Commit: `feat(role): enforce active-role via EnsureActiveRole middleware`

- **T6 · Registration fields**
  - Files: extend Fortify `CreateNewUser` + registration FormRequest (username UNIQUE, phone);
    assign chosen role(s) on register; wallet auto-create.
  - Rules: §7 users columns; §10 validation per FormRequest.
  - Acceptance: register with username/phone persists; duplicate username rejected (422).
  - Tests: duplicate username rejected (FormRequest).
  - Commit: `feat(auth): capture username and phone on registration`

- **T7 · Public reviews**
  - Files: `app_reviews` migration (user_id nullable, reviewer_name, rating 1..5, comment);
    `AppReview` model + `AppReviewFactory`; `StoreAppReviewRequest`; `AppReviewController`
    (guest `store` + paginated `index`); public Inertia page (form + list, **text only, no v-html**).
  - Rules: §13 guest reviews; Golden rule 8 (no v-html on UGC); §10 validation.
  - Acceptance: guest submits valid review → appears in list as text; rating outside 1..5 rejected.
  - Tests (Pest): guest can submit valid review; invalid rating → 422.
  - Commit: `feat(review): add public app reviews with guest submission`

- **T8 · UI foundation**
  - Files: `add` textarea/table/pagination/tabs; apply Ocean palette to Tailwind 4 `@theme` +
    shadcn CSS vars (light + dark; kit's `HandleAppearance` already wired); `GuestLayout`,
    `DashboardLayout` (role-aware sidebar); wrappers `Navbar` (guest/authed), `Footer`, `BottomNav`,
    `EmptyState`, `StatCard`; active-role badge + switcher; Pinia `authStore` (user, roles, activeRole).
  - Rules: §11; Golden rules 11–13, 17 (components presentation-only); responsive 360/768/1280.
  - Acceptance: layouts render guest vs authed; role switcher re-runs selection; dark mode works.
  - Skills: `ui-ux-pro-max` (palette + layout/UX review), `frontend-design` (visual refinement).
  - **Visual QA (Playwright MCP):** screenshot layouts + role badge/switcher at 360/768/1280 per the
    "Visual verification" section; fix layout issues before commit.
  - **Record final Ocean hex tokens back into deviation #2 once chosen.**
  - Commit(s): `feat(ui): add ocean palette and layout foundation`

- **T9 · Pages**
  - Files: Landing, catalog (dummy data ok), product detail, role-aware dashboard shells
    (Admin / Seller / Buyer / Driver) with balance placeholder + empty/loading/error states.
  - Rules: §11; §13 Day 1 pages; dummy data acceptable (real catalog = Sprint 2).
  - Acceptance: each role sees its own shell; guest browses all public pages; states present.
  - **Visual QA (Playwright MCP):** navigate every page, screenshot empty/loading/error states at the
    three breakpoints; confirm bottom-nav (mobile) vs sidebar (desktop).
  - Commit: `feat(ui): add landing, catalog and role dashboards`

- **T10 · Demo seeder**
  - Files: `DatabaseSeeder` — `admin/password` (is_admin), `seller1`, `buyer1` (funded placeholder +
    default address can wait to S3), `driver1`, `multi1` (buyer+seller+driver). README credential block.
  - Rules: §12 (Level 1 subset — stores/products/orders/discounts come in later sprints).
  - Acceptance: `migrate:fresh --seed` yields a demoable multi-role login world.
  - Commit: `feat(db): seed demo users and roles`

## Tests (Pest feature — correctness-critical)

- Multi-role login returns role-selection, single-role auto-selects (T5).
- `EnsureActiveRole` returns 403 when active role ≠ route's required role (T5).
- Buyer-scoped Sanctum token cannot call a seller-only API route (T5).
- Guest can submit a valid review; invalid rating rejected by FormRequest (T7).
- Duplicate username rejected on registration (T6).

## Demo checklist (end of sprint)

1. Guest: landing → catalog → product detail → submit review → review shows as text.
2. Register a multi-role user → login → **role modal appears** → pick Seller → seller dashboard with
   active-role badge → "Switch role" → Buyer → buyer dashboard.
3. Single-role user logs in → skips modal → goes straight to their dashboard.
4. Logout → Sanctum token revoked (row deleted).
5. Responsive check at 360px / 768px / 1280px — **verified via Playwright MCP screenshots**.

## Risks / open questions

- Ocean palette exact tokens TBD until T8 runs `ui-ux-pro-max` — placeholder until then; will not block.
- Fortify customization surface: registering extra fields (username/phone) via `CreateNewUser` is the
  simplest §5-consistent path; if Fortify fights it, fall back to a thin custom register controller
  (document in README).

## Out of scope (deferred to later sprints)

- Real stores & products + catalog from DB → **Sprint 2**.
- `wallet_transactions`, top-up, addresses, cart, checkout → **Sprint 3**.
- Discounts, seller order processing, reports → **Sprint 4**.
- Driver, `ClockService`/clock advance, overdue sweep, admin dashboard → **Sprint 5**.
- **All deployment (prod Docker, nginx, caddy, GCP), security pass, Swagger polish, README
  finalize → Sprint 6** (deployment relocated here per deviation #1).
