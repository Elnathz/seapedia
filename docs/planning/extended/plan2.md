# Extended Plan: Security, Seeder, Related Products, Overdue Fix

> Sprint: Extended | Date: 2026-06-30 | Author: Farros

## Scope

Four clusters confirmed and approved by user:

1. **Fix `/admin/overdue`** — missing `import { ref }` in Vue component
2. **Popular Categories** — flatten root+child sorted by product count
3. **Topup Max Balance** — Rp 1.000.000.000 (1 Miliar), max per topup Rp 10.000.000
4. **Security Guards** — store delete block, driver resign block, account delete per-role
5. **Level 7 Security Hardening** — v-html audit, SQLi check, RBAC verify, session
6. **Product Seeder Overhaul** — replace fake with real photo-backed products (~45 items)
7. **Related Products section** — product detail page, below description
8. **Level 7 Documentation** — documented means README.md + inline comments

## Decisions (Locked)

| # | Decision | Locked Value |
|---|----------|-------------|
| Balance cap | Max total saldo wallet | Rp 1.000.000.000 |
| Per topup | Max per transaksi topup | Rp 10.000.000 |
| Store delete | Guard on active orders | Hard block 422 |
| Driver resign | Guard on active delivery | Hard block 422 |
| Account delete | Soft delete + anonymize | YES - buat per-role |
| Popular categories | Flatten root+child | YES |
| Related products | Same cat or parent cat | YES |
| "Documented" meaning | README.md + code comments | README sections |

## File Targets

| Task | File | Type |
|------|------|------|
| T1 | `resources/js/pages/admin/overdue/Index.vue` | fix |
| T2 | `app/Services/CategoryService.php` | modify |
| T2 | `app/Http/Controllers/Web/CatalogController.php` | modify |
| T3 | `app/Services/TopupService.php` | modify |
| T3 | `app/Http/Requests/Buyer/TopupRequest.php` | modify (if exists) |
| T4A | `app/Http/Controllers/Web/SellerStoreController.php` | add destroy |
| T4B | `app/Http/Controllers/Web/RoleController.php` | modify |
| T4C | `app/Http/Controllers/Settings/ProfileController.php` | modify destroy |
| T5 | XSS/SQLi/RBAC audit + README | docs |
| T6 | `database/seeders/StoreProductSeeder.php` | overhaul |
| T7 | `app/Services/CatalogService.php` | add related() |
| T7 | `app/Http/Controllers/Web/CatalogController.php` | modify show |
| T7 | `resources/js/pages/catalog/Show.vue` | add section |

## Commit Order (commit-message skill)

```
fix(admin): add missing Vue ref import on overdue page
feat(catalog): flatten categories for popular section including children  
feat(wallet): add max balance guard and per-topup limit
feat(security): block store delete if active orders exist
feat(security): block driver role removal if active delivery exists
feat(auth): add soft delete and anonymize on account deletion
feat(product): replace seeder with real photo-backed products
feat(catalog): add related products section on product detail page
docs(security): document L7 security measures in README
```
