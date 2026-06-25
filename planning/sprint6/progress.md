# Sprint 6 Progress — Finalization

> Final sprint. Update checkboxes **locally** as tasks land; this file is committed **once** at the
> close (T12), not per task. No `docs(planning): record …` commits mid-sprint (commit bracket rule).

## Tasks

- [x] **T1 · Security L7A/7B (layered)** — security-pass + security-guidance + `/security-review`;
  Pest: xss, sqli, ownership 403, role gate, token logout revocation.
- [x] **T2 · Drop iPaymu + polish dummy top-up** — remove real path + `/webhook/ipaymu`; PSP-sim flow
  (reference + status, idempotent credit).
- [x] **T3 · Landing page (hybrid) + public reviews** — split hero, trust band, featured strip, trio
  role cards, reviews list+form (no v-html, lucide only, no emoji).
- [x] **T4 · Auth + role-select rework**.
- [x] **T5 · Catalog + product detail + store page** — real photos/re-theme + public column scoping.
- [x] **T6 · Seller pages rework**.
- [x] **T7 · Buyer pages** — cart/checkout/timeline + `buyer/wallet/Show.vue` mobile card-list.
- [x] **T8 · Driver + Admin light pass** + breadcrumb i18n.
- [x] **T9 · API docs (Postman/OpenAPI) + OWASP audit** — 33 paths via l5-swagger; securitySchemes + servers added; SEC-001–006 fixed; 181 tests green. Commit `4f6ec48`.
- [x] **T10 · README + seed demo finalize** — Sprint 6 demo path, OWASP table, admin creation note, token expiry note, current status updated.
- [ ] **T11 · Oracle Cloud Free Tier deploy (guided)** — VM, ports (VCN + iptables), Docker, compose,
  migrate/seed, verify URL.
- [ ] **T12 · Housekeeping** — move `planning/` → `docs/`, demo recording, finalize this file.

## Deviations / notes (record here, do not commit until T12)

- **No Playwright visual QA** (owner instruction since Sprint 5) — layouts reasoned from code at
  1280/1920; this is an accepted gap, not an oversight.
- **iPaymu + webhook removed** (not deferred) — top-up is dummy/in-process; documented in README.

### T1 findings (security audit, full detail — for future-fix reference)

**Real gaps found and fixed:**
1. **IDOR — `DriverJobController@show` (web + api) had no ownership check.** Any authenticated
   driver could view any other driver's full job detail (order items, store name, driver name) by
   guessing/incrementing the delivery id. Fixed: `DeliveryPolicy::view()` added (available-to-anyone
   OR owning-driver-only) + `$this->authorize('view', $delivery)` wired into both controllers.
   Regression test: `tests/Feature/Security/OwnershipAccessTest.php`.
2. **No `/api/v1` token issuance/revocation route existed at all.** `RoleService::issueApiToken()`
   was only ever called from test setup, never from a real HTTP route — so L7B's "logout invalidates
   token" was structurally undemoable, and Postman/evaluator couldn't authenticate against `/api/v1`
   (relevant for T9). Fixed: added `POST /api/v1/login` (`AuthController@store`, via new
   `AuthService::loginViaApi`), `POST /api/v1/logout` (`AuthController@destroy`), and
   `POST /api/v1/role/select` (`RoleController@store`, rotates the pending token). Single-role users
   (and admins) get a role-scoped token immediately on login; multi-role users get a pending
   (no-ability) token and must call `/role/select` next. Covered by
   `tests/Feature/Security/TokenLogoutRevocationTest.php`.
3. **Sanctum `expiration` was `null` (tokens never expired).** Set to 480 min (8h) via
   `SANCTUM_TOKEN_EXPIRATION` env, default 480, in `config/sanctum.php`. Needs a line in README (T10).

**Audited and confirmed already solid (no change needed):** no `DB::raw`/`whereRaw` anywhere; no
`v-html` anywhere; every write FormRequest validates rating(1-5)/qty/price/stock/discount/email
correctly; Address/CartItem/Order/Product/Store policies all correctly own-only; admin routes
correctly gated by `is_admin` (not token ability); LIKE-search in `CatalogService` is parameter-bound
(safe despite the string interpolation look). Cross-user 403 already covered for store/product/
order/address/cart/admin by pre-existing Sprint 1-5 tests — only the driver-job one was missing.

**Known but deliberately NOT fixed in T1 (out of scope, belongs elsewhere):**
- **`StoreService::publicShow()` / `StoreController@show` leak the full `Store` row** (incl. internal
  `user_id`) **and full `Product` rows** (incl. `stock`, timestamps) to any guest on the public store
  page — no column scoping at all. This is excessive data exposure (OWASP API3:2023), not an L7A
  input-validation bug, and is explicitly T5's job ("public payload column-scoped, carry-over from
  Sprint 2/4"). **Do not forget this in T5** — `app/Services/StoreService.php:45-52`.
- **42 pre-existing PHPStan/Larastan errors** (`composer types:check`) in files this sprint didn't
  touch (BuyerAddressController, CheckoutController, SellerProductController, SetLocale,
  CheckoutService, DeliveryService, AdminMonitorService, etc.) — mostly `FormRequest::validated()`
  array-shape mismatches and missing generic type params. Pre-date Sprint 6 (confirmed via
  `git status` before any T1 edits). Not a runtime bug (full 178-test Pest suite passes), not part of
  CLAUDE.md's Definition of Done (`pint` is, `phpstan` isn't), out of scope for a security task — but
  flagged here so it isn't silently lost. If time allows late in the sprint, worth a dedicated pass;
  otherwise note under "Known gaps at submission" in T12.
- `/security-review` (security-guidance plugin, agentic diff review) ran clean — no High/Medium
  findings on the T1 diff (3 low-confidence/non-issues noted, none actionable).

### T2 findings (drop iPaymu, PSP-sim top-up)

- **No `/webhook/ipaymu` route ever existed** in this codebase (checked before touching anything) —
  the plan's "remove the webhook" item was a no-op; `IpaymuGateway.php` was a stub that only ever
  threw `RuntimeException` (never had a real HTTP/signing call to rip out). Deleted the stub file,
  removed `PaymentGatewayType::Ipaymu`, the `ipaymu` config block, and the `AppServiceProvider` match
  arm. `PaymentGateway` interface + `FakeGateway` are now the only payment code path.
- **DB schema still allowed `gateway = 'ipaymu'`** (`topups.gateway` was `ENUM('ipaymu','fake')`).
  Added migration `2026_06_27_020000_narrow_topups_gateway_enum.php` (raw `DB::statement` for the
  `MODIFY COLUMN`, since Schema Builder can't alter enum value sets) to narrow it to `ENUM('fake')` —
  pure schema hygiene, not a behavior change (the app never wrote `'ipaymu'`).
- **Real PSP-sim flow**: `FakeGateway::createTopup()` no longer credits synchronously — it's a no-op
  "kick off." A new `checkStatus()` resolves the top-up (credits + marks paid) once a real-time
  `payment.topup.processing_seconds` window (default 3s, configurable via `TOPUP_PROCESSING_SECONDS`)
  has elapsed since `created_at`, under `lockForUpdate` + the existing `processed_at` idempotency
  guard. New `GET buyer/wallet/topup/{topup}` (web + api) route polls/resolves it; new
  `TopupPolicy::view()` gates cross-buyer access. New page `buyer/wallet/topup/Show.vue` (the
  "receipt card" signature element) polls every 1.5s via `router.reload({only:['topup']})` until the
  status leaves `pending`.
- **Deliberate, narrow exception to golden rule 6** (`ClockService::now()` only): the processing-delay
  comparison uses real wall-clock `CarbonImmutable::now()` against `created_at` (which Eloquent always
  stamps with the real clock), not the simulated day-clock — comparing against `ClockService::now()`
  would be comparing two incompatible clocks (a multi-day simulated jump would make every pending
  top-up resolve instantly, which is harmless given the idempotency guard, but conceptually wrong).
  Documented inline in `FakeGateway::checkStatus()`.
- **Seeder fix required**: `BuyerDemoSeeder` depended on the old synchronous credit (it places a real
  checkout right after topping up). Fixed by setting `payment.topup.processing_seconds = 0` and
  calling `checkStatus()` immediately after `create()` inside the seeder — seeders don't need to wait
  through the buyer-facing UX delay.
- Tests: `tests/Feature/Wallet/TopupTest.php` rewritten (pending-on-create, delay-respected,
  resolves-and-credits-once, double-poll-no-double-credit, cross-buyer 403, direct gateway
  idempotency). Full suite 181/181 green, pint/phpstan-delta/eslint/prettier/vue-tsc all clean (no new
  phpstan errors vs the pre-existing 42).
- README still needs (T10): document "top-up is dummy/in-process, no external callback, no webhook
  by design" and the `TOPUP_PROCESSING_SECONDS` knob.

## Known gaps at submission

- _(fill in only if a task genuinely cannot ship — with the reason)_
