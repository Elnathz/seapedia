---
name: code-review
description: Use before committing a SEAPEDIA slice, or when reviewing changes, to self-check logic, security, performance, readability, and consistency against this project's rules.
allowed-tools: Read, Grep, Glob
---
# Self code-review (5 layers)

## 1. Logic

- Edge cases handled (empty cart, zero stock, insufficient balance, expired/used voucher)?
- Money/stock/status mutations inside a locked DB::transaction?
- Idempotency guards present where required (refund, webhook, take-job)?

## 2. Security

- Eloquent only; no raw SQL with interpolation. No `v-html` on user input.
- FormRequest validates the input. Route gated by correct role / Policy.
- No secrets hardcoded; uses env.

## 3. Performance

- No N+1 (eager load relations used in views/reports).
- Queries hit indexes (status, sla_due_at, FKs).

## 4. Readability

- Logic in Services, not controllers. Functions focused (~≤30 lines).
- Clear names; comments explain WHY, not WHAT.

## 5. Consistency

- Follows TDD §5 locked decisions and the unified wallet model.
- Matches existing naming/structure; no new dependency without a TDD-stated need.

## Verification checklist

- [ ] 5 layers checked.
- [ ] Locked decisions (§5) respected.
- [ ] No N+1; indexes used.
- [ ] No raw SQL / no v-html on user content.
- [ ] Files placed per project structure (§2.5); enums used for fixed sets.
- [ ] `pint` + ESLint/Prettier pass.
