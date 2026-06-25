# Sprint 6 Plan — Finalization: Security (L7), iPaymu drop, UI rework, API docs, Oracle deploy

> **Final sprint.** No Sprint 7. This sprint consolidates Level 7 (security + docs + demo data),
> reworks the user-facing UI in Final-Demo-Checklist order (starting with the landing page), drops
> iPaymu, finalizes the API docs + README, and deploys to Oracle Cloud Free Tier.

## Goal

When this sprint is done, SEAPEDIA is a **deployed, hardened, fully-documented** marketplace: a guest
hits a compelling landing page that sells the one-account/three-roles idea and shows live products +
public reviews; every private route is provably protected server-side by ownership + active role;
the dummy top-up reads as a real payment-gateway flow; the `/api/v1` surface is documented (OpenAPI/
Postman) and audited against the OWASP API Top 10; the README documents every locked rule + admin
creation + env + the live deploy URL; and the whole thing runs on an Oracle Cloud Free Tier VM that
the evaluator can open and test.

## Scope (challenge criteria covered — from `docs/SEAPEDIA_SPEC.md`)

This sprint closes **Level 7 (10 pts)** and banks the **Bonus (25 pts: UI 10 + Deploy 15)**, while
re-polishing earlier levels' UI so the end-to-end demo is clean.

- **L7A — Secure inputs, queries, public comments (4 pts):** SQLi prevention (Eloquent only), XSS
  prevention (no `v-html` on UGC — reviews especially), validate email/phone/rating/qty/price/stock/
  discount via FormRequests, reject dangerous input with clear errors.
- **L7B — Harden session & RBAC (3 pts):** logout invalidates token; no private route reachable by
  changing frontend routes; active role verified server-side for every Seller/Buyer/Driver/Admin
  action; no cross-user resource access (other seller's product / buyer's order / driver's job);
  documented token/session expiry.
- **L7C — Final docs & demo data (3 pts):** API docs (OpenAPI/Postman); seed accounts for all 4
  roles; document single-store checkout, discount-vs-PPN order, driver earning rule, overdue SLA +
  time simulation, and the security measures; short end-to-end testing guide.
- **Bonus UI (10 pts):** creative/intuitive UI — landing page + checklist-order rework.
- **Bonus Deploy (15 pts):** publicly accessible, evaluator-testable deployment.
- **Re-touched for demo cleanliness (already graded in earlier sprints):** L1A landing/catalog, L1C
  public reviews, L1B auth/role-select, L2 seller/store/catalog, L3 wallet/cart/checkout, L5 driver,
  L6 admin/overdue.

## Locked decisions referenced (TDD §5 — must honor, not re-open)

- **§5.0** Sanctum opaque token, scoped to active role → logout deletes the row (proves L7B "logout
  invalidates token"); set + document token expiry.
- **§5.1 / §5.1b** Money is integer IDR; one unified wallet, per-role summaries. No floats anywhere.
- **§5.2** Checkout calc order (PPN 12%) — kept as-is; **documented** in README (L7C).
- **§5.3** Voucher vs Promo distinction — **documented** (combinability rule + position vs PPN).
- **§5.4** Delivery methods/fees · **§5.5** driver earning rule · **§5.6** lifecycle transitions ·
  **§5.7** time simulation · **§5.8** single-store cart · **§5.9** overdue refund effects — all
  **documented** in README (L7C); no logic re-opened except where a security fix requires it.
- **Escrow deviation (Sprint 5):** seller income credited on `Pesanan Selesai`, not checkout — must
  be reflected in the README docs pass; reconcile TDD §5.2/§5.9 wording in the same docs pass.

## Locked Sprint-6 decisions (from brainstorming, this session)

1. **iPaymu DROPPED.** `SEAPEDIA_SPEC.md` awards **zero** points for a real gateway and calls top-up
   "dummy" (L3A + Final Demo Checklist). Keep the `PaymentGateway` interface + `FakeGateway` **only**;
   remove the planned real `IpaymuGateway` v2 call **and the `/webhook/ipaymu` route** (no external
   callback exists for an in-process dummy credit → smaller attack surface, documented in README).
2. **Dummy top-up is polished into a visible PSP-sim flow:** create top-up → "memproses pembayaran"
   page showing a reference id + polled status → credited + recorded in wallet history. Makes the
   gateway pattern legible to the evaluator without depending on any third party.
3. **Deploy = Oracle Cloud Free Tier**, Ampere A1 (ARM) via `docker-compose.prod.yml`. HTTPS optional
   (no webhook). **T11 is a guided, interactive deploy** (owner drives console + SSH, Claude walks
   each step + verifies). **Region:** prefer **Indonesia North (Batam)** *iff* offered as home region
   AND Always-Free-eligible (lowest latency for ID evaluators, newer region → better A1 capacity);
   else fall back to **Singapore `ap-singapore-1`**. Verify both in-console at T11. Home region is
   permanent.
4. **UI rework follows the Final Demo Checklist order:** Guest/Review/Auth → Seller → Buyer → Driver
   → Admin. The **landing page is the hero deliverable**.
5. **Commit bracket rule (memory: docs-commit-once-when-done):** one `docs(planning): add sprint6
   plan` at the start, `progress.md` edited locally throughout but committed **once at the end**
   (folded into the closing `docs: finalize sprint6 + README`), and **no `docs(planning): record …`
   mid-sprint**. The sprint's history must read as a contiguous run of `feat/fix/refactor/test`.
6. **No Playwright** visual QA (owner instruction since Sprint 5) — record the gap in `progress.md`;
   reason about layouts at 1280/1920 from the code instead.

## New tooling wired into this sprint

- **Layered security (T1 + ongoing):**
  - `security-pass` skill — the project's manual L7 checklist (read-only audit).
  - **`security-guidance` plugin (automatic, hooks)** — pattern warnings on Edit/Write, end-of-turn
    LLM diff review, and an **agentic commit-review on `git commit`** that traces data flow across
    files (IDOR / auth-bypass / injection). Stays ON for the whole sprint; the cross-file reviewer
    is the strongest check for L7B ownership/RBAC. (Adds LLM cost per turn/commit — accepted.)
  - **`/security-review`** — on-demand review of the branch diff, run at the end of T1 and before the
    final merge.
- **API docs via the Postman path (T9), replacing `l5-swagger`** (which the TDD itself flagged as
  risky on Laravel 13): `postman:generate-spec` (OpenAPI 3.0 from Laravel routes) →
  `postman:security` (OWASP API Top 10 audit) → `postman:docs` (publish). `postman:run-collection` /
  `postman:test` can smoke-test `/api/v1` for the demo.
- **Per-slice quality gates:** `/code-review` (plugin) + the project `code-review` skill before each
  commit; `simplify` for altitude/cleanup; `verify` before any "done" claim.

## shadcn-vue components needed

All already installed under `resources/js/components/ui/` (verified): `button, card, badge, avatar,
separator, input, textarea, label, select, dialog, tabs, table, breadcrumb, alert, skeleton, spinner,
sonner, sheet, navigation-menu, pagination, tooltip`. **No new `add` required.** Reviews carousel is
built as a responsive grid/marquee from existing primitives (no `carousel` component). If any slice
discovers a genuine gap, run `npx shadcn-vue@latest add <name>` as that slice's first UI step (rule
12) — do not substitute.

## Design direction (via `ui-ux-pro-max` + `frontend-design`)

Anchor on the **existing teal "sea" brand tokens** — no new palette (consistent with the Sprint 5
rework). Each UI slice below invokes `ui-ux-pro-max` (+ `frontend-design`) before building, and names
its **signature element** so pages don't read as templated shadcn defaults (golden rule 11a).

- **Palette:** existing `--sea` teal accent + neutral foreground/border tokens; status fill/accent
  tokens from Sprint 5 (`StatTile`/`StatBar`). Light + dark.
- **Type:** existing kit pairing; landing uses a larger display scale for the hero headline to carry
  the 3-second hook.
- **Icons:** **lucide only (`@lucide/vue` v1.21)** — **no emoji anywhere**.
- **Signature elements per page** are named in each task's Acceptance.

## Task breakdown (ordered vertical slices)

> Build order = security base first, then the iPaymu/top-up rework, then UI in checklist order, then
> docs/deploy/housekeeping. Each task is one (or a few tightly-related) conventional commit(s).

### T1 · Security hardening — Level 7A + 7B (layered)
- **Files:** audit-wide. Likely touches: `app/Policies/*`, `app/Http/Requests/*`, route middleware in
  `routes/web.php` + `routes/api.php`, `config/sanctum.php` (token expiry), any Vue component
  rendering UGC (review displays — confirm no `v-html`). Add Pest tests under `tests/Feature`.
- **Business rules:** §5.0 (token expiry + logout revoke), §4 active-role server-side, ownership
  policies (Seller→own product/store, Buyer→own order/cart/wallet, Driver→own job, Admin gate).
- **Acceptance (demoable):**
  - `grep` proves no `DB::raw`/`whereRaw` with interpolation and no `v-html` on user content.
  - `<script>alert(1)</script>` in a review comment renders as inert text.
  - `' OR 1=1 --` in login/search/review/checkout has no effect.
  - Cross-user URL access → 403 (other seller's product edit, other buyer's order, other driver's
    job). Hitting a seller route as active-buyer → 403. Non-admin → `/admin/*` → 403.
  - Logout → the issued Sanctum token is rejected on the next `/api/v1` call.
  - Token expiry value set in `config/sanctum.php` and noted for the README.
- **Tooling:** run `security-pass` skill checklist; let `security-guidance` review the diff + commit;
  finish with `/security-review` over the branch.
- **Tests:** `SecurityXssTest`, `SecuritySqliTest`, `OwnershipAccessTest` (cross-user 403),
  `RoleGateTest` (wrong active role 403), `TokenLogoutRevocationTest`.
- **Commit(s):** `fix(security): …` per discrete finding (e.g. `fix(security): enforce ownership on
  driver job routes`); `test(security): cover xss, sqli, ownership, role gating`.

### T2 · Drop iPaymu + polish dummy top-up into a visible PSP-sim flow
- **Files:** remove real-iPaymu code path + `/webhook/ipaymu` route + webhook controller/request;
  `app/Support/` gateway wiring (keep interface + `FakeGateway`); `WalletService` top-up; top-up Vue
  pages (`create` → `processing`/status → `return`), wallet history view. Update `.env.example`
  guidance (owner edits env, not Claude). Update `ipaymu-topup` skill references are **not** edited
  here (skill stays; we simply don't use the real path).
- **Business rules:** §5.1 integer money; top-up credit in `DB::transaction` + `lockForUpdate`;
  idempotent by top-up reference (no double credit on repeated status poll).
- **Acceptance:** buyer initiates top-up → sees a "memproses pembayaran" page with a reference id and
  a polled status → on success, balance increases and a wallet transaction row appears; refreshing
  the status page never double-credits. README notes top-up is dummy/in-process with no external
  callback. **Signature element:** the processing/receipt card (reference id + status chip).
- **Tests:** `DummyTopupFlowTest` (credits once, idempotent on repeat); remove/replace any webhook
  test.
- **Commit(s):** `refactor(wallet): drop real iPaymu path and webhook`, `feat(wallet): make dummy
  top-up read as a gateway flow`.

### T3 · Landing page (hybrid) + public reviews — Guest/Review
- **Files:** `resources/js/pages/Welcome.vue` (full redesign), small presentational components for
  hero/trust-band/role-cards/review-list under `resources/js/components/landing/`; review submit
  via existing reviews controller/FormRequest (guest allowed, §L1C); `routes/web.php` home payload
  (featured products + recent reviews, column-scoped).
- **Business rules:** L1A (looks like a marketplace, guest-browsable), L1C (public app reviews, guest
  allowed, rendered safely — **no `v-html`**).
- **Acceptance / layout (hybrid editorial-split + role-led + product proof, lucide icons, no emoji):**
  - **Split hero:** left = display headline **“Satu akun. Tiga peran.”** + subcopy + dual CTA
    `Jelajahi Katalog` / `Daftar` + micro-trust line; right = product/hero image collage from
    `public/images/banner/hero`, `side`, product photos.
  - **Trust band:** `ReceiptText` PPN 12% transparan · `ShieldCheck` escrow aman · `Truck` kurir
    kampus.
  - **"Lagi ramai" strip:** real featured products from the seeded catalog (proves a live market).
  - **Trio role cards:** `ShoppingBag` Buyer · `Store` Seller · `Truck` Driver — each lists what the
    role can do; hover/active interaction.
  - **Public reviews:** list/testimonial of recent reviews + a submit form (name, rating 1–5,
    comment); guest can submit; comments render as inert text.
  - Responsive at 360/768/1280/1920; empty states (no products / no reviews); dark mode.
  - **Signature element:** the asymmetric split hero with the product collage.
- **Tests:** `PublicReviewSubmitTest` (guest can submit; XSS-inert) if not already covered by T1.
- **Commit(s):** `feat(ui): redesign landing into role-led marketplace hero`, `feat(ui): add public
  reviews section to landing`.

### T4 · Auth pages (login / register / role-select) — Auth
- **Files:** `resources/js/pages/auth/*` + role-selection page/modal.
- **Business rules:** L1B — multi-role user must choose active role before any private dashboard;
  active role visible in UI.
- **Acceptance:** login/register/role-select redesigned on the sea palette (consistent with the new
  landing), clear guest→authed transition, error states; multi-role user is sent to role-select, not
  straight to a dashboard. **Signature element:** the role-select cards reuse the landing trio
  styling so the brand reads continuously.
- **Tests:** none new (flow covered by existing auth tests).
- **Commit:** `feat(ui): rework auth and role-selection screens`.

### T5 · Catalog + product detail + store page — Guest browse
- **Files:** catalog index/detail pages, public store page; controllers' public payloads
  **column-scoped** (carry-over from Sprint 2/4); wire **real product photos** + product re-theme
  (assets staged in `public/images/product`, `category`).
- **Business rules:** L1A/L2C guest browse; guests cannot mutate/checkout.
- **Acceptance:** catalog + detail + store pages use real imagery and the sea palette; public payload
  exposes only public columns (no internal fields); empty/loading states. **Signature element:**
  product card with real photo + store chip.
- **Tests:** `PublicStorePayloadTest` (no private columns leaked) if cheap; else assertion in T1.
- **Commit(s):** `feat(ui): re-theme catalog and product detail with real photos`, `fix(store):
  column-scope public store payload`.

### T6 · Seller pages — Seller
- **Files:** seller store form, product CRUD list/forms, incoming-orders + process action pages.
- **Business rules:** §5.6 process transition `Sedang Dikemas → Menunggu Pengirim` (logic untouched;
  UI only); unique store name validation surfaced.
- **Acceptance:** seller dashboard/store/product/incoming pages on the sea palette, clear states,
  process action obvious. **Signature element:** the incoming-order row with a one-tap process CTA +
  status timeline preview. (Medium pass — these are functional but visually pre-rework.)
- **Tests:** none new.
- **Commit:** `feat(ui): rework seller store, product, and order-processing pages`.

### T7 · Buyer pages — Buyer
- **Files:** cart, checkout (summary with subtotal/discount/delivery/PPN/total), order history/detail
  with status timeline; **`buyer/wallet/Show.vue` redesigned to a mobile card-list** (carry-over).
- **Business rules:** §5.2 PPN display order, §5.8 single-store cart messaging, §5.6 timeline; logic
  untouched, presentation only (golden rule 17).
- **Acceptance:** checkout summary renders the full money breakdown clearly; single-store conflict is
  explained in-UI; order timeline legible; wallet history is a card list on mobile (no horizontal
  table scroll). **Signature element:** the checkout summary "receipt" + the order status timeline.
- **Tests:** none new (money math covered by existing checkout tests).
- **Commit(s):** `feat(ui): rework cart, checkout summary, and order timeline`, `fix(wallet): make
  wallet history a mobile card list`.

### T8 · Driver + Admin light pass — Driver / Admin
- **Files:** driver + admin pages (already reworked in Sprint 5); breadcrumb i18n (carry-over).
- **Acceptance:** consistency sweep with the new landing/auth styling; fix any residual generic-shadcn
  bits; breadcrumbs localized. No structural redesign. **Signature element:** unchanged from Sprint 5
  (command-bar / dispatch rows) — just reconciled with the refreshed tokens.
- **Tests:** none new.
- **Commit:** `fix(ui): reconcile driver/admin styling and localize breadcrumbs`.

### T9 · API docs (Postman/OpenAPI) + OWASP audit — Level 7C (docs) + security
- **Files:** generated `openapi.yaml` (or `postman/specs/*`), any API-Resource shaping for repeated
  envelopes (carry-over), `routes/api.php` annotations as needed.
- **Steps:** `postman:generate-spec` (OpenAPI 3.0 from Laravel routes) → review/shape the core flows
  (auth, catalog, wallet/topup, cart, checkout, orders, delivery, admin) → `postman:security` (OWASP
  API Top 10 audit) → fix findings → `postman:docs` to publish. `postman:run-collection` as a smoke
  test.
- **Acceptance:** the `/api/v1` core flows are documented and openable by the evaluator; OWASP audit
  run with findings addressed or explained.
- **Tests:** none new (collection run serves as smoke test).
- **Commit(s):** `docs(api): generate OpenAPI spec for /api/v1 core flows`, `fix(api): address OWASP
  audit findings`, `refactor(api): shape repeated response envelopes via resources` (if needed).

### T10 · README + seed demo finalize — Level 7C (docs + demo data)
- **Files:** root `README.md`, `database/seeders/*` (verify 4-role demo accounts), `.env.example`
  guidance (owner edits actual env).
- **Acceptance:** README documents — setup/run, env vars, **admin account creation**, single-store
  checkout, discount combination + PPN 12% calc order, driver earning rule, overdue SLA + how to
  simulate the next day, the escrow income model, security measures (SQLi/XSS/validation/session/
  RBAC + token expiry + "no webhook, dummy in-process top-up"), a short **end-to-end testing guide**,
  and the **deploy link** (filled in after T11). Seed produces demo accounts for Admin/Seller/Buyer/
  Driver.
- **Tests:** none.
- **Commit:** `docs: finalize README with locked rules, security notes, and testing guide`.

### T11 · Oracle Cloud Free Tier deploy (guided, interactive) — full runbook

> **Region confirmed:** **Indonesia North (Batam)** `aFIT:AP-BATAM-1` is available and the A1 shape is
> Always-Free-eligible (verified in-console this session) → use Batam. Singapore stays the only
> fallback if Batam capacity is ever exhausted. Home region is permanent.
>
> **Roles:** Claude walks + verifies each step; the **owner** runs every console action and every SSH
> command (via `! <cmd>` in the session so output lands here). Claude **never** writes the prod `.env`
> (memory: no-claude-edit-env) — hands the owner key/value to paste.

**Artifacts to author first (commit before provisioning the VM):**
- `Dockerfile` (prod, multi-stage: PHP 8.3-fpm + composer install `--no-dev` + `npm ci && npm run
  build`; arm64-compatible base images).
- `docker-compose.prod.yml` — services: `app` (php-fpm), `web` (nginx **or** Caddy), `db` (mysql:8
  with a **named volume** so data survives restarts), optional `caddy` for auto-TLS. App listens on
  80 (and 443 if HTTPS).
- `docker/nginx/default.conf` (or `Caddyfile` if going HTTPS).
- `.env.example` updated for prod keys (owner copies → real `.env`). README documents both compose
  files (Sail dev vs prod).

**Step A — Create the Always-Free VM (Oracle console):**
1. Compartment + **Create Instance**. Name e.g. `seapedia-prod`.
2. **Image:** Canonical **Ubuntu 24.04**.
3. **Shape:** **Ampere `VM.Standard.A1.Flex`** (Always-Free-eligible) → expand the row and set
   **OCPU = 4, Memory = 24 GB** (the full Always-Free A1 allowance). If "Out of host capacity" on
   create, drop to **2 OCPU / 12 GB** (or 1/6) and retry; last resort = AMD `VM.Standard.E2.1.Micro`.
4. **Placement:** AD 1 Batam; **Capacity type = On-demand** (NOT Preemptible — it can be reclaimed
   anytime). Fault domain default; cluster placement group off.
5. **SSH keys:** "Generate a key pair for me" → **download BOTH** the private and public key (no
   private key = no SSH). Or paste the owner's existing `~/.ssh/id_*.pub`.
6. **Networking (Required):** Create new VCN + **public subnet**; **"Assign a public IPv4 address" =
   Yes** (this is the evaluator-facing IP).
7. **Storage (Required):** default boot volume (~47 GB) is fine.
8. **Review → Create.** Wait for **Running** + note the **Public IP**.

**Step B — Open the ports (the classic Oracle two-layer gotcha):**
1. **VCN Security List / NSG (console):** add **Ingress** rules — source `0.0.0.0/0`, TCP, dest ports
   **22, 80, 443** (22 usually already open).
2. **Ubuntu host iptables (over SSH):** Oracle's Ubuntu image ships a restrictive INPUT chain — open
   80/443 there too and persist:
   ```bash
   sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
   sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
   sudo netfilter-persistent save      # (install iptables-persistent if missing)
   ```

**Step C — Connect:**
```bash
chmod 400 ~/Downloads/<private-key>
ssh -i ~/Downloads/<private-key> ubuntu@<PUBLIC_IP>
```

**Step D — Install Docker + compose plugin (on the VM):**
```bash
sudo apt-get update && sudo apt-get install -y ca-certificates curl git
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker ubuntu      # then re-login so `docker` works without sudo
```
- **Add swap** (build memory safety on smaller shapes; skip if you took 24 GB):
  `sudo fallocate -l 4G /swapfile && sudo chmod 600 /swapfile && sudo mkswap /swapfile && sudo swapon /swapfile`.

**Step E — Deploy the app:**
```bash
git clone <repo-url> seapedia && cd seapedia
cp .env.example .env                # owner edits .env (APP_KEY, APP_URL=http://<IP>, DB_*, PAYMENT_GATEWAY=fake)
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan key:generate
docker compose -f docker-compose.prod.yml exec app php artisan migrate:fresh --seed
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app php artisan config:cache route:cache
```

**Step F — (Optional) HTTPS:** point a free hostname (`<IP>.nip.io` or duckdns) at the IP, set
`APP_URL=https://…`, and let **Caddy** auto-provision TLS (add the `caddy` service + `Caddyfile`).
Skippable — `http://<PUBLIC_IP>` already satisfies "accessible & testable" (no webhook needs HTTPS).

**Step G — Verify + record:**
- Open `http://<PUBLIC_IP>` → landing loads; log in as each seeded role; complete one checkout.
- `postman:run-collection` against the deployed base URL as an API smoke test.
- Paste the live URL + the seeded demo credentials into the **README** (T10).

- **Acceptance:** evaluator-reachable public URL serving the seeded app for all four roles; data
  persists across `docker compose restart` (named MySQL volume).
- **Tests:** manual smoke (landing, login per role, a checkout) + `postman:run-collection`.
- **Watch-outs:** A1 capacity (fallback above); MySQL **named volume** or data is lost on restart;
  `usermod` requires re-login; iptables must be persisted or it resets on reboot.
- **Commit(s):** `chore(deploy): add prod Dockerfile, nginx/caddy, and compose`, `docs(deploy):
  document Oracle Cloud Free Tier setup in README`.

### T12 · Housekeeping — move `planning/` → `docs/`, demo recording, close-out
- **Files:** move `planning/` into `docs/` (carry-over, owner instruction); record the demo;
  finalize `progress.md`.
- **Acceptance:** repo layout matches the owner's final structure; demo recording captured following
  the Final Demo Checklist; `progress.md` reflects the whole sprint (including the Playwright gap).
- **Commit:** `docs: finalize sprint6 progress and relocate planning into docs` (the **single**
  closing docs commit — progress.md lands here, not per-task).

## Demo checklist (end of sprint — mirrors `SEAPEDIA_SPEC.md` Final Demo Checklist)

**Guest / Review / Auth**
- [ ] Guest lands on the new hero, browses catalog + product detail.
- [ ] Guest submits an app review (rating + comment) without checkout; it renders safely.
- [ ] Register + login; multi-role user chooses active role before any dashboard.
- [ ] Private dashboards protected by active role (manual URL → 403).

**Seller**
- [ ] Create store (unique name), CRUD products, products appear in public catalog.
- [ ] Process order `Sedang Dikemas → Menunggu Pengirim`.

**Buyer**
- [ ] Dummy top-up reads as a gateway flow (reference + status → credited; no double credit).
- [ ] Manage address + cart (single-store enforced + explained); checkout shows subtotal, discount,
      delivery fee, PPN 12%, final total; order history/detail/timeline visible.

**Driver**
- [ ] Find / take / complete a job; earnings + history visible.

**Admin / Overdue / Security**
- [ ] Monitor users/stores/products/orders/discounts/deliveries/overdue; generate + view voucher/promo.
- [ ] Simulate next day → at least one auto refund/return demonstrated.
- [ ] SQLi + XSS test cases handled safely; RBAC enforced server-side; logout invalidates token.

**Bonus**
- [ ] UI is distinctive (not templated shadcn defaults), responsive, with empty/error/loading states.
- [ ] Public deploy URL reachable and testable; API docs openable; README complete.

## Risks / open questions

- **Oracle A1 capacity** in the chosen region may be exhausted → fall back to AMD micro; both are
  Always Free. Resolved live in T11.
- **Batam region eligibility** (home-region availability + Always Free) unverified from memory →
  confirm in-console at T11; Singapore is the documented fallback.
- **`security-guidance` PHP coverage** — its automatic patterns lean Python/JS; the language-agnostic
  classes (XSS, secrets, IDOR, auth-bypass, injection) still apply, and the manual `security-pass`
  skill covers the Laravel-specific items, so T1 does not rely on the plugin alone.
- **No Playwright** → visual regressions can slip; mitigated by reasoning at 1280/1920 from code and
  the owner's manual demo pass. Gap recorded in `progress.md`.
- **Postman MCP auth** may need `/postman:setup` (OAuth/API key) before T9; local OpenAPI auditing
  works without MCP if setup stalls.

## Out of scope (deferred / not built)

- **Real iPaymu** integration and the `/webhook/ipaymu` endpoint — removed, not deferred.
- **Background cron/queue daemon** for overdue — the manual admin trigger + artisan command already
  satisfy §5.7 / L6C.
- **Rewriting old (Sprint 1–5) git history** — the brief requires visible step-by-step commits; only
  the bracket rule (above) applies going forward.
- **i18n full sweep** beyond the breadcrumb carry-over — the ID/EN toggle remains its own future
  effort (memory: seapedia-i18n-request); not in this sprint.

## Instructions for the Sonnet implementer

- Switch `/model` → **Sonnet** before writing any feature code (execution model).
- Implement **task-by-task in order**, each via the `vertical-feature` skill where it touches the DB,
  the design skills (`ui-ux-pro-max` + `frontend-design`) at every UI step (golden rule 11a), and the
  relevant project skills (`security-pass` for T1, `money-and-checkout`/`order-lifecycle` only if a
  fix reopens that logic — otherwise UI/docs only).
- **Commit discipline (critical this sprint):** per-slice `feat/fix/refactor/test` commits only. **Do
  NOT** commit `progress.md` per task and **do NOT** create `docs(planning): record …` commits.
  Update `progress.md` locally; it ships in the single closing commit at T12.
- Before each commit: `/code-review` + project `code-review` skill, `simplify`, then `verify`; run
  `./vendor/bin/sail pint`, `npm run lint`, `npm run build`, `vue-tsc`, and the Pest suite — all must
  pass (memory: user-runs-routine-commands → Claude runs these; only `sail up`/`npm run dev`/
  `shadcn-vue add` stay owner-run).
- **No `.env` edits** (memory) — hand the owner key/value to set.
- **No Playwright** (memory) — note the visual-QA gap in `progress.md`.
- Reference this plan's **Locked Sprint-6 decisions** and the carry-overs folded from
  `planning/sprint5/plan.md` "Perlu dikerjakan next sprint".

## Perlu dikerjakan next sprint (carry-over)

**None — Sprint 6 is the final sprint.** Anything not completed here is a release-blocker to resolve
within this sprint, not a hand-off. If something genuinely cannot ship, record it in `progress.md`
under a "Known gaps at submission" heading with the reason.
