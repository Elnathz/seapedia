---
name: sprint-planner
description: Use at the START of any sprint, before writing feature code, to produce the written plan at planning/sprint{N}/plan.md. Always run this and pause for approval before implementing a sprint.
---
# Sprint planning (write the plan, then STOP)

When asked to start sprint N (or "plan sprint N"):

1. Read the matching Day N section in TDD §13, plus the SEAPEDIA challenge criteria for the level(s) that sprint covers, and §5 locked decisions.
2. Create `planning/sprint{N}/plan.md` with the structure below.
3. Create `planning/sprint{N}/progress.md` as an empty checklist mirroring the task list.
4. Commit: `docs(planning): add sprint{N} plan`.
5. **STOP. Do not write any feature code.** Print a short summary and ask for approval.
6. Only after approval, implement task-by-task using the `vertical-feature` skill, updating progress.md and committing per slice.

## `plan.md` required structure

```
# Sprint {N} Plan — {sprint title}

## Goal
One paragraph: what works end-to-end when this sprint is done.

## Scope (challenge criteria covered)
- Level X.Y — <criterion> (pts)
- ... (list each gradable item this sprint satisfies)

## Locked decisions referenced
- Link the §5 rules this sprint must honor (e.g. §5.2 PPN order, §5.8 cart rule).

## shadcn-vue components needed
- List every shadcn-vue component this sprint will use. These get `npx shadcn-vue@latest add ...`'d as the first UI step, before any import.

## Design direction (via `ui-ux-pro-max` / `frontend-design`)
- Run the `ui-ux-pro-max` skill (+ `frontend-design`) and record the chosen direction for this sprint's pages: palette (named hex), type pairing + scale, spacing rhythm, and the signature element each new page is built around. shadcn-vue is the substrate; this section is what keeps the pages from reading as templated defaults (golden rule 11a). Every UI task references this direction.

## Task breakdown (ordered vertical slices)
For each task:
- T{n}: <name>
  - Files: migrations/models/services/requests/policies/controllers/routes/pages to create or change
  - Business rules: which §5 / §4 rules apply
  - Acceptance: what must be demoable for this task to be "done"
  - Tests: Pest cases (esp. concurrency/idempotency) — or "none"
  - Commit: the conventional message

## Demo checklist (end of sprint)
- Bullet the end-to-end flow to verify (mirror the challenge's Final Demo Checklist items for this level).

## Risks / open questions
- Anything ambiguous, plus the simplest §5-consistent assumption you'll make if unanswered.

## Out of scope (deferred)
- What belongs to later sprints, so this sprint stays bounded.
```

## Rules

- Plans describe WHAT and in what order; they do not contain full implementation code.
- Keep tasks small enough to be one commit each.
- If the sprint covers two levels (Day 5), list both levels' criteria in Scope and group tasks per level.
- Never skip the approval pause.
