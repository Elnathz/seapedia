---
name: ipaymu-topup
description: Use when implementing wallet top-up, the PaymentGateway interface, IpaymuGateway, FakeGateway, or the iPaymu webhook. Encodes the verified v2 signature + idempotency rules.
---
# iPaymu top-up (TDD §9)

- iPaymu funds the WALLET only. Checkout never calls a gateway.
- Behind `PaymentGateway` interface. `PAYMENT_GATEWAY=fake` for demo/seed; `ipaymu` to exercise real flow.
- Signature (v2): stringToSign = "POST:"+VA+":"+lowercase(sha256(jsonBody))+":"+apiKey ;
  signature = hash_hmac('sha256', stringToSign, apiKey).
  Headers: va, signature, timestamp (YmdHis), Content-Type application/json.
  Endpoint: sandbox https://sandbox.ipaymu.com/api/v2/payment | prod https://my.ipaymu.com/api/v2/payment.
  Redirect Buyer to response Data.Url. Store Data.SessionID.
- Top-up record (topups) keyed by unique gateway_reference; status pending→paid.
- Webhook (notifyUrl, no auth): map by reference → if processed_at set, return 200 (idempotent) → else in a transaction credit wallet via WalletService, write wallet_transaction(topup), mark paid + processed_at.
- notifyUrl must be the deployed public HTTPS URL.
