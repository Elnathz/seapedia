export type OrderStatusKey =
    | 'sedang_dikemas'
    | 'menunggu_pengirim'
    | 'sedang_dikirim'
    | 'pesanan_selesai'
    | 'dikembalikan';

const LABELS: Record<OrderStatusKey, string> = {
    sedang_dikemas: 'Sedang Dikemas',
    menunggu_pengirim: 'Menunggu Pengirim',
    sedang_dikirim: 'Sedang Dikirim',
    pesanan_selesai: 'Pesanan Selesai',
    dikembalikan: 'Dikembalikan',
};

/**
 * Order status labels stay Indonesian in both locales — they're the
 * system's own status vocabulary (printed on receipts, used by both
 * buyer and seller), not general UI chrome, so they don't go through
 * vue-i18n.
 */
export function orderStatusLabel(status: OrderStatusKey): string {
    return LABELS[status] ?? status;
}

const BADGE_VARIANTS: Record<
    OrderStatusKey,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    sedang_dikemas: 'outline',
    menunggu_pengirim: 'outline',
    sedang_dikirim: 'secondary',
    pesanan_selesai: 'default',
    dikembalikan: 'destructive',
};

export function orderStatusBadgeVariant(status: OrderStatusKey) {
    return BADGE_VARIANTS[status] ?? 'outline';
}
