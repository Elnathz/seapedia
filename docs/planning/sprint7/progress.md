# Sprint 7 Progress

## A — Bug fixes
- [x] A1 fix(order): match status tabs to real order enum — `c1aa062`
- [x] A2 fix(catalog): close sheet before category modal — `1fe2331`

## F — Wallet
- [x] F1 feat(wallet): bound top-up between 5rb and 100jt

## B — Cart & Checkout
- [x] B0 feat(checkout): distance + weight delivery fee — superseded the region-tier surcharge after the user chose a real-marketplace model (SPEC line 278 is the only hard rule; distance/weight are spec-legal). DeliveryFeeService = base(method) + Haversine km × rate(method) + weight; lat/lng on stores+addresses (Leaflet/OSM picker, no API key), mandatory per-variant weight; preview==commit; TDD §5.4a rewritten; +6 Pest tests. Commits: 7ec3bf9, a66acce, 1e08feb, 443c10a.
- [x] B1 fix(cart): stop price overflow and align mobile summary
- [x] B2 feat(checkout): redesign payment summary rail (sea-tinted plate, highlighted total, distance/weight breakdown) — `1090aea`. Layout already responsive (lg:grid-cols-3 + sticky rail).
- [x] B3 feat(checkout): add inline add-address modal — extracted the address form into a reusable `AddressFormDialog`, redesigned the checkout address section as selectable radio cards (default badge, region line) with an "add new address" affordance + empty state, and made `BuyerAddressController@store` redirect `back()` so adding from checkout keeps the buyer on checkout and auto-selects the new address. +1 Pest test (referrer redirect).
- [ ] Map picker: fix(ui) SSR-safe + z-index isolate — `c42b973`
- [x] B4 feat(discount): add buyer promo and voucher picker modal — `DiscountService::availableFor(subtotal)` lists live promos/vouchers annotated with the savings each yields, eligibility vs min-spend, and (vouchers) redemption progress. New `DiscountPickerDialog` (Promo/Voucher tabs, usage Progress bar per voucher, gated ineligible codes) applies a pick through the same code path as manual entry. `cartSubtotal()` extracted from preview() so the picker gates on the exact subtotal. +3 Pest tests.
- [x] B5 feat(checkout): add post-checkout success screen — `store()` now PRG-redirects to `buyer.checkout.success/{order}` (a refresh can't re-submit) which renders a celebratory `Success.vue` (animated check, order recap: code, store+items, method, total) with CTAs to "Pesanan Saya" and the catalog. Ownership enforced via the order `view` policy. +2 Pest tests (renders for owner, 403 for others); updated the 4 redirect assertions in Checkout/DeliveryFee/Discount tests.

## C — Seller
- [x] C1 feat(store): store profile completeness — full address (jalan/RT/RW + region cascader + postal), map-pinned origin (Leaflet), 1:1 logo upload with Cropper.js crop + validation, saved-state summary card. Commits `9d8644b`, `e9140d1`.
- [x] C2 feat(store): add seller first-run onboarding steps — `DashboardService` now emits an `onboarding` state (store / address+map / logo / first product) and the seller dashboard shows a `SellerOnboardingCard` checklist (progress bar, per-step status, next-step CTA) that fades out once every milestone is done. Replaced the stale "coming soon" empty state. +1 Pest test.
- [ ] C3 feat(store): enrich seller dashboard with pipeline stats
- [ ] C4 fix(product): <root cause once reproduced>

## D — Driver
- [ ] D1 feat(delivery): sort driver jobs by nearest region
- [ ] D2 feat(delivery): add delivery route animation to order detail
- [ ] D3 feat(delivery): cap driver active pickups at three

## E — Cross-cutting
- [ ] E1 feat(ui): open mobile sidebar with edge swipe

## Decisions (resolved)
- [x] Delivery fee: base per-method + region-tier surcharge (B0)
- [x] Promos: expiry-only (progress bar = vouchers only)
