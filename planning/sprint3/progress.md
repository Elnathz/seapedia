# Sprint 3 Progress

Check off each slice as it is committed (one commit per task). Keep in sync with `plan.md`.

## Tasks

- [ ] T1 · Wallet ledger + locked WalletService — `feat(wallet): add wallet ledger and locked WalletService`
- [ ] T2 · Fake top-up (gateway interface + FakeGateway) + wallet page — `feat(wallet): add fake top-up via payment gateway interface`
- [ ] T3 · Delivery address management — `feat(buyer): add delivery address management`
- [ ] T4 · Cart with single-store guard — `feat(cart): add buyer cart with single-store guard`
- [ ] T5 · Checkout preview + commit + OrderService + ClockService — `feat(checkout): charge wallet and reduce stock in a locked transaction`
- [ ] T6 · Buyer order history + detail + seller incoming list — `feat(order): add buyer order history and seller incoming list`
- [ ] T7 · API mirror + Swagger for buyer flows — `feat(api): expose buyer wallet, cart and checkout endpoints`
- [ ] T8 · Demo seed (wallets, addresses, sample order) + README — `feat(db): seed buyer wallets, addresses and demo order`

## Candidate add-ons (owner decides at approval — not in the 20 pts)

- [ ] A1 · Featured products + reviews on the landing page — `feat(catalog): show featured products and reviews on landing`
- [ ] A2 · Real demo product photos — `chore(db): use real product photos in demo seed`

## Tests

- [ ] Concurrent credit+debit stay consistent (locked); over-debit rejected (T1)
- [ ] Fake top-up credits wallet + writes ledger; replay is idempotent (T2)
- [ ] Cross-user address update → 403; new default unsets previous (T3)
- [ ] Add from different store → 422; clear-then-add succeeds; qty update changes line subtotal (T4)
- [ ] Oversell: two concurrent checkouts on last unit → one succeeds, one rejected, no negative stock (T5)
- [ ] Insufficient balance → rejected, no order/stock/charge side effects (T5)
- [ ] Buyer scoped to own orders; seller scoped to own store's orders (T6)
- [ ] API checkout happy path returns order; insufficient balance → 422 (T7)

## Visual QA (Playwright MCP) — 360/768/1280/1920, both locales, light + dark

- [ ] Wallet: balance card + top-up form (loading/success) + transaction ledger
- [ ] Addresses: list + create/edit dialog + delete confirm + set-default
- [ ] Cart: items, qty steppers, single-store conflict dialog, empty state
- [ ] Checkout: address select + delivery radio-group + summary ledger + confirm; insufficient-balance disabled state
- [ ] Buyer order history + detail (money breakdown + status timeline, ID labels in EN)
- [ ] Seller incoming orders list

## Notes / deviations recorded

(Filled in during implementation.)
