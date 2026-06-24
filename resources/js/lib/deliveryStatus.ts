export type DeliveryStatusKey = 'available' | 'taken' | 'completed';

const LABELS: Record<DeliveryStatusKey, string> = {
    available: 'Menunggu Kurir',
    taken: 'Sedang Diantar',
    completed: 'Selesai Diantar',
};

/**
 * Delivery status labels stay Indonesian in both locales, same rationale
 * as orderStatusLabel — they're the system's own status vocabulary.
 */
export function deliveryStatusLabel(status: DeliveryStatusKey): string {
    return LABELS[status] ?? status;
}

const BADGE_VARIANTS: Record<
    DeliveryStatusKey,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    available: 'outline',
    taken: 'secondary',
    completed: 'default',
};

export function deliveryStatusBadgeVariant(status: DeliveryStatusKey) {
    return BADGE_VARIANTS[status] ?? 'outline';
}
