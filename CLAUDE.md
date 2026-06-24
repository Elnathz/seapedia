# SEAPEDIA — Claude Code Operating Rules

You are building SEAPEDIA, a multi-role marketplace. The original challenge brief from the
committee is `docs/KetentuanPanitia.pdf` — the graded source of truth. `docs/SEAPEDIA_TDD.md`
is our detailed distillation of it. Read the TDD's Locked Decisions (§5) and Schema (§7) before
any task. They are authoritative.

## Source of truth (order of authority)

`docs/KetentuanPanitia.pdf` (the committee brief) is the ultimate authority — it is what gets
graded. `docs/SEAPEDIA_TDD.md` is our faithful distillation that fills in the HOW (locked values,
schema, decisions the brief leaves open).

When implementing any sprint, read sources in this order:
`planning/sprint{N}/plan.md` → the sprint's design doc (`docs/specs/...`, if one exists)
→ `docs/SEAPEDIA_TDD.md` (the § cited per task) → this `CLAUDE.md` (golden rules).
**If the TDD or a plan ever conflicts with the brief, the brief wins. Otherwise the TDD wins over
the plan/CLAUDE** — flag the conflict in `progress.md`.

## Stack

Laravel 13 (PHP 8.3) · Inertia · Vue 3 + TypeScript · shadcn-vue · Tailwind 4 · MySQL 8 · Docker · Sanctum · Pest · iPaymu (v2). TypeScript is kept loose (`any` allowed where it unblocks).

## Plan before you build (MANDATORY)

0. **Do not write feature code for a sprint until you have written its plan and I have approved it.** At the start of each sprint:
   - Read the relevant TDD sections + the `sprint-planner` skill.
   - Write the plan to **`planning/sprint{N}/plan.md`** (structure defined in the `sprint-planner` skill).
   - **STOP and wait for my approval.** Do not begin implementation in the same turn.
   - During the sprint, keep `planning/sprint{N}/progress.md` updated (check off tasks as committed).
   - The `planning/` folder is committed (`docs(planning): add sprint{N} plan`).

## Execution model (EVERY sprint)

- **Planning is done on Opus; implementation is done on Claude Sonnet.** After a plan is approved, the
  feature slices are implemented with the model set to Sonnet. Every sprint plan must carry an
  "Instructions for the Sonnet implementer" section. (Owner switches `/model` → Sonnet to implement.)
- **Every UI slice is designed with the design skills before it is built.** Invoke `ui-ux-pro-max`
  (+ `frontend-design` for aesthetic direction) at the UI step of every slice — see golden rule 11a.
  The sprint plan names the design direction (palette/type/signature) the slice will follow, not just
  the shadcn components it adds.
- **Every UI slice is visually verified with the Playwright MCP server** (`mcp__playwright__*`): after
  `sail npm run dev`, navigate each new/changed page, screenshot at **360 / 768 / 1280px**, and confirm
  empty/loading/error states, responsive nav (mobile bottom-nav vs desktop sidebar), and dark mode
  before committing. Every sprint plan lists its Playwright visual-QA checks.

## Golden rules

1. **Controllers are thin.** Validate via FormRequest → call ONE Service method → return Inertia/JSON. No business logic in controllers.
2. **All business logic lives in Services** (`app/Services`). Services are framework-agnostic and reused by both web and `/api/v1`.
3. **Any mutation of balance / stock / used_count / order status MUST run in `DB::transaction()` with `lockForUpdate()`** on the affected rows.
4. **Authorization via Policies** for ownership; **`EnsureActiveRole` middleware** for role-gating. Never trust role from request body. Active role is resolved server-side.
5. **Money is integer IDR** (`BIGINT UNSIGNED`). No floats.
6. **Time comes ONLY from `ClockService::now()`** — never `now()`/`Carbon::now()` directly in business logic.
7. **Order status changes only via `OrderService` using the locked transition table (§5.6).** No status string set directly on the model elsewhere. Every change writes an `order_status_histories` row.
8. **Eloquent only.** No `DB::raw`/`whereRaw` with interpolated input. **Never `v-html` on user-generated content.**
9. **Every write endpoint has a FormRequest** validating per §10.
10. **iPaymu sits behind the `PaymentGateway` interface** with a `FakeGateway` fallback. Checkout never calls a gateway — it pays from the wallet only.
11. **Use shadcn-vue components**; don't hand-roll buttons/inputs/dialogs/tables it provides. Type Inertia props with interfaces; loose TS is fine.
11a. **Design every UI slice with the design skills — not raw shadcn defaults.** Before building or reworking ANY page/component, invoke the `ui-ux-pro-max` skill (and `frontend-design` for visual direction). shadcn-vue is the component *substrate*; the design skills decide palette, typography scale, spacing, hierarchy, and the page's signature element so the UI doesn't read as a templated default. A grid of identical StatCards + badge-and-count rows is the failure mode this rule exists to prevent. This applies to new pages AND reworks, on every sprint.
12. **shadcn-vue: install before use.** A shadcn-vue component only exists after `npx shadcn-vue@latest add <name>` copies its files into `resources/js/components/ui/`. NEVER import a shadcn-vue component you have not added first. At the start of a sprint: (a) list the components the sprint needs in `plan.md`, (b) run the `add` command(s) as the first UI step, (c) confirm the files exist (`resources/js/components/ui/<name>/`), (d) only then import and use them. If a needed component isn't installed, run `add` — do not write a substitute or assume it's there.
13. **Follow the project structure (§2.5).** Each layer in its folder: logic in `app/Services`, ownership in `app/Policies`, validation in `app/Http/Requests`, fixed sets as `app/Enums` (no magic strings), web vs API controllers separated. Models singular, FormRequests `Store/Update...Request`, services `XxxService`.
14. **Format before every commit.** Run `./vendor/bin/sail pint` (PHP) and the kit's ESLint/Prettier (`npm run lint`/`format`) before committing. A slice is not done until both pass. Functions stay focused (~≤30 lines); no business logic in controllers or Vue components.
15. Any action that may be retried externally (payment webhook, overdue sweep, delivery completion, seller process order) must be idempotent.
    Repeated requests must not create duplicate effects.
16. Eager-load relationships explicitly.
    Avoid N+1 queries by using with() when rendering lists.
17. Vue components are presentation only.
    No business rules, money calculations, status transitions, or ownership checks inside components.

## Definition of Done (per vertical slice)

- Migration + model (`$fillable`) + factory + seeder entry. Fixed sets use Enums (§2.5).
- FormRequest + Policy (if ownership) + Service method (transactional where needed). Files placed per §2.5 structure.
- Controller (web) + route behind correct middleware. Add `/api/v1` + Swagger annotation for core flows.
- Inertia page **designed via the `ui-ux-pro-max`/`frontend-design` skills** (golden rule 11a) — built on the UI kit, but with a deliberate palette/type/hierarchy/signature, not raw shadcn defaults; responsive; has empty/error/loading states.
- UI slices are **visually verified via Playwright MCP** (screenshots at 360/768/1280, states, dark mode).
- A Pest feature test for any concurrency/idempotency-critical path.
- **`pint` + ESLint/Prettier pass.**
- One focused commit. Conventional message: `feat(scope): ...`, `fix(scope): ...`.

## Commit discipline (GRADED — the evaluator reads the history)

Commit per vertical slice, never squash. Use Conventional Commits: `type(scope): subject`.

- **Subject:** imperative present tense ("add", not "added"), ≤ 50 chars, no trailing period.
- **Types:** `feat` (user-facing feature), `fix` (bug a user could hit), `refactor` (no behavior change), `test`, `docs`, `chore` (tooling/config/deps), `style`, `perf`.
- **Scopes for THIS project:** `auth`, `role`, `store`, `product`, `catalog`, `wallet`, `cart`, `checkout`, `discount`, `order`, `delivery`, `admin`, `overdue`, `report`, `ui`, `db`, `api`, `docker`, `deploy`, `security`, `docs`.
- **Body (optional):** explain the WHY/impact, not the WHAT (the diff shows what).
- **Granularity:** one logical change per commit. A migration+model+service for one feature can be one commit; mixing two unrelated features in one commit is wrong.
- Commit in the development order of TDD §13 so the history reads like the build progression.
- Examples:
  - `feat(auth): add register, login, logout with hashing`
  - `feat(role): enforce active-role via EnsureActiveRole middleware`
  - `feat(checkout): charge wallet and reduce stock in a locked transaction`
  - `fix(overdue): guard double refund with refunded_at sentinel`
  - `test(delivery): cover concurrent take-job rejection`
  - `chore(docker): add production compose with nginx + caddy`
- The full convention also lives in the `commit-message` skill (§15.6).

## Commands (local dev uses Laravel Sail)

- Up: `./vendor/bin/sail up -d`  ·  Shell: `./vendor/bin/sail shell`
- Migrate+seed: `./vendor/bin/sail artisan migrate:fresh --seed`
- Dev assets: `./vendor/bin/sail npm run dev`  ·  Build: `./vendor/bin/sail npm run build`
- Tests: `./vendor/bin/sail artisan test`
- Format (run before commit): `./vendor/bin/sail pint` · `./vendor/bin/sail npm run lint`
- Advance simulated day: `./vendor/bin/sail artisan seapedia:advance-day`
- Production deploy uses `docker-compose.prod.yml` (see §12.5 / §16), not Sail.

## Do NOT

- Do not gold-plate or invent features beyond the claimed level.
- Do not add libraries without a concrete need stated in the TDD.
- Do not skip seeders or the README.
- Do not leave deployment or docs to the end — they ship incrementally.

## When unsure

Re-read TDD §5 (Locked Decisions). If a decision is genuinely missing, pick the simplest option consistent with §5, implement it, and note it in the README — do not block.
