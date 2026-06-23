# Sprint 2 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [ ] T1 · Stores foundation — `feat(store): add seller store create and update`
- [ ] T2 · Product CRUD + image upload — `feat(product): add seller product crud with image upload`
- [ ] T3 · Real public catalog — `feat(catalog): read public catalog and detail from database`
- [ ] T4 · Public store detail page — `feat(store): add public store detail page`
- [ ] T5 · Catalog API + Swagger — `feat(api): expose public catalog endpoints with swagger`
- [ ] T6 · i18n infrastructure — `feat(i18n): add hybrid id/en locale infrastructure`
- [ ] T7 · Locale switcher + translate — `feat(i18n): add navbar and settings language switcher`
- [ ] T8 · Demo seed (stores + products) — `feat(db): seed demo stores and products`

## Tests

- [ ] Duplicate store name → 422; cross-seller store update → 403 (T1)
- [ ] Cross-seller product update → 403; cross-seller delete → 403; CRUD own-only; invalid price/stock → 422 (T2)
- [ ] Catalog returns only active products of active stores; search filters; inactive detail → 404 (T3)
- [ ] Store page lists only that store's active products; inactive store → 404 (T4)
- [ ] `GET /api/v1/catalog` returns only active products (T5)
- [ ] Default locale `id`; `SetLocale` applies cookie locale (T6)
- [ ] Authenticated locale update persists to `users.locale`; guest update sets cookie (T7)

## Visual QA (Playwright MCP)

- [ ] Catalog (grid/search/empty/loading/error) at 360/768/1280/1920, both locales, light + dark
- [ ] Product detail + store info block at all breakpoints/locales/themes
- [ ] Public store detail page
- [ ] Seller store onboarding + product list + create/edit form (image upload + delete dialog)
- [ ] Navbar locale toggle (guest + authed) + Settings locale preference

## Notes / deviations recorded

- (record deviations here as slices land, per CLAUDE.md)
