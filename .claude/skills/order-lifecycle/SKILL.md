---
name: order-lifecycle
description: Use when changing any order status (seller process, driver take/complete, overdue refund). Enforces the locked transition table and history logging.
---
# Order lifecycle (LOCKED — TDD §5.6, §5.9)

- Statuses (user-facing, Indonesian, never remove):
  sedang_dikemas → menunggu_pengirim → sedang_dikirim → pesanan_selesai
  ↘ dikembalikan (overdue)
- Change status ONLY through OrderService::transition($order, $to, $by), which:
  - asserts the (from → to) pair is in the valid table; else throws (422).
  - writes order_status_histories {status, note, changed_by, created_at via ClockService}.
- Driver take: DB::transaction + lockForUpdate delivery; assign only if driver_id null (else 409).
- Overdue refund: idempotent via order.refunded_at sentinel + row lock. In one transaction:
  credit buyer wallet (grand_total), reverse seller income, restore stock, set refunded_at, status→dikembalikan, history.
- Never set $order->status = ... directly outside OrderService.
