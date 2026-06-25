# Sprint 6 Progress — Finalization

> Final sprint. Update checkboxes **locally** as tasks land; this file is committed **once** at the
> close (T12), not per task. No `docs(planning): record …` commits mid-sprint (commit bracket rule).

## Tasks

- [ ] **T1 · Security L7A/7B (layered)** — security-pass + security-guidance + `/security-review`;
  Pest: xss, sqli, ownership 403, role gate, token logout revocation.
- [ ] **T2 · Drop iPaymu + polish dummy top-up** — remove real path + `/webhook/ipaymu`; PSP-sim flow
  (reference + status, idempotent credit).
- [ ] **T3 · Landing page (hybrid) + public reviews** — split hero, trust band, featured strip, trio
  role cards, reviews list+form (no v-html, lucide only, no emoji).
- [ ] **T4 · Auth + role-select rework**.
- [ ] **T5 · Catalog + product detail + store page** — real photos/re-theme + public column scoping.
- [ ] **T6 · Seller pages rework**.
- [ ] **T7 · Buyer pages** — cart/checkout/timeline + `buyer/wallet/Show.vue` mobile card-list.
- [ ] **T8 · Driver + Admin light pass** + breadcrumb i18n.
- [ ] **T9 · API docs (Postman/OpenAPI) + OWASP audit** — generate-spec → security → docs.
- [ ] **T10 · README + seed demo finalize** — all locked rules + admin creation + security notes +
  testing guide + deploy link.
- [ ] **T11 · Oracle Cloud Free Tier deploy (guided)** — VM, ports (VCN + iptables), Docker, compose,
  migrate/seed, verify URL.
- [ ] **T12 · Housekeeping** — move `planning/` → `docs/`, demo recording, finalize this file.

## Deviations / notes (record here, do not commit until T12)

- **No Playwright visual QA** (owner instruction since Sprint 5) — layouts reasoned from code at
  1280/1920; this is an accepted gap, not an oversight.
- **iPaymu + webhook removed** (not deferred) — top-up is dummy/in-process; documented in README.

## Known gaps at submission

- _(fill in only if a task genuinely cannot ship — with the reason)_
