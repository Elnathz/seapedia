# Sprint 7 Progress

## A — Bug fixes
- [x] A1 fix(order): match status tabs to real order enum — `c1aa062`
- [x] A2 fix(catalog): close sheet before category modal — `1fe2331`

## F — Wallet
- [x] F1 feat(wallet): bound top-up between 5rb and 100jt

## B — Cart & Checkout
- [x] B0 feat(checkout): distance + weight delivery fee — superseded the region-tier surcharge after the user chose a real-marketplace model (SPEC line 278 is the only hard rule; distance/weight are spec-legal). DeliveryFeeService = base(method) + Haversine km × rate(method) + weight; lat/lng on stores+addresses (Leaflet/OSM picker, no API key), mandatory per-variant weight; preview==commit; TDD §5.4a rewritten; +6 Pest tests. Commits: 7ec3bf9, a66acce, 1e08feb, 443c10a.
- [x] B1 fix(cart): stop price overflow and align mobile summary
- [ ] B2 feat(checkout): make layout responsive with summary rail
- [ ] B3 feat(checkout): add inline add-address modal
- [ ] B4 feat(discount): add buyer promo and voucher picker modal
- [ ] B5 feat(checkout): add post-checkout success screen

## C — Seller
- [ ] C1 feat(store): expand seller store profile fields
- [ ] C2 feat(store): add seller first-run onboarding steps
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
