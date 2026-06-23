---
name: security-pass
description: Use on Day 6, or whenever finalizing a feature, to verify SQL injection, XSS, input validation, session, and authorization hardening before shipping. Read-only audit.
allowed-tools: Read, Grep, Glob
---
# Security pass (TDD §10)

Verify and fix:

- grep for `whereRaw`/`DB::raw` with interpolated input → none, or fully parameter-bound.
- grep for `v-html` → must never wrap user-generated content (reviews etc.).
- every write route has a FormRequest validating email/phone/rating(1-5)/qty/price/stock/discount.
- every private route passes auth + EnsureActiveRole (or is_admin); every owned resource has a Policy.
- Sanctum token expiry configured + documented; logout invalidates.
- webhook idempotent + reference-validated.
- demo: `<script>` in review renders inert; `' OR 1=1 --` in forms has no effect.

## Verification checklist

- [ ] No raw SQL with interpolated input.
- [ ] No `v-html` on user content.
- [ ] Every write endpoint validated by a FormRequest.
- [ ] Every private route + owned resource gated (middleware + Policy).
- [ ] Token expiry set; logout invalidates session/token.
- [ ] XSS + SQLi test cases demonstrated safe.
