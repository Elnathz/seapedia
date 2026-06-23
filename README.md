# SEAPEDIA

A multi-role campus marketplace — buyers, sellers, and drivers share one
platform and one wallet, with roles switchable per session. Built with
Laravel 13, Inertia + Vue 3 (TypeScript), shadcn-vue, Tailwind 4, and MySQL,
running on Docker via Laravel Sail.

Full product/technical decisions live in `SEAPEDIA_TDD.md`. Sprint plans and
progress are tracked under `.planning/sprint{N}/`.

## Setup

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

The app runs at `http://localhost`.

## Demo credentials

Seeded by `migrate:fresh --seed` (`DemoUserSeeder`). Every account's password
is `password`.

| Username  | Role(s)                | Notes                                   |
| --------- | ----------------------- | ---------------------------------------- |
| `admin`   | Admin (`is_admin`)      | Lands on the admin dashboard shell       |
| `seller1` | Seller                  | Single role — skips the role-select step |
| `buyer1`  | Buyer                   | Wallet pre-funded with a placeholder balance (Rp 500.000) |
| `driver1` | Driver                  | Single role — skips the role-select step |
| `multi1`  | Buyer, Seller, Driver   | Multi-role — shows the role-select modal on login |

## Demo path (Sprint 1)

1. Guest: landing (`/`) → catalog (`/catalog`) → product detail → submit a
   review (`/reviews`).
2. Log in as `multi1` → pick a role from the modal → land on that role's
   dashboard → switch role from the sidebar badge.
3. Log in as `seller1` or `driver1` → single role, no modal, straight to
   the dashboard shell.
4. Log in as `admin` → dashboard shows live platform counts (users per
   role); full admin tooling (user management, overdue sweep, reports)
   ships in Sprint 5.

## Current status

Sprint 1 (Level 1 — auth, multi-role, public reviews, UI foundation,
landing/catalog/dashboard pages with dummy data) is complete. See
`.planning/sprint1/progress.md` for the task-by-task log and any documented
deviations from the TDD.

## Tests

```bash
./vendor/bin/sail artisan test
```

## Formatting

```bash
./vendor/bin/sail pint
./vendor/bin/sail npm run lint
./vendor/bin/sail npm run format
```
