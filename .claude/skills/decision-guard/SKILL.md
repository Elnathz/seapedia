
---
name: decision-guard
description: Use BEFORE implementing or recommending any change where more than one implementation is reasonable AND the choice affects business rules, money, user-facing behavior, or system state. Trigger this whenever a decision touches pricing/discounts/tax, order or job state transitions, role/permission enforcement, idempotency, concurrency/race conditions, refunds/payouts, data deletion, or any hard-to-reverse or destructive operation. Also trigger when the user asks "should I...", "which approach...", "is it safe to...", or when a change to one actor (admin/seller/buyer/driver) could silently affect another. Do NOT jump straight to the happy-path implementation. Force explicit reasoning about invariants, affected actors, edge cases, and reversibility first, then surface the tradeoff instead of silently picking the convenient option.
---
# Decision Guard

Most production bugs in multi-actor systems are not coding mistakes. They are
decisions made implicitly — one reasonable-looking option was chosen without
checking what rule it broke, who else it touched, or whether it could be undone.
This skill exists to make that decision explicit *before* code is written.

The core failure mode this prevents: **happy-path tunnel vision** — implementing
the obvious flow for the obvious actor and discovering later that a second actor,
a retry, a concurrent request, or a money rounding edge made it wrong.

If the decision is trivial and reversible (rename a variable, add a log line),
skip this. This is for decisions with consequences.

## The protocol

Run these six steps in order. Do not skip to step 6.

### 1. Name the decision and the real options

State the decision in one sentence, then list the *actual* competing options.
There is almost never only one option — if you think there is, you have not
looked. "Just add a flag" is a decision, not the absence of one.

### 2. Map the affected actors

List every role/actor that can read, write, or be affected by this. In a
marketplace that is usually more than one: a change to the buyer flow often
touches seller payouts, driver job state, and admin reporting. Ask: *who else
sees the state I am about to change?*

### 3. State the invariants that must hold

Write down the business rules that must stay true regardless of which option you
pick. Examples: "a paid order can never be deleted", "total = subtotal − discount

+ tax", "one driver per job", "a refund never exceeds the captured amount". These
  are your test oracle. If an option can violate one, it is wrong even if it
  compiles.

### 4. Trace edge cases and second-order effects

Walk the unhappy paths explicitly:

- **Concurrency**: two requests at once (double-accept, double-submit, double-pay)
- **Partial failure / retry**: the call ran twice, or died halfway through
- **Boundary values**: zero, negative, null, empty cart, 100% discount, rounding
- **Order of operations**: discount-then-tax vs tax-then-discount changes the total
- **Time**: timezones, expiry, "now" computed in two places
- **Cross-actor leak**: does this let one role do/see something it shouldn't?

### 5. Reversibility check

Classify the operation:

- **Safe**: pure read, or easily undone → decide freely
- **Costly**: writes state other actors depend on → decide carefully, log it
- **Irreversible / destructive**: deletes data, moves money, fires an external
  side effect (payment, email, webhook), or transitions a state with no path
  back → **do not decide silently. Surface it.**

Money movement and data deletion are always at least "costly". Treat them as
irreversible unless you can name the exact undo path.

### 6. Decide and surface the tradeoff

Do not just emit the convenient option. Present the recommendation *with* the
rule it protects, the risk it accepts, and the alternative. The user owns
consequential tradeoffs — your job is to make the tradeoff legible, not to hide
it inside an implementation.

## Output format

For any decision that reaches step 5 as "costly" or "irreversible", surface a
compact block before writing code:

```
DECISION: <one line>
OPTIONS: A — <option> / B — <option>
AFFECTS: <actors>
INVARIANTS AT RISK: <rules that could break>
EDGE CASES: <the unhappy paths that matter here>
REVERSIBILITY: safe | costly | irreversible — <why>
RECOMMENDATION: <choice> because <reason>; tradeoff is <what you give up>
```

Keep it terse. This is a gate, not a report. For "safe" decisions, a single
sentence naming the option and why is enough.

## Category heuristics

Quick checks for the decisions that bite most often.

**Money (price / discount / tax / refund / payout)**

- Compute on the server. Never trust a client-sent total.
- Fix and document the order of operations (e.g. discount on subtotal, then tax).
- Round once, at the end, in a defined direction. Inconsistent rounding = money
  that doesn't reconcile.
- A refund/payout is irreversible. Make it idempotent or you will pay twice.

**State machines (order / job / shipment lifecycle)**

- Draw the legal transitions. Reject everything not on the diagram.
- Enforce transitions in one place (server), not scattered across UI.
- Ask "what if the actor is in a stale state?" — the buyer cancels an order a
  driver already accepted; resolve the conflict on purpose, not by last-write.

**Permissions / roles**

- Enforce on the backend. Frontend hiding is UX, not security.
- Default deny. Check the actor *and* ownership of the specific resource.
- A change that adds capability to one role: re-check it didn't widen another.

**Idempotency / retries**

- Any operation that can be retried (network, queue, user double-click) and has a
  side effect needs an idempotency key or a uniqueness constraint.
- "It worked in testing" means it ran once. Assume it runs twice.

**Concurrency / race conditions**

- Shared resource grabbed by many (one driver per job, last stock unit): use a
  DB-level guard — transaction + row lock, conditional update, or unique
  constraint — not a read-then-write in app code.
- If your fix is `if (available) { take() }`, it is already broken.

## When to STOP and ask the user

Decide autonomously for safe and most costly decisions; **stop and ask** when:

- The operation is irreversible *and* the options have materially different
  outcomes (e.g. how to handle a cancel-after-payment).
- An invariant has no obviously correct resolution and it's a product/policy call,
  not a technical one ("should partial refunds be allowed?").
- Two of the user's own stated requirements conflict.

Asking here is not indecision — it is refusing to make a policy decision on the
user's behalf and bury it in code.

## Worked example

**Input:** "A buyer wants to cancel an order. Add a cancel button."

**Bad (happy-path tunnel vision):** add a button that sets `status = cancelled`.

**With this skill:**

```
DECISION: How does buyer cancellation behave across order states?
OPTIONS: A — allow cancel only while PENDING / B — allow anytime with rules per state
AFFECTS: buyer, seller (stock/payout), driver (active job), admin (reporting), payment gateway
INVARIANTS AT RISK:
  - a captured payment must be refunded, never just hidden
  - a job already taken by a driver can't vanish from under them
  - cancelled order must not still reduce seller stock
EDGE CASES:
  - buyer cancels the same instant the seller ships → who wins?
  - buyer cancels after driver accepted → driver must be released + notified
  - double-click cancel → must be idempotent (don't refund twice)
REVERSIBILITY: irreversible once payment is captured (refund) or driver dispatched
RECOMMENDATION: Option B. Gate cancellation by current state:
  PENDING → cancel freely; PAID → cancel + trigger refund (idempotent);
  PICKED_UP/EN_ROUTE → block, route to support. Tradeoff: more logic now, but
  Option A silently loses money and breaks driver state.
```

Then write the code — against the rules you just made explicit.

## Anti-patterns

- Silently choosing the option that's fastest to type.
- Treating a money, state, or permission change as routine.
- "I'll just add a boolean" — flags are decisions; trace them.
- Implementing the happy path and leaving edge cases as a TODO that never returns.
- Enforcing a rule in the UI only and calling it done.
- Hiding a real tradeoff inside an implementation so the user never gets to weigh it.
