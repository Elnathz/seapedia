---
name: commit-message
description: Use when committing changes or writing a git commit message for SEAPEDIA. Enforces Conventional Commits (type(scope): subject) with this project's scopes, imperative present tense, and per-slice granularity.
disable-model-invocation: false
---
# Commit message convention (history is GRADED)

Format: `type(scope): subject`  — subject ≤ 50 chars, imperative present tense ("add", not "added"), no trailing period.
Optional body explains WHY/impact, not WHAT.

## Types

| type     | when                  |
| -------- | --------------------- |
| feat     | user-facing feature   |
| fix      | bug a user could hit  |
| refactor | no behavior change    |
| test     | add/modify tests      |
| docs     | documentation only    |
| chore    | tooling, config, deps |
| style    | formatting/whitespace |
| perf     | performance           |

## Scopes (this project)

auth · role · store · product · catalog · wallet · cart · checkout · discount · order · delivery · admin · overdue · report · ui · db · api · docker · deploy · security · docs

## Rules

1. One logical change per commit. Do not mix two unrelated features.
2. Commit per vertical slice, in TDD §13 order, so history reads like the build.
3. NEVER squash the whole project into one commit.
4. A feature's migration+model+service may share one commit; UI for the same feature can be its own commit.

## Examples

- feat(auth): add register, login, logout with hashing
- feat(role): enforce active-role via middleware
- feat(checkout): charge wallet and reduce stock atomically
- fix(overdue): prevent double refund with refunded_at guard
- test(delivery): cover concurrent take-job rejection
- chore(deploy): add production compose with nginx and caddy

## Pitfalls

- Don't use `feat` for invisible internal changes → use `chore`/`refactor`.
- Don't use past tense ("added", "fixed").
- Don't bundle unrelated work to "save commits" — granularity is the point being graded.

## Verification checklist

- [ ] `type(scope): subject`, ≤ 50 chars, imperative.
- [ ] Scope is from the list above.
- [ ] One logical change.
- [ ] Body (if any) explains WHY.
